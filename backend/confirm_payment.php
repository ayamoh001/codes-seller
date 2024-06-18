<?php
try {
  require_once '../include/config.php';

  if (isset($_GET['paymentStatus']) && isset($_GET['merchantTradeNo']) && isset($_GET['transactionId']) && isset($_GET['timestamp']) && isset($_GET['signature'])) {
    $userId = "";

    if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
      $user_id = (int) $_SESSION["user_id"];
      $getUserStmt = $connection->prepare("SELECT * FROM `users` WHERE id = ? AND status != 'BLOCKED' LIMIT 1");
      $getUserStmt->bind_param("i", $user_id);
      $getUserStmt->execute();
      if ($getUserStmt->errno) {
        // $_SESSION['flash_message'] = "Error in the auth proccess! please try again.";
        $_SESSION['flash_message'] = $getUserStmt->error;
        $_SESSION['flash_type'] = "danger";
        header("Location: $baseURL/");
        exit;
      }
      $userResult = $getUserStmt->get_result();
      $user = $userResult->fetch_assoc();
      if (!$user) {
        $_SESSION['flash_message'] = "No user found! Please login in first.";
        $_SESSION['flash_type'] = "danger";
        header("Location: $baseURL/");
        exit;
      }
      $getUserStmt->close();
      $userId = $user["id"];
    } else {
      $userId = $guestIdPrefix . session_id();
    }

    $paymentStatus = $_GET['paymentStatus'];
    $merchantTradeNo = $_GET['merchantTradeNo'];
    $transactionId = $_GET['transactionId'];
    $timestamp = $_GET['timestamp'];
    $signature = $_GET['signature'];

    // Recreate the signature to verify its authenticity
    $payload = $merchantTradeNo . "\n" . $transactionId . "\n" . $timestamp . "\n";

    $binance_pay_api_secret = $API_SECRET;
    $calculatedSignature = strtoupper(hash_hmac('SHA512', $payload, $binance_pay_api_secret));

    // Check all signature, GET status, Order status, and the DB status
    if ($signature === $calculatedSignature) {
      $request = [
        "merchantTradeNo" => $merchantTradeNo,
      ];

      $json_request = json_encode($request);
      $payload = $timestamp . "\n" . $nonce . "\n" . $json_request . "\n";
      $binance_pay_api_key = $API_KEY;
      $signature = strtoupper(hash_hmac('SHA512', $payload, $binance_pay_api_secret));
      $headers = [
        "Content-Type: application/json",
        "BinancePay-Timestamp: $timestamp",
        "BinancePay-Nonce: $nonce",
        "BinancePay-Certificate-SN: $binance_pay_api_key",
        "BinancePay-Signature: $signature",
      ];

      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_URL, $binanceURL . "/binancepay/openapi/order/query");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json_request);

      $result = curl_exec($ch);
      if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
      }
      curl_close($ch);
      // var_dump($result);

      $responseData = json_decode($result, true);

      if ($paymentStatus === 'SUCCESS' && $responseData["status"] == "SUCCESS" && $responseData["data"]["status"] == "PAID") {
        echo "Payment was successful. Transaction ID: " . $transactionId;

        $prepayID = $responseData["prepayID"];
        $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE (prepay_id = ?) AND (`status` != 'PAID') AND (user_id = ?) LIMIT 1");
        $getPaymentStmt->bind_param("ss", $prepayID, $userId);
        $getPaymentStmt->execute();
        if ($getPaymentStmt->errno) {
          // $_SESSION['flash_message'] = "Error in the Server! please contact the support.";
          $_SESSION['flash_message'] = $getPaymentStmt->error;
          $_SESSION['flash_type'] = "danger";
          header("Location: $baseURL/");
          exit;
        }
        $paymentResult = $getPaymentStmt->get_result();
        $payment = $groupResult->fetch_assoc();
        $getPaymentStmt->close();

        if (!$payment) {
          $_SESSION['flash_message'] = "No payment found in the DB.";
          $_SESSION['flash_type'] = "danger";
          header("Location: $baseURL/");
          exit;
        }

        $connection->begin_transaction();

        $newStatus = "PAID";
        $updatePaymentStatusStmt = $connection->prepare("UPDATE `payments` SET `status` = ? WHERE id = ?");
        $updatePaymentStatusStmt->bind_param("si", $newStatus, $product["id"]);
        if ($updatePaymentStatusStmt->errno) {
          // $_SESSION['flash_message'] = "Error in saving the payment success status!";
          $_SESSION['flash_message'] = $updatePaymentStatusStmt->error;
          $_SESSION['flash_type'] = "danger";
          header("Location: $baseURL/");
          $connection->rollback();
          exit;
        }
        $updatePaymentStatusStmt->close();

        $connection->commit();
      } else {
        $_SESSION['flash_message'] = "Payment statuses: " . $paymentStatus . " / " . $responseData["status"] . " / " . $responseData["data"]["status"];
        $_SESSION['flash_type'] = "danger";
        header("Location: $baseURL/");
      }
    } else {
      $_SESSION['flash_message'] = "Invalid signature. Payment verification failed.";
      $_SESSION['flash_type'] = "danger";
      header("Location: $baseURL/");
    }
  } else {
    $_SESSION['flash_message'] = "Missing parameters. Payment verification failed.";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/");
  }
} catch (Throwable $e) {
  // $_SESSION['flash_message'] = "Error in the server!";
  $_SESSION['flash_message'] = $e->getMessage();
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/");

  $errorMessage = $e->getFile() . " | " . $e->getLine() . " | " . $e->getMessage();
  file_put_contents($errorLogsFilePath, $errorMessage, FILE_APPEND);
  exit;
}
