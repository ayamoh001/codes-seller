<?php
require_once "../include/config.php";


try {
  $userId = "";

  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $getUserStmt = $connection->prepare("SELECT * FROM users WHERE id = ? AND status != 'BLOCKED' LIMIT 1");
    $getUserStmt->bind_param("i", $user_id);
    $getUserStmt->execute();
    if ($getUserStmt->errno) {
      echo json_encode(["error" => "Error in the auth proccess! please try again."]);
      echo json_encode(["error" => $getUserStmt->error]);
      exit;
    }
    $userResult = $getUserStmt->get_result();
    $user = $userResult->fetch_assoc();
    $getUserStmt->close();
    $userId = $user["id"];
  } else {
    $userId = "Guest_" . session_id();
  }



  // start the process
  $requestData = json_decode(file_get_contents("php://input"), true);
  $type = $requestData["type"];
  $quantity = (int) $requestData["quantity"] ?? 1;
  $groupId = (int) $requestData["groupId"];

  // var_dump($type);
  // var_dump($groupId);
  // var_dump($quantity);

  $getGroupStmt = $connection->prepare("SELECT * FROM groups WHERE id = ? LIMIT 1");
  $getGroupStmt->bind_param("i", $groupId);
  $getGroupStmt->execute();
  if ($getGroupStmt->errno) {
    echo json_encode(["error" => "Error in the Server! please contact the support."]);
    echo json_encode(["error" => $getGroupStmt->error]);
    exit;
  }
  $groupResult = $getGroupStmt->get_result();
  $group = $groupResult->fetch_assoc();
  $getGroupStmt->close();
  // var_dump($group);

  if (!$group) {
    echo json_encode(["error" => "No group with this ID!"]);
    exit;
  }

  $products = [];
  $productsIds = [];
  $getProductsStmt = $connection->prepare("SELECT * FROM products WHERE (group_id = ?) AND (`type` = ?) AND (payment_id IS NULL) LIMIT ?");
  $getProductsStmt->bind_param("isi", $groupId, $type, $quantity);
  $getProductsStmt->execute();
  if ($getProductsStmt->errno) {
    echo json_encode(["error" => "Error in the Server! please contact the support."]);
    echo json_encode(["error" => $getProductsStmt->error]);
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
    echo json_encode(["error" => "No enough quantity! Please chose less quantity or contact us."]);
    exit;
  }

  $connection->begin_transaction();

  $status = "INTIATED";
  $metadata1 = "";
  $metadata2 = "";

  $encodedProductsIds = json_encode($productsIds);
  $createPaymentStmt = $connection->prepare("INSERT INTO payments(user_id, group_id, `status`, price, products, metadata1, metadata2) VALUES (?,?,?,?,?,?,?)");
  $createPaymentStmt->bind_param("iisdsss", $userId, $groupId, $status, $totalPrice, $encodedProductsIds, $metadata1, $metadata2);
  if ($createPaymentStmt->errno) {
    echo json_encode(["error" => "Error in the Server! please contact the support."]);
    echo json_encode(["error" => $createPaymentStmt->error]);
    $connection->rollback();
    exit;
  }
  $insertedPaymentId = $createPaymentStmt->insert_id;
  $createPaymentStmt->close();

  // calculate total price
  $totalPrice = 0.0;
  foreach ($products as $product) {
    $totalPrice += (float) $product["price"];
  }

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
  $binance_pay_key = $API_KEY;
  $binance_pay_secret = $API_SECRET;
  $signature = strtoupper(hash_hmac('SHA512', $payload, $binance_pay_secret));
  $headers = [
    "Content-Type: application/json",
    "BinancePay-Timestamp: $timestamp",
    "BinancePay-Nonce: $nonce",
    "BinancePay-Certificate-SN: $binance_pay_key",
    "BinancePay-Signature: $signature",
  ];

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_URL, $binanceURL . "/binancepay/openapi/v2/order");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json_request);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
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
  if (!!$responseData["errorMessage"] || $responseData["status"] != "SUCCESS") {
    echo json_encode(["error" => "Error in the Binance side: " . ($responseData["errorMessage"] ? $responseData["errorMessage"] : $responseData["code"])]);
    $connection->rollback();
    exit;
  }

  // update the payment
  $prepayID = $responseData["data"]["prepayId"];
  $newStatus = "PENDING";
  $updatePaymentStmt = $connection->prepare("UPDATE payments SET prepay_id = ?, `status` = ? WHERE id = ?");
  $updatePaymentStmt->bind_param("ssi", $prepayID, $newStatus, $insertedPaymentId);
  if ($updatePaymentStmt->errno) {
    echo json_encode(["error" => "Error in storing binance prepay ID."]);
    echo json_encode(["error" => $updatePaymentStmt->error]);
    $connection->rollback();
    exit;
  }
  $updatePaymentStmt->close();

  $connection->commit();

  // $paymentCheckoutURL = $responseData["checkoutUrl"];

  echo json_encode([
    "status" => "SUCCESS",
    "message" => "Payment Page Created Successfully!",
    "data" => $responseData,
  ]);
} catch (Throwable $e) {
  // echo json_encode(["error" => "Error in the server!"]);
  echo json_encode(["error" => $e->getLine() . " | " . $e->getMessage()]);
  exit;
}
