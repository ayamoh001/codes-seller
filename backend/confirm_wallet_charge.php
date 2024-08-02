<?php
require_once '../include/config.php';
require_once '../include/functions.php';

logErrors($_SERVER['REQUEST_URI']);
logErrors(json_encode($_GET));
logErrors(json_encode($_POST));
logErrors(file_get_contents('php://input'));

try {
  $userId = "";
  $returnPath = "profile/wallet.php";
  $merchantTradeNo = $_GET['merchantTradeNo'];

  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $userId = $user["id"];
  } else {
    $userId = $guestIdPrefix . session_id();
  }

  $binance_pay_api_key = $API_KEY;
  $binance_pay_api_secret = $API_SECRET;

  $request = [
    "merchantTradeNo" => $merchantTradeNo,
  ];

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
  curl_setopt($ch, CURLOPT_URL, "$binanceURL/binancepay/openapi/order/query");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json_request);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    showSessionAlert("Error in binance connection!", "danger", true, $returnPath);
    exit;
  }
  curl_close($ch);

  echo "<pre>";
  var_dump($result);

  $responseData = json_decode($result, true);

  if ($responseData["status"] == "SUCCESS" && $responseData["data"]["status"] == "PAID") {
    $prepayID = $responseData["prepayID"];
    $getPaymentStmt = $connection->prepare("SELECT * FROM `charges` WHERE (prepay_id = ?) AND (`status` = 'PENDING') AND (user_id = ?) LIMIT 1");
    $getPaymentStmt->bind_param("ss", $prepayID, $userId);
    $getPaymentStmt->execute();
    if ($getPaymentStmt->errno) {
      showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
      exit;
    }

    $paymentResult = $getPaymentStmt->get_result();
    $payment = $paymentResult->fetch_assoc();
    $getPaymentStmt->close();

    if (!$payment) {
      showSessionAlert("No payment found in the DB.", "danger", true, $returnPath);
      exit;
    }

    $connection->begin_transaction();

    $newStatus = "PAID";
    $updatePaymentStatusStmt = $connection->prepare("UPDATE `charges` SET `status` = ? WHERE id = ?");
    $updatePaymentStatusStmt->bind_param("si", $newStatus, $product["id"]);
    if ($updatePaymentStatusStmt->errno) {
      $connection->rollback();
      echo $updatePaymentStatusStmt->error;
      // showSessionAlert("Error in saving the payment success status!", "danger", true, $returnPath);
      exit;
    }
    if ($updatePaymentStatusStmt->affected_rows == 0) {
      showSessionAlert("No pending payment to confirm!", "danger", true, $returnPath);
      exit;
    }
    $updatePaymentStatusStmt->close();

    // $connection->commit();
  } else {
    showSessionAlert("Payment statuses: " . $paymentStatus . " / " . $responseData["status"] . " / " . $responseData["data"]["status"], "danger", true, $returnPath);
    exit;
  }
} catch (Throwable $e) {
  var_dump($e);
  // showSessionAlert("Error in the server!", "danger", true, $returnPath);
  // logErrors($e);
  exit;
}
