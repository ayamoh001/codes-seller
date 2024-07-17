<?php
try {
  require_once '../include/config.php';
  require_once '../include/functions.php';

  logErrors($_SERVER['REQUEST_URI']);

  if (isset($_GET['paymentStatus']) && isset($_GET['merchantTradeNo']) && isset($_GET['transactionId']) && isset($_GET['timestamp']) && isset($_GET['signature'])) {
    $userId = "";
    $returnPath = "checkout.php";

    if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
      $user_id = (int) $_SESSION["user_id"];
      $user = getUser($user_id, $returnPath);
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
        showSessionAlert("Error in binance connection!", "danger", true, $returnPath);
        exit;
      }
      curl_close($ch);
      // var_dump($result);

      $responseData = json_decode($result, true);

      if ($paymentStatus === 'SUCCESS' && $responseData["status"] == "SUCCESS" && $responseData["data"]["status"] == "PAID") {
        // echo "Payment was successful. Transaction ID: " . $transactionId;

        $prepayID = $responseData["prepayID"];
        $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE (prepay_id = ?) AND (`status` != 'PAID') AND (user_id = ?) LIMIT 1");
        $getPaymentStmt->bind_param("ss", $prepayID, $userId);
        $getPaymentStmt->execute();
        if ($getPaymentStmt->errno) {
          showSessionAlert($getPaymentStmt->error, "danger", true, $returnPath);
          exit;
        }
        $paymentResult = $getPaymentStmt->get_result();
        $payment = $groupResult->fetch_assoc();
        $getPaymentStmt->close();

        if (!$payment) {
          showSessionAlert("No payment found in the DB.", "danger", true, $returnPath);
          exit;
        }

        $connection->begin_transaction();

        $newStatus = "PAID";
        $updatePaymentStatusStmt = $connection->prepare("UPDATE `payments` SET `status` = ? WHERE id = ?");
        $updatePaymentStatusStmt->bind_param("si", $newStatus, $payment["id"]);
        if ($updatePaymentStatusStmt->errno) {
          $connection->rollback();
          showSessionAlert($updatePaymentStatusStmt->error, "danger", true, $returnPath);
          exit;
        }
        $updatePaymentStatusStmt->close();

        // TODO: get products and linke them to the payment
        $products = [];
        $typeId = $payment["type_id"];
        $getProductsStmt = $connection->prepare("SELECT * FROM `products` WHERE type_id = ? AND payment_id IS NULL LIMIT ?");
        $getProductsStmt->bind_param("ii", $typeId, $quantity);
        $getProductsStmt->execute();
        if ($getProductsStmt->errno) {
          showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
          exit;
        }
        $productsResult = $getProductsStmt->get_result();

        // if ($productsResult->num_rows < $quantity) {
        //   showSessionAlert("No enough quantity! Please chose less quantity or contact us.", "danger", true, $returnPath);
        //   exit;
        // };

        $product = $productsResult->fetch_assoc();
        $getProductsStmt->close();

        $errors = [];
        while ($product = $productsResult->fetch_assoc()) {
          $productId = $product["id"];
          $setPaymentIdStmt = $connection->prepare("UPDATE `products` SET `payment_id` = ? WHERE id = ? AND `payment_id` IS NULL");
          $setPaymentIdStmt->bind_param("si", $payment["id"], $productId);
          if ($setPaymentIdStmt->errno) {
            $connection->rollback();
            showSessionAlert($setPaymentIdStmt->error, "danger", true, $returnPath);
            exit;
          }
          if ($setPaymentIdStmt->affected_rows == 0) {
            $errors[] = "Error: One of the products (ID: $productId) isn't found! It may be already sold, please contact the support.";
          }
          $setPaymentIdStmt->close();
        }
        $connection->commit();

        header("location: /success.php?paymentId=" . $payment["id"] . "&errors=" . json_encode($errors));
      } else {
        showSessionAlert(("Payment statuses: " . $paymentStatus . " / " . $responseData["status"] . " / " . $responseData["data"]["status"]), "danger", true, $returnPath);
        exit;
      }
    } else {
      showSessionAlert("Invalid signature. Payment verification failed.", "danger", true, $returnPath);
      exit;
    }
  } else {
    showSessionAlert("Missing parameters. Payment verification failed.", "danger", true, $returnPath);
    exit;
  }
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
