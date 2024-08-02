<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

try {
  // Disable output buffering
  while (ob_get_level()) ob_end_flush();
  ob_implicit_flush(true);

  set_time_limit(120);

  require_once "../include/config.php";
  require_once "../include/functions.php";

  $paymentId = $_GET['paymentId'];
  $returnPath = "/payment_processing.php?paymentId=$paymentId";

  // check if logged in or a guest
  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $userId = $user["id"];
  } else {
    $userId = $guestIdPrefix . session_id();
  }

  $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE (id = ?) AND (`status` != 'PAID') AND (user_id = ?) LIMIT 1");
  $getPaymentStmt->bind_param("is", $paymentId, $userId);
  $getPaymentStmt->execute();
  if ($getPaymentStmt->errno) {
    echo "data: " . json_encode(["error" => $getPaymentStmt->errno]) . "\n\n";
    flush();
    // exit;
  }
  $paymentResult = $getPaymentStmt->get_result();
  $payment = $paymentResult->fetch_assoc();
  $getPaymentStmt->close();
  if (!$payment) {
    echo "data: " . json_encode(["error" => "Payment not found!"]) . "\n\n";
    flush();
    exit;
  }

  $paymentId = $payment["id"];
  $typeId = $payment["type_id"];
  $quantity = $payment["quantity"];
  $merchantTradeNo = $payment['merchantTradeNo'];

  $binance_pay_api_key = $API_KEY;
  $binance_pay_api_secret = $API_SECRET;

  $request = [
    "merchantTradeNo" => $merchantTradeNo,
  ];
  echo "data: " . json_encode(["request" => $request]) . "\n\n";

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
  $endDate->add(new DateInterval('PT2M'));

  while (new DateTime() <= $endDate) {
    sleep(1);

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
    // paymentInfo:{payerId: '308307882', payMethod: 'funding', paymentInstructions: Array(1), channel: 'DEFAULT'}
    // prepayId: "308464622446870528"
    // status: "PAID"
    // transactTime:1721476848406
    // transactionId: "308464654340489216"
    // }
    // status: "SUCCESS"

    if ($responseData["status"] == "SUCCESS" && $responseData["data"]["status"] == "PAID") {
      $result = linkProductsWithPayment($paymentId, $typeId, $quantity, $returnPath);
      $products = $result[0];
      $errors = $result[1];
      echo "data: " . json_encode(["message" => "Payment successful! Redirecting to the success page.", "success" => true, "products" => $products, "errors" => $errors]) . "\n\n";
      flush();
      break;
    } else {
      echo "data: " . json_encode(["error" => "Payment statuses: " . $responseData["status"] . " / " . $responseData["data"]["status"]]) . "\n\n";
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
