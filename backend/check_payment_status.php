<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
// header('X-Accel-Buffering: no');

try {
  // Disable output buffering
  //  while (ob_get_level()) ob_end_flush();
  //  ob_implicit_flush(true);

  set_time_limit(900); // TODO: set to 15 minutes

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
    $userId = $guestIdPrefix . session_id();
  }

  $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE (id = ?) AND (user_id = ?) LIMIT 1");
  $getPaymentStmt->bind_param("is", $paymentId, $userId);
  $getPaymentStmt->execute();
  if ($getPaymentStmt->errno) {
    logErrors($getPaymentStmt->error, "string");
    echo "data: " . json_encode(["error" => $getPaymentStmt->errno]) . "\n\n";
    flush();
    exit;
  }

  $paymentResult = $getPaymentStmt->get_result();
  $payment = $paymentResult->fetch_assoc();
  $getPaymentStmt->close();
  if (!$payment) {
    echo "data: " . json_encode(["error" => "Payment not found!"]) . "\n\n";
    flush();
    exit;
  }

  $paymentId = (int) $payment["id"];
  $typeId = (int) $payment["type_id"];
  $quantity = (int) $payment["quantity"];
  $merchantTradeNo = $payment['merchantTradeNo'];
  $transactionId = $payment['transaction_id'];
  $isManual = (bool) $payment['is_manual'];

  // echo "data: " . json_encode(["isManual" => $isManual]) . "\n\n";
  // flush();
  // exit;

  $endDate = new DateTime();
  $endDate->add(new DateInterval('PT15M')); // TODO: set to 15 minutes

  // $connection->begin_transaction();
  $counter = 0;
  while (new DateTime() <= $endDate) {
    sleep(10);

    $counter++;
    if ($counter % 6 == 0) {
      echo "data: " . json_encode(["heartbeat" => "still running"]) . "\n\n";
      flush();
    }

    if ($useWallet === "TRUE") {
      $getPaymentStmt = $connection->prepare("SELECT `status` FROM `payments` WHERE (id = ?) AND (user_id = ?) LIMIT 1");
      $getPaymentStmt->bind_param("is", $paymentId, $userId);
      $getPaymentStmt->execute();
      if ($getPaymentStmt->errno) {
        logErrors($getPaymentStmt->error, "string");
        echo "data: " . json_encode(["error" => $getPaymentStmt->errno]) . "\n\n";
        flush();
        continue;
      }
      $paymentResult = $getPaymentStmt->get_result();
      $payment = $paymentResult->fetch_assoc();
      $getPaymentStmt->close();
      if (!$payment) {
        echo "data: " . json_encode(["error" => "Payment not found!"]) . "\n\n";
        flush();
        continue;
      }

      if ($payment["status"] == "PAID") {
        $result = linkProductsWithPaymentOrReturnExistings($paymentId,  $userId, $typeId, $quantity, $returnPath);
        $products = $result[0];
        $errors = $result[1];
        echo "data: " . json_encode(["message" => "Wallet payment confirmed successfully!", "success" => true, "products" => $products, "errors" => $errors]) . "\n\n";
        flush();
        break;
      } else {
        echo "data: " . json_encode(["error" => "Payment statuses: " . $payment["status"]]) . "\n\n";
        flush();
        continue;
      }
    } else if ($isManual) {
      $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE (id = ?) AND (user_id = ?) LIMIT 1");
      $getPaymentStmt->bind_param("is", $paymentId, $userId);
      $getPaymentStmt->execute();
      if ($getPaymentStmt->errno) {
        logErrors($getPaymentStmt->error, "string");
        echo "data: " . json_encode(["error" => $getPaymentStmt->errno]) . "\n\n";
        flush();
        continue;
      }
      $paymentResult = $getPaymentStmt->get_result();
      $payment = $paymentResult->fetch_assoc();
      $getPaymentStmt->close();
      if (!$payment) {
        echo "data: " . json_encode(["error" => "Payment not found!"]) . "\n\n";
        flush();
        continue;
      }

      if ($payment["status"] == "PAID") {
        $result = linkProductsWithPaymentOrReturnExistings($paymentId, $userId, $typeId, $quantity, $returnPath);
        $products = $result[0];
        $errors = $result[1];
        echo "data: " . json_encode(["message" => "Request confirmed successfully!", "success" => true, "products" => $products, "errors" => $errors]) . "\n\n";
        flush();
        break;
      } else {
        echo "data: " . json_encode(["error" => "Payment statuses: " . $payment["status"]]) . "\n\n";
        flush();
        continue;
      }
    } else {
      $binance_pay_api_key = $API_KEY;
      $binance_pay_api_secret = $API_SECRET;

      $request = [
        "merchantTradeNo" => $merchantTradeNo,
      ];
      echo "data: " . json_encode(["request" => $request]) . "\n\n";

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
        echo "data: " . json_encode(["error" => "Error in binance connection!"]) . "\n\n";
        flush();
        continue;
      }

      $responseData = json_decode($result, true);

      // echo "data: " . json_encode(["message" => $responseData]) . "\n\n";
      // flush();

      // code:"000000"
      // data: {
      // commission: "0.1779"
      // createTime: 1721476833526
      // currency:"USDT"
      // merchantId:801762960
      // merchantTradeNo:"1968528456285"
      // openUserId:"2e6bc287add110c017e2d68e94baecf2"
      // orderAmount: "17.79000000"
      // paymentInfo:{payerId: '308307882', payMethod: 'funding', paymentInstructions: Array(1), channel: 'DEFAULT'}
      // prepayId: "308464622446870528"
      // status: "PAID"
      // transactTime:1721476848406
      // transactionId: "308464654340489216"
      // }
      // status: "SUCCESS"

      if ($responseData["status"] == "SUCCESS" && $responseData["data"]["status"] == "PAID") { // TODO: change to PAID on production
        $newStatus = "PAID";
        $updatePaymentStmt = $connection->prepare("UPDATE `payments` SET `status` = ? WHERE id = ?");
        $updatePaymentStmt->bind_param("si", $newStatus, $insertedPaymentId);
        $updatePaymentStmt->execute();
        if ($updatePaymentStmt->errno) {
          $connection->rollback();
          logErrors($updatePaymentStmt->error);
          echo "data: " . json_encode(["error" => $updatePaymentStmt->errno]) . "\n\n";
          flush();
          continue;
        }
        $updatePaymentStmt->close();

        $result = linkProductsWithPaymentOrReturnExistings($paymentId, $userId, $typeId, $quantity, $returnPath);
        $products = $result[0];
        $errors = $result[1];
        echo "data: " . json_encode(["message" => "Payment successful! Redirecting to the success page.", "success" => true, "products" => $products, "errors" => $errors]) . "\n\n";
        flush();
        break;
      } else {
        echo "data: " . json_encode(["error" => "Payment statuses: " . $responseData["status"] . " / " . $responseData["data"]["status"]]) . "\n\n";
        flush();
        continue;
      }
    }
  }

  curl_close($ch);

  // $connection->commit();

  echo "data: " . json_encode(["error" => "Timeout! Please try to buy again."]) . "\n\n";
  flush();
} catch (Throwable $e) {
  logErrors($e);
  echo "data: " . json_encode(["error" => "An error occurred. Please try again later."]) . "\n\n";
  flush();
  exit;
}
