<?php
header('Content-Type: application/json');

try {
  // Disable output buffering
  //  ob_implicit_flush(true);

  set_time_limit(120);

  require_once "../include/config.php";
  require_once "../include/functions.php";

  $chargeId = (int) $_GET['chargeId'];
  // echo json_encode(["chargeId" => $chargeId]);

  $returnPath = "payment_processing.php?chargeId=$chargeId";

  // check if logged in
  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $userId = $user["id"];
  } else {
    echo json_encode(["alert" => "You are not logged in!"]);
    exit;
  }

  $getChargeStmt = $connection->prepare("SELECT * FROM `charges` WHERE (id = ?) AND (`status` != 'PAID') AND (user_id = ?) LIMIT 1");
  $getChargeStmt->bind_param("ii", $chargeId, $userId);
  $getChargeStmt->execute();
  if ($getChargeStmt->errno) {
    logErrors($getChargeStmt->error, "string");
    echo json_encode(["alert" => $getChargeStmt->errno]);
    exit;
  }
  $chargeResult = $getChargeStmt->get_result();
  $charge = $chargeResult->fetch_assoc();
  $getChargeStmt->close();
  if (!$charge) {
    echo json_encode(["error" => "Charge not found!"]);
    exit;
  }

  $chargeId = $charge["id"];
  $merchantTradeNo = $charge['merchantTradeNo'];

  $binance_pay_api_key = $API_KEY;
  $binance_pay_api_secret = $API_SECRET;

  $request = [
    "merchantTradeNo" => $merchantTradeNo,
  ];
  // echo json_encode(["request" => $request]);

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

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo json_encode(["error" => "Error in binance connection!"]);
    exit;
  }
  curl_close($ch);

  $responseData = json_decode($result, true);

  // echo json_encode(["message" => $responseData]);

  if ($responseData["status"] == "SUCCESS" && $responseData["data"]["status"] == "PAID") { // TODO: change to PAID on production
    $errors = confirmCharge($chargeId, $userId, $returnPath);
    if (count($errors)) {
      logErrors($errors, "string");
      echo json_encode(["error" => "Error in the process!", "success" => false, "errors" => $errors]);
      exit;
    } else {
      echo json_encode(["message" => "Charge successful! Redirecting to the success page.", "success" => true]);
      exit;
    }
  } else {
    echo json_encode(["error" => "Charge statuses: " . $responseData["status"] . " / " . $responseData["data"]["status"]]);
  }
} catch (Throwable $e) {
  logErrors($e);
  echo json_encode(["error" => "An error occurred. Please try again later."]);
  exit;
}
