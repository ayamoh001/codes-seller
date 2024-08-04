<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

try {
  // Disable output buffering
  //  while (ob_get_level()) ob_end_flush();
  //  ob_implicit_flush(true);

  set_time_limit(120);

  require_once "../include/config.php";
  require_once "../include/functions.php";

  $chargeId = (int) $_GET['chargeId'];
  echo "data: " . json_encode(["chargeId" => $chargeId]) . "\n\n";
  flush();

  $returnPath = "payment_processing.php?chargeId=$chargeId";

  // check if logged in
  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $userId = $user["id"];
  } else {
    echo "data: " . json_encode(["error" => "You are not logged in!"]) . "\n\n";
    flush();
    exit;
  }

  $getChargeStmt = $connection->prepare("SELECT * FROM `charges` WHERE (id = ?) AND (`status` != 'PAID') AND (user_id = ?) LIMIT 1");
  $getChargeStmt->bind_param("ii", $chargeId, $userId);
  $getChargeStmt->execute();
  if ($getChargeStmt->errno) {
    logErrors($getChargeStmt->error, "string");
    echo "data: " . json_encode(["error" => $getChargeStmt->errno]) . "\n\n";
    flush();
    // exit;
  }
  $chargeResult = $getChargeStmt->get_result();
  $charge = $chargeResult->fetch_assoc();
  $getChargeStmt->close();
  if (!$charge) {
    echo "data: " . json_encode(["error" => "Charge not found!"]) . "\n\n";
    flush();
    exit;
  }

  $chargeId = $charge["id"];
  $merchantTradeNo = $charge['merchantTradeNo'];

  $binance_pay_api_key = $API_KEY;
  $binance_pay_api_secret = $API_SECRET;

  $request = [
    "merchantTradeNo" => $merchantTradeNo,
  ];
  // echo "data: " . json_encode(["request" => $request]) . "\n\n";

  $ch = curl_init();

  $nonce = generateNonce();
  $timestamp = round(microtime(true) * 1000);

  $json_request = json_encode($request);
  $payload = $timestamp . "\n" . $nonce . "\n" . $json_request . "\n";
  $signature = strtoupper(hash_hmac('SHA512', $payload, $binance_pay_api_secret));
  $headers = [
    "Content-Type: application/json",
    "BinancePay-Timestamp: $timestamp",
    "BinancePay-Nonce: $nonce",
    "BinancePay-Certificate-SN: $binance_pay_api_key",
    "BinancePay-Signature: $signature",
  ];

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_URL, "$binanceURL/binancepay/openapi/v2/order/query");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json_request);

  $endDate = new DateTime();
  $endDate->add(new DateInterval('PT15M')); // TODO: set to 15 minutes

  $counter = 0;
  while (new DateTime() <= $endDate) {
    sleep(10);

    $counter++;
    if ($counter % 6 == 0) {
      echo "data: " . json_encode(["heartbeat" => "still running"]) . "\n\n";
      flush();
    }

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
      echo "data: " . json_encode(["error" => "Error in binance connection!"]) . "\n\n";
      flush();
      continue;
    }

    $responseData = json_decode($result, true);

    echo "data: " . json_encode(["message" => $responseData]) . "\n\n";
    flush();

    // code:"000000"
    // data: {
    // commission: "0.1779"
    // createTime: 1721476833526
    // currency:"USDT"
    // merchantId:801762960
    // merchantTradeNo:"1968528456285"
    // openUserId:"2e6bc287add110c017e2d68e94baecf2"
    // orderAmount: "17.79000000"
    // paymentInfo:{payerId: '308307882', payMethod: 'funding', ChargeInstructions: Array(1), channel: 'DEFAULT'}
    // prepayId: "308464622446870528"
    // status: "PAID"
    // transactTime:1721476848406
    // transactionId: "308464654340489216"
    // }
    // status: "SUCCESS"

    if ($responseData["status"] == "SUCCESS" && $responseData["data"]["status"] == "PAID") { // TODO: change to PAID on production
      $errors = confirmCharge($chargeId, $userId, $returnPath);
      if (count($errors)) {
        echo "data: " . json_encode(["message" => "Error in the process!", "success" => false, "errors" => $errors]) . "\n\n";
        flush();
        break;
      } else {
        echo "data: " . json_encode(["message" => "Charge successful! Redirecting to the success page.", "success" => true]) . "\n\n";
        flush();
        break;
      }
    } else {
      echo "data: " . json_encode(["error" => "Charge statuses: " . $responseData["status"] . " / " . $responseData["data"]["status"]]) . "\n\n";
      flush();
    }
  }

  curl_close($ch);

  echo "data: " . json_encode(["error" => "Timeout! Please try to buy again."]) . "\n\n";
  flush();
} catch (Throwable $e) {
  logErrors($e);
  echo "data: " . json_encode(["error" => "An error occurred. Please try again later."]) . "\n\n";
  flush();
  exit;
}
