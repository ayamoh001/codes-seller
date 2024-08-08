<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $paymentId = $_GET['paymentId'];
  $useWallet = (string) (isset($_GET["useWallet"]) && ($_GET["useWallet"] == "TRUE")) ? "TRUE" : "FALSE";
  $useWalletBoolean = ($useWallet === "TRUE") ? 1 : 0;

  $returnPath = "/payment_processing.php?paymentId=$paymentId&useWallet=$useWallet";

  // check if logged in or a guest
  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $userId = $user["id"];
  } else {
    echo json_encode(["alert" => "You are not logged in!"]);
    exit;
  }

  $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE (id = ?) AND (user_id = ?) LIMIT 1");
  $getPaymentStmt->bind_param("is", $paymentId, $userId);
  $getPaymentStmt->execute();
  if ($getPaymentStmt->errno) {
    logErrors($getPaymentStmt->error, "string");
    echo json_encode(["error" => $getPaymentStmt->errno]);
    exit;
  }

  $paymentResult = $getPaymentStmt->get_result();
  $payment = $paymentResult->fetch_assoc();
  $getPaymentStmt->close();
  if (!$payment) {
    echo json_encode(["error" => "Payment not found!"]);
    exit;
  }

  $paymentId = (int) $payment["id"];
  $typeId = (int) $payment["type_id"];
  $quantity = (int) $payment["quantity"];
  $merchantTradeNo = $payment['merchantTradeNo'];
  $transactionId = $payment['transaction_id'];
  $isManual = (bool) $payment['is_manual'];

  // echo json_encode(["isManual" => $isManual]);
  // exit;

  // $connection->begin_transaction();

  if ($useWallet === "TRUE") {
    $getPaymentStmt = $connection->prepare("SELECT `status` FROM `payments` WHERE (id = ?) AND (user_id = ?) LIMIT 1");
    $getPaymentStmt->bind_param("is", $paymentId, $userId);
    $getPaymentStmt->execute();
    if ($getPaymentStmt->errno) {
      logErrors($getPaymentStmt->error, "string");
      echo json_encode(["error" => $getPaymentStmt->errno]);
      exit;
    }
    $paymentResult = $getPaymentStmt->get_result();
    $payment = $paymentResult->fetch_assoc();
    $getPaymentStmt->close();
    if (!$payment) {
      echo json_encode(["error" => "Payment not found!"]);
      exit;
    }

    if ($payment["status"] == "PAID") {
      $result = linkProductsWithPaymentOrReturnExistings($paymentId,  $userId, $typeId, $quantity, $returnPath);
      $products = $result[0];
      $errors = $result[1];
      echo json_encode(["message" => "Wallet payment confirmed successfully!", "success" => true, "products" => $products, "errors" => $errors]);
      exit;
    } else {
      echo json_encode(["error" => "Payment statuses: " . $payment["status"]]);
      exit;
    }
  } else if ($isManual) {
    $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE (id = ?) AND (user_id = ?) LIMIT 1");
    $getPaymentStmt->bind_param("is", $paymentId, $userId);
    $getPaymentStmt->execute();
    if ($getPaymentStmt->errno) {
      logErrors($getPaymentStmt->error, "string");
      echo json_encode(["error" => $getPaymentStmt->errno]);
      exit;
    }
    $paymentResult = $getPaymentStmt->get_result();
    $payment = $paymentResult->fetch_assoc();
    $getPaymentStmt->close();
    if (!$payment) {
      echo json_encode(["error" => "Payment not found!"]);
      exit;
    }

    if ($payment["status"] == "PAID") {
      $result = linkProductsWithPaymentOrReturnExistings($paymentId, $userId, $typeId, $quantity, $returnPath);
      $products = $result[0];
      $errors = $result[1];
      echo json_encode(["message" => "Request confirmed successfully!", "success" => true, "products" => $products, "errors" => $errors]);
      exit;
    } else {
      echo json_encode(["error" => "Payment statuses: " . $payment["status"]]);
      exit;
    }
  } else {
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

    if ($responseData["status"] == "SUCCESS") {
      if ($responseData["data"]["status"] == "PAID") {
        $newStatus = "PAID";
        $updatePaymentStmt = $connection->prepare("UPDATE `payments` SET `status` = ? WHERE id = ?");
        $updatePaymentStmt->bind_param("si", $newStatus, $insertedPaymentId);
        $updatePaymentStmt->execute();
        if ($updatePaymentStmt->errno) {
          $connection->rollback();
          logErrors($updatePaymentStmt->error);
          echo json_encode(["error" => $updatePaymentStmt->errno]);
          exit;
        }
        $updatePaymentStmt->close();

        $result = linkProductsWithPaymentOrReturnExistings($paymentId, $userId, $typeId, $quantity, $returnPath);
        $products = $result[0];
        $errors = $result[1];
        echo json_encode(["success" => true, "message" => "Payment successful! Redirecting to the success page.", "products" => $products, "errors" => $errors]);
        exit;
      } else if ($responseData["data"]["status"] == ("INITIAL" || "PENDING")) {
        echo json_encode(["pending" => "The payment is created an waiting to be paid, current status: " . $responseData["data"]["status"]]);
        exit;
      } else {
        echo json_encode(["alert" => "payment is not pending nor paid, current status: " . $responseData["data"]["status"]]);
        exit;
      }
    } else {
      echo json_encode(["error" => "Payment Query Faild! response status: " . $responseData["status"]]);
      exit;
    }
  }
  // $connection->commit();

} catch (Throwable $e) {
  logErrors($e);
  echo json_encode(["error" => "An error occurred. Please try again later."]);
  exit;
}
