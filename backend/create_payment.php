<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $type = $_POST["type"];
  $quantity = (int) $_POST["quantity"] ?? 1;
  $groupId = (int) $_POST["groupId"];
  $useWallet = (bool) $_POST["useWallet"];

  $returnPath = "checkout.php?groupId=" . $groupId . "&quantity=" . $quantity . "&type=" . $type;

  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $userId = $user["id"];
  } else {
    $userId = $guestIdPrefix . session_id();
  }



  $getGroupStmt = $connection->prepare("SELECT * FROM `groups` WHERE id = ? LIMIT 1");
  $getGroupStmt->bind_param("i", $groupId);
  $getGroupStmt->execute();
  if ($getGroupStmt->errno) {
    showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
    exit;
  }
  $groupResult = $getGroupStmt->get_result();
  $group = $groupResult->fetch_assoc();
  $getGroupStmt->close();
  // var_dump($group);

  if (!$group) {
    showSessionAlert("No group with this ID!", "danger", true, $returnPath);
    exit;
  }

  $products = [];
  $productsIds = [];
  $getProductsStmt = $connection->prepare("SELECT * FROM `products` WHERE (group_id = ?) AND (`type` = ?) AND (payment_id IS NULL) LIMIT ?");
  $getProductsStmt->bind_param("isi", $groupId, $type, $quantity);
  $getProductsStmt->execute();
  if ($getProductsStmt->errno) {
    showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
    exit;
  }
  $productsResult = $getProductsStmt->get_result();
  while ($product = $productsResult->fetch_assoc()) {
    // var_dump($product);
    $products[] = $product;
    $productsIds[] = $product["id"];
  };
  $getProductsStmt->close();

  // var_dump($products);

  // check products count
  if (count($products) < $quantity) {
    showSessionAlert("No enough quantity! Please chose less quantity or contact us.", "danger", true, $returnPath);
    exit;
  }

  $connection->begin_transaction();

  $status = "INTIATED";
  $metadata1 = "";
  $metadata2 = "";

  $encodedProductsIds = json_encode($productsIds);
  $createPaymentStmt = $connection->prepare("INSERT INTO `payments`(user_id, group_id, `status`, price, products, metadata1, metadata2) VALUES (?,?,?,?,?,?,?)");
  $createPaymentStmt->bind_param("iisdsss", $userId, $groupId, $status, $totalPrice, $encodedProductsIds, $metadata1, $metadata2);
  if ($createPaymentStmt->errno) {
    $connection->rollback();
    showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
    exit;
  }
  $insertedPaymentId = $createPaymentStmt->insert_id;
  $createPaymentStmt->close();

  // calculate total price
  $totalPrice = 0.0;
  foreach ($products as $product) {
    $totalPrice += (float) $product["price"];
  }

  $prepayID = "";
  if ($useWallet == "TRUE") {
    // use wallet balance
    $getWalletStmt = $connection->prepare("SELECT id FROM `wallets` WHERE user_id = ? LIMIT 1");
    $getWalletStmt->bind_param("i", $userId);
    $getWalletStmt->execute();
    if ($getWalletStmt->errno) {
      $connection->rollback();
      showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
      exit;
    }
    $walletResult = $getWalletStmt->get_result();
    $wallet = $walletResult->fetch_assoc();
    $walletId = $wallet["id"];
    $getWalletStmt->close();

    if ($wallet["balance"] < $totalPrice) {
      $connection->rollback();
      showSessionAlert("Not enough balance! please charge your wallet first.", "danger", true, $returnPath);
      exit;
    }

    // subtract the balance
    $subtractBalanceStmt = $connection->prepare("UPDATE `wallets` SET balance = balance - ? WHERE id = ?");
    $subtractBalanceStmt->bind_param("di", $totalPrice, $walletId);
    if ($subtractBalanceStmt->errno) {
      $connection->rollback();
      showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
      exit;
    }
    $subtractBalanceStmt->close();
  } else {
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
      "fiatAmount" => $totalPrice,
      "fiatCurrency" => "USD",
      "goods" => [
        "goodsType" => "02",
        "goodsCategory" => "6000",
        "referenceGoodsId" => $insertedPaymentId . time(),
        "goodsName" => $group["title"],
        "goodsDetail" => $group["description"],
        "goodsQuantity" => $quantity,
      ],
      "webhookUrl" => $baseURL . "/backend/binance_payment_webhook.php",
      "returnUrl" => $baseURL . "/backend/confirm_payment.php",
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
      showSessionAlert("Error in binance connection!", "danger", true, $returnPath);
      $connection->rollback();
      exit;
    }
    curl_close($ch);

    var_dump($result);

    // {
    //   "status": "SUCCESS",
    //   "code": "000000",
    //   "data": {
    //     "prepayId": "29383937493038367292",
    //     "terminalType": "APP",
    //     "expireTime": 121123232223,
    //     "qrcodeLink": "https://qrservice.dev.com/en/qr/dplkb005181944f84b84aba2430e1177012b.jpg",
    //     "qrContent": "https://qrservice.dev.com/en/qr/dplk12121112b",
    //     "checkoutUrl": "https://pay.binance.com/checkout/dplk12121112b",
    //     "deeplink": "bnc://app.binance.com/payment/secpay/xxxxxx",
    //     "universalUrl": "https://app.binance.com/payment/secpay?_dp=xxx=&linkToken=xxx"
    //   },
    //   "errorMessage": ""
    // }

    $responseData = json_decode($result, true);
    if (!!$responseData["msg"] || !!$responseData["errorMessage"] || $responseData["status"] != "SUCCESS") {
      $connection->rollback();
      showSessionAlert("Error in the Binance side: " . ($responseData["errorMessage"] ? $responseData["errorMessage"] : ($responseData["msg"] ? $responseData["msg"] : $responseData["code"])), "danger", true, $returnPath);

      exit;
    }

    $prepayID = $responseData["data"]["prepayId"];
  }

  // update the payment
  $newStatus = "PENDING";
  $type = $useWallet ? "WALLET" : "BINANCE";
  $updatePaymentStmt = $connection->prepare("UPDATE `payments` SET prepay_id = ?, `status` = ?, type = ? WHERE id = ?");
  $updatePaymentStmt->bind_param("sssi", $prepayID, $newStatus, $type, $insertedPaymentId);
  if ($updatePaymentStmt->errno) {
    $connection->rollback();
    showSessionAlert("Error in storing binance prepay ID.", "danger", true, $returnPath);
    exit;
  }
  $updatePaymentStmt->close();


  $paymentCheckoutURL = $responseData["checkoutUrl"];

  $connection->commit();
  header("location: $paymentCheckoutURL");
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
