<?php
require_once "../include/config.php";

try {
  $userId = "";

  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $getUserStmt = $connection->prepare("SELECT * FROM `users` WHERE id = ? AND status != 'BLOCKED' LIMIT 1");
    $getUserStmt->bind_param("i", $user_id);
    $getUserStmt->execute();
    if ($getUserStmt->errno) {
      $_SESSION['flash_message'] = "Error in the auth proccess! please try again.";
      $_SESSION['flash_message'] = $getUserStmt->error;
      $_SESSION['flash_type'] = "danger";
      header("Location: $baseURL/profile/wallet.php");
      exit;
    }
    $userResult = $getUserStmt->get_result();
    $user = $userResult->fetch_assoc();
    $getUserStmt->close();
    $userId = $user["id"];
  } else {
    $_SESSION['flash_message'] = "You are not logged in!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/login.php");
    exit;
  }

  // get Wallet ID
  $getWalletStmt = $connection->prepare("SELECT id FROM `wallets` WHERE user_id = ? AND status != 'BLOCKED' LIMIT 1");
  $getWalletStmt->bind_param("i", $userId);
  $getWalletStmt->execute();
  if ($getWalletStmt->errno) {
    $_SESSION['flash_message'] = "Error in the Server! please contact the support.";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/profile/wallet.php");
    exit;
  }
  $walletResult = $getWalletStmt->get_result();
  $wallet = $walletResult->fetch_assoc();
  if (!$wallet) {
    $_SESSION['flash_message'] = "No active wallet found! Please contact the support for your wallet status.";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/login.php");
    exit;
  }
  $getWalletStmt->close();
  $walletId = $wallet["id"];

  $amount = (int) $_POST["amount"];

  if ($amount < 1) {
    $_SESSION['flash_message'] = "Amount must be at least 1 USD.";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/profile/wallet.php");
    exit;
  }

  $connection->begin_transaction();

  $status = "INTIATED";
  $metadata1 = "";
  $metadata2 = "";

  $createChargeStmt = $connection->prepare("INSERT INTO `charges`(wallet_id, amount, `status`, metadata1, metadata2) VALUES (?,?,?,?,?)");
  $createChargeStmt->bind_param("idsss", $walletId, $amount, $status, $metadata1, $metadata2);
  if ($createChargeStmt->errno) {
    echo json_encode(["error" => "Error in the Server! please contact the support."]);
    echo json_encode(["error" => $createChargeStmt->error]);
    $connection->rollback();
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
    // "orderAmount" => 25.17,
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
    "webhookUrl" => $baseURL . "/backend/binance_payment_webhook.php",
    "returnUrl" => $baseURL . "/backend/confirm_wallet_charge.php",
    "cancelUrl" => $baseURL . "/faild_payment.php",
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
    $_SESSION['flash_message'] = "Error in binance connection: " . curl_error($ch);
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/profile/wallet.php");
    exit;
  }
  curl_close($ch);

  var_dump($result);

  $responseData = json_decode($result, true);
  if (!!$responseData["msg"] || !!$responseData["errorMessage"] || $responseData["status"] != "SUCCESS") {
    $connection->rollback();
    $_SESSION['flash_message'] = "Error in the Binance side: " . ($responseData["errorMessage"] ? $responseData["errorMessage"] : ($responseData["msg"] ? $responseData["msg"] : $responseData["code"]));
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/profile/wallet.php");
    exit;
  }

  // update the charge
  $prepayID = $responseData["data"]["prepayId"];
  $newStatus = "PENDING";
  $updateChargeStmt = $connection->prepare("UPDATE `charges` SET prepay_id = ?, `status` = ? WHERE id = ?");
  $updateChargeStmt->bind_param("ssi", $prepayID, $newStatus, $insertedChargeId);
  if ($updateChargeStmt->errno) {
    echo json_encode(["error" => "Error in storing binance prepay ID."]);
    echo json_encode(["error" => $updateChargeStmt->error]);
    $connection->rollback();
    exit;
  }
  $updateChargeStmt->close();

  $connection->commit();

  $checkoutUrl = $responseData["checkoutUrl"];
  header("location: $checkoutUrl");
  exit;
} catch (Throwable $e) {
  $_SESSION['flash_message'] = "Error in the server!";
  $_SESSION['flash_message'] = $e->getMessage();
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/profile/wallet.php");

  $errorMessage = $e->getFile() . " | " . $e->getLine() . " | " . $e->getMessage();
  file_put_contents($errorLogsFilePath, $errorMessage, FILE_APPEND);
  exit;
}
