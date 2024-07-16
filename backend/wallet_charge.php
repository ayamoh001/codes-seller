<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $userId = "";
  $returnPath = "profile/wallet.php";

  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $userId = $user["id"];
  } else {
    showSessionAlert("You are not logged in!", "danger", true, "login.php");
    exit;
  }

  // get Wallet ID
  $wallet = getUserWallet($user_id, $returnPath);
  $walletId = $wallet["id"];

  $amount = (int) $_POST["amount"];

  if ($amount < 1) {
    showSessionAlert("Amount must be at least 1 USD!", "danger", true, $returnPath);
    exit;
  }

  $connection->begin_transaction();

  $status = "INTIATED";
  $metadata1 = "";
  $metadata2 = "";

  $createChargeStmt = $connection->prepare("INSERT INTO `charges`(wallet_id, amount, `status`, metadata1, metadata2) VALUES (?,?,?,?,?)");
  $createChargeStmt->bind_param("idsss", $walletId, $amount, $status, $metadata1, $metadata2);
  if ($createChargeStmt->errno) {
    $connection->rollback();
    showSessionAlert($createChargeStmt->error, "danger", true, $returnPath);
    exit;
  }
  $insertedChargeId = $createChargeStmt->insert_id;
  $createChargeStmt->close();

  $binance_pay_api_key = $API_KEY;
  $binance_pay_api_secret = $API_SECRET;

  // Generate Nonce
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $nonce = '';
  for ($i = 1; $i <= 32; $i++) {
    $pos = mt_rand(0, strlen($chars) - 1);
    $char = $chars[$pos];
    $nonce .= $char;
  }

  $ch = curl_init();
  $timestamp = round(microtime(true) * 1000);

  $request = [
    "env" => [
      "terminalType" => "WEB"
    ],
    "merchantTradeNo" => mt_rand(982538, 9825382937292),
    // "orderAmount" => $amount,
    // "currency" => "USDT",
    "fiatAmount" => $amount,
    "fiatCurrency" => "USD",
    "goods" => [
      "goodsType" => "02",
      "goodsCategory" => "6000",
      "referenceGoodsId" => $insertedChargeId . time(),
      "goodsName" => "Charge Account Wallet",
      "goodsDetail" => "Charge Account Wallet with $amount",
      "goodsQuantity" => 1,
    ],
    "webhookUrl" => "$webhookBaseURL/backend/binance_payment_webhook.php",
    "returnUrl" => "$baseURL/backend/confirm_wallet_charge.php",
    "cancelUrl" => "$baseURL/faild_payment.php",
  ];

  $json_request = json_encode($request);
  $payload = $timestamp . "\n" . $nonce . "\n" . $json_request . "\n";
  $binance_pay_api_key = $API_KEY;
  $binance_pay_api_secret = $API_SECRET;
  $signature = strtoupper(hash_hmac('SHA512', $payload, $binance_pay_api_secret));
  $headers = [
    "Content-Type: application/json",
    "BinancePay-Timestamp: $timestamp",
    "BinancePay-Nonce: $nonce",
    "BinancePay-Certificate-SN: $binance_pay_api_key",
    "BinancePay-Signature: $signature",
    "X-MBX-APIKEY: $binance_pay_api_key", // not important \_(0_0)_/
  ];

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_URL, $binanceURL . "/binancepay/openapi/v2/order");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json_request);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    $connection->rollback();
    showSessionAlert("Error in binance connection!", "danger", true, $returnPath);
    exit;
  }
  curl_close($ch);

  // echo "<pre>";
  // var_dump($result);

  $responseData = json_decode($result, true);
  // var_dump($responseData);

  if ($responseData["status"] != "SUCCESS") {
    $connection->rollback();
    showSessionAlert("Error in the Binance side: " . ($responseData["errorMessage"] ? $responseData["errorMessage"] : ($responseData["msg"] ? $responseData["msg"] : $responseData["code"])), "danger", true, $returnPath);
    exit;
  }

  // update the charge
  $prepayID = $responseData["data"]["prepayId"];
  $newStatus = "PENDING";
  $updateChargeStmt = $connection->prepare("UPDATE `charges` SET prepay_id = ?, `status` = ? WHERE id = ?");
  $updateChargeStmt->bind_param("ssi", $prepayID, $newStatus, $insertedChargeId);
  if ($updateChargeStmt->errno) {
    $connection->rollback();
    showSessionAlert("Error in storing Binance prepay ID.", "danger", true, $returnPath);
    exit;
  }
  $updateChargeStmt->close();

  $connection->commit();

  $checkoutUrl = $responseData["data"]["checkoutUrl"];
  header("location: $checkoutUrl");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
