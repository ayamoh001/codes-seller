<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $groupId = (int) $_POST["groupId"];
  $typeId = (int) $_POST["typeId"];
  $quantity = (int) $_POST["quantity"] ?? 1;
  $useWallet = (bool) isset($_POST["useWallet"]) ? ($_POST["useWallet"] == "TRUE") : false;

  $returnPath = "checkout.php?groupId=$groupId&typeId=$typeId&quantity=$quantity";

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

  $getTypeStmt = $connection->prepare("SELECT * FROM `types` WHERE group_id = ? AND id = ? LIMIT 1");
  $getTypeStmt->bind_param("ii", $groupId, $typeId);
  $getTypeStmt->execute();
  if ($getTypeStmt->errno) {
    showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
    exit;
  }
  $groupResult = $getTypeStmt->get_result();
  $type = $groupResult->fetch_assoc();
  $getTypeStmt->close();
  // var_dump($group);

  if (!$type) {
    showSessionAlert("No type for this group ID!", "danger", true, $returnPath);
    exit;
  }

  $products = [];
  $getProductsStmt = $connection->prepare("SELECT * FROM `products` WHERE (type_id = ?) AND (payment_id IS NULL) LIMIT ?");
  $getProductsStmt->bind_param("ii", $typeId, $quantity);
  $getProductsStmt->execute();
  if ($getProductsStmt->errno) {
    showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
    exit;
  }
  $productsResult = $getProductsStmt->get_result();
  if ($productsResult->num_rows < $quantity) {
    showSessionAlert("No enough quantity! Please chose less quantity or contact us.", "danger", true, $returnPath);
    exit;
  };
  $getProductsStmt->close();

  $connection->begin_transaction();

  $status = "INTIATED";
  $metadata1 = "";
  $metadata2 = "";
  $totalPrice = (float) ($type["price"] * $quantity);

  $createPaymentStmt = $connection->prepare("INSERT INTO `payments`(user_id, group_id, type_id, `status`, price, metadata1, metadata2) VALUES (?,?,?,?,?,?,?)");
  $createPaymentStmt->bind_param("iiisdss", $userId, $groupId, $typeId, $status, $totalPrice, $metadata1, $metadata2);
  if ($createPaymentStmt->errno) {
    $connection->rollback();
    showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
    exit;
  }
  $insertedPaymentId = $createPaymentStmt->insert_id;
  $createPaymentStmt->close();

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
    $ch = curl_init();

    $nonce = generateNonce();
    $timestamp = round(microtime(true) * 1000);
    $merchantTradeNo = mt_rand(982538, 9825382937292);

    $request = [
      "env" => [
        "terminalType" => "WEB"
      ],
      "merchantTradeNo" => $merchantTradeNo,
      // "orderAmount" => $totalPrice,
      // "currency" => "USDT",
      "fiatAmount" => $totalPrice,
      "fiatCurrency" => "USD",
      "goods" => [
        "goodsType" => "02",
        "goodsCategory" => "6000",
        "referenceGoodsId" => $insertedPaymentId . time(),
        "goodsName" => preg_replace('/[^a-zA-Z0-9 ]/', '', $group["title"]),
        "goodsDetail" => preg_replace('/[^a-zA-Z0-9 ]/', '', $group["description"]),
        "goodsQuantity" => $quantity,
      ],
      "webhookUrl" => "$webhookBaseURL/backend/binance_payment_webhook.php",
      "returnUrl" => "$baseURL/backend/confirm_payment.php?merchantTradeNo=$merchantTradeNo",
      "cancelUrl" => "$baseURL/faild_payment.php",
    ];

    // echo "<pre>";
    // var_dump($request);
    // exit;

    $json_request = json_encode($request);
    // echo "<pre>";
    // var_dump($json_request);
    // exit;

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

    echo "<pre>";
    // var_dump($result);

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
    var_dump($responseData);

    if ($responseData["status"] != "SUCCESS") {
      $connection->rollback();
      // echo "<pre>";
      // var_dump($responseData);
      // exit;
      showSessionAlert("Error in the Binance side: " . ($responseData["errorMessage"] ? $responseData["errorMessage"] : ($responseData["msg"] ? $responseData["msg"] : $responseData["code"])), "danger", true, $returnPath);
      exit;
    }

    $prepayID = $responseData["data"]["prepayId"];
  }

  // update the payment
  $newStatus = "PENDING";
  $updatePaymentStmt = $connection->prepare("UPDATE `payments` SET prepay_id = ?, merchantTradeNo = ?, `status` = ?, use_wallet = ? WHERE id = ?");
  $updatePaymentStmt->bind_param("sssbi", $prepayID, $merchantTradeNo, $newStatus, $useWallet, $insertedPaymentId);
  if ($updatePaymentStmt->errno) {
    $connection->rollback();
    logErrors($updatePaymentStmt->error);
    showSessionAlert("Error in storing binance prepay ID.", "danger", true, $returnPath);
    exit;
  }
  $updatePaymentStmt->close();

  // $payemntCheckoutURLCodeLink = $responseData["data"]["qrCodeLink"];
  // $payemntCheckoutURLQRContent = $responseData["data"]["qrContent"];
  $payemntCheckoutURLDeepLink = $responseData["data"]["deeplink"];
  $paymentCheckoutURLWebPage = $responseData["data"]["checkoutUrl"];

  $connection->commit();

  // Redirect to the app or the web page if no app is installed
  header("location: $baseURL/redirect_to_app_or_page.php?deepLink=$payemntCheckoutURLDeepLink&webLink=$paymentCheckoutURLWebPage");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
