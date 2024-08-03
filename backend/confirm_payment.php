<?php
try {
  require_once '../include/config.php';
  require_once '../include/functions.php';

  logErrors($_SERVER['REQUEST_URI']);
  logErrors(json_encode($_GET));
  logErrors(json_encode($_POST));
  logErrors(file_get_contents('php://input'));

  $userId = "";
  $returnPath = "checkout.php";
  $useWallet = (string) (isset($_GET["useWallet"]) && ($_GET["useWallet"] == "TRUE")) ? "TRUE" : "FALSE";

  // check if logged in or a guest
  if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $userId = $user["id"];
  } else {
    $userId = $guestIdPrefix . session_id();
  }

  if ($useWallet === "TRUE") {
    $paymentId = $_GET['paymentId'];
  } else {
    $merchantTradeNo = $_GET['merchantTradeNo'];
    if (!$merchantTradeNo) {
      showSessionAlert("No merchant trade number found! please contact the support.", "danger", true, $returnPath);
      exit;
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
      var_dump($ch);
      // showSessionAlert("Error in binance connection!", "danger", true, $returnPath);
      exit;
    }
    curl_close($ch);
    echo "<pre>";
    var_dump($result);
    // exit;

    $responseData = json_decode($result, true);
    echo "<pre>";
    var_dump($responseData);
    // exit;

    if (!$responseData["status"] == "SUCCESS" || !$responseData["data"]["status"] == "PAID") {
      echo "Payment statuses: " . $responseData["status"] . " / " . $responseData["data"]["status"];
      // showSessionAlert(("Payment statuses: " . $paymentStatus . " / " . $responseData["status"] . " / " . $responseData["data"]["status"]), "danger", true, $returnPath);
      exit;
    }
  }

  if ($useWallet === "TRUE") {
    $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE (id = ?) AND (`status` = 'PENDING') AND (user_id = ?) LIMIT 1");
    $getPaymentStmt->bind_param("ss", $paymentId, $userId);
  } else {
    $prepayID = $responseData["prepayID"];
    $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE (prepay_id = ?) AND (`status` = 'PENDING') AND (user_id = ?) LIMIT 1");
    $getPaymentStmt->bind_param("ss", $prepayID, $userId);
  }

  $getPaymentStmt->execute();
  if ($getPaymentStmt->errno) {
    logErrors($getPaymentStmt->error, "string");
    echo "<pre>";
    var_dump($getPaymentStmt->errno);
    // exit;
    // showSessionAlert($getPaymentStmt->error, "danger", true, $returnPath);
    // exit;
  }

  $paymentResult = $getPaymentStmt->get_result();
  $payment = $paymentResult->fetch_assoc();
  $getPaymentStmt->close();

  if (!$payment) {
    var_dump("No pending payment found in the DB.");
    // exit;
    // showSessionAlert("No pending payment found in the DB.", "danger", true, $returnPath);
    // exit;
  }

  $connection->begin_transaction();

  $newStatus = "PAID";
  $updatePaymentStatusStmt = $connection->prepare("UPDATE `payments` SET `status` = ? WHERE id = ?");
  $updatePaymentStatusStmt->bind_param("si", $newStatus, $payment["id"]);
  if ($updatePaymentStatusStmt->errno) {
    $connection->rollback();
    logErrors($updatePaymentStatusStmt->error, "string");
    echo "<pre>";
    var_dump($updatePaymentStatusStmt->errno);
    // exit;
    // showSessionAlert($updatePaymentStatusStmt->error, "danger", true, $returnPath);
    // exit;
  }
  $updatePaymentStatusStmt->close();

  $products = [];
  $typeId = $payment["type_id"];
  $getProductsStmt = $connection->prepare("SELECT * FROM `products` WHERE type_id = ? AND payment_id IS NULL LIMIT ?");
  $getProductsStmt->bind_param("ii", $typeId, $quantity);
  $getProductsStmt->execute();
  if ($getProductsStmt->errno) {
    logErrors($getProductsStmt->error, "string");
    echo $getProductsStmt->errno;
    // showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
    // exit;
  }
  $productsResult = $getProductsStmt->get_result();

  if ($productsResult->num_rows < $quantity) {
    echo "No enough products quantity! it may be sold during your purchase. Please chose less quantity or contact us.";
    // showSessionAlert("No enough quantity! Please chose less quantity or contact us.", "danger", true, $returnPath);
    // exit;
  };

  $errors = [];
  while ($product = $productsResult->fetch_assoc()) {
    $productId = $product["id"];
    $setPaymentIdStmt = $connection->prepare("UPDATE `products` SET `payment_id` = ? WHERE id = ? AND `payment_id` IS NULL");
    $setPaymentIdStmt->bind_param("si", $payment["id"], $productId);
    if ($setPaymentIdStmt->errno) {
      $connection->rollback();
      logErrors($setPaymentIdStmt->error, "string");
      // showSessionAlert($setPaymentIdStmt->error, "danger", true, $returnPath);
      // exit;
    }
    if ($setPaymentIdStmt->affected_rows == 0) {
      $errors[] = "Error: One of the products (ID: $productId) isn't found! It may be already sold, please contact the support.";
    }
    $setPaymentIdStmt->close();
  }

  $getProductsStmt->close();

  // var_dump($errors);
  // exit;

  // echo "Payment was successful.";
  $connection->commit();

  header("location: /success.php?paymentId=" . $payment["id"] . "&errors=" . json_encode($errors));
} catch (Throwable $e) {
  var_dump($e);
  // showSessionAlert("Error in the server!", "danger", true, $returnPath);
  // logErrors($e);
  exit;
}
