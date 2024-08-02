<?php
// require_once "./include/config.php";

function logErrors($e, $type = "error")
{
  global $errorLogsFilePath;
  if ($e instanceof Throwable || $e instanceof Exception) {
    $errorMessage = $e->getFile() . " | " . $e->getTraceAsString() . " | " . $e->getLine() . " | " . $e->getMessage();
  } else {
    $errorMessage = json_encode($e);
  }
  file_put_contents($errorLogsFilePath, $errorMessage . PHP_EOL, FILE_APPEND);
}

function showSessionAlert($message, $type, $return = true, $returnPath = "")
{
  global $baseURL;
  try {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    if ($return) {
      header("Location: $baseURL/$returnPath");
    }
  } catch (Throwable $e) {
    // ¯\_(ツ)_/¯
    $_SESSION['flash_message'] = "Unexpected error in the alert displaying process! Please contact the support.";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/$returnPath");
    logErrors($e);
    exit;
  }
}

function printFlashMessages()
{
  try {
    if (isset($_SESSION['flash_message'])) {
      echo '<div class="alert alert-' . $_SESSION['flash_type'] . '">' . $_SESSION['flash_message'] . '</div>';

      unset($_SESSION['flash_message']);
      unset($_SESSION['flash_type']);
    };
  } catch (Throwable $e) {
    echo "error in the flash message displaying process!";
    logErrors($e);
    exit;
  }
}

function getGroups(string $returnPath = ""): array
{
  global $connection;
  try {
    $getGroupsWithProuductsStmt = $connection->prepare("SELECT g.*, t.id AS type_id, t.name AS type_name, t.price AS type_price, p.id AS product_id, t.sort_index AS type_sort_index
                                                      FROM `groups` g
                                                      LEFT JOIN `types` t ON g.id = t.group_id
                                                      LEFT JOIN `products` p ON t.id = p.type_id
                                                      WHERE p.payment_id IS NULL AND g.visibility = 1
                                                      ORDER BY g.sort_index, g.id, t.sort_index, t.id, p.date");

    $getGroupsWithProuductsStmt->execute();
    if ($getGroupsWithProuductsStmt->errno) {
      showSessionAlert($getGroupsWithProuductsStmt->error, "danger");
      logErrors($getGroupsWithProuductsStmt->error, "string");
      exit;
    }
    $getGroupsWithProuductsResults = $getGroupsWithProuductsStmt->get_result();
    $getGroupsWithProuductsStmt->close();

    $groups = [];

    while ($row = $getGroupsWithProuductsResults->fetch_assoc()) {
      // var_dump($row);
      if (!isset($groups[$row["id"]])) {
        $groups[$row["id"]] = [
          'id' => $row["id"],
          'title' => $row["title"],
          'description' => $row["description"],
          'image' => $row["image"],
          'sort_index' => $row["sort_index"],
          'visibility' => $row["visibility"],
          'date' => $row["date"],
          'types' => []
        ];
      }

      if (isset($row["type_name"])) {
        if (!isset($groups[$row["id"]]["types"][$row["type_name"]])) {
          $groups[$row["id"]]["types"][$row["type_name"]] = [
            'id' => $row["type_id"],
            'name' => $row["type_name"],
            'price' => $row["type_price"],
            'sort_index' => $row["type_sort_index"],
            'products' => []
          ];
        }

        if (isset($row["product_id"])) {
          $groups[$row["id"]]["types"][$row["type_name"]]["products"][] = [
            'id' => $row["product_id"],
          ];
        }
      }
    }

    return $groups;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger");
    logErrors($e);
    exit;
  }
}
function getGroupsForAdmin(string $returnPath = ""): array
{
  global $connection;
  try {
    $getGroupsWithProuductsStmt = $connection->prepare("SELECT g.*, t.id AS type_id, t.name AS type_name, t.price AS type_price, p.id AS product_id, t.sort_index AS type_sort_index, p.code_value
                                                      FROM `groups` g
                                                      LEFT JOIN `types` t ON g.id = t.group_id
                                                      LEFT JOIN `products` p ON t.id = p.type_id
                                                      WHERE p.payment_id IS NULL AND g.visibility = 1
                                                      ORDER BY g.sort_index, g.id, t.sort_index, t.id, p.date");

    $getGroupsWithProuductsStmt->execute();
    if ($getGroupsWithProuductsStmt->errno) {
      showSessionAlert($getGroupsWithProuductsStmt->error, "danger");
      logErrors($getGroupsWithProuductsStmt->error, "string");
      exit;
    }
    $getGroupsWithProuductsResults = $getGroupsWithProuductsStmt->get_result();
    $getGroupsWithProuductsStmt->close();

    $groups = [];

    while ($row = $getGroupsWithProuductsResults->fetch_assoc()) {
      // var_dump($row);
      if (!isset($groups[$row["id"]])) {
        $groups[$row["id"]] = [
          'id' => $row["id"],
          'title' => $row["title"],
          'description' => $row["description"],
          'image' => $row["image"],
          'sort_index' => $row["sort_index"],
          'visibility' => $row["visibility"],
          'date' => $row["date"],
          'types' => []
        ];
      }

      if (isset($row["type_name"])) {
        if (!isset($groups[$row["id"]]["types"][$row["type_name"]])) {
          $groups[$row["id"]]["types"][$row["type_name"]] = [
            'id' => $row["type_id"],
            'name' => $row["type_name"],
            'price' => $row["type_price"],
            'sort_index' => $row["type_sort_index"],
            'products' => []
          ];
        }

        if (isset($row["product_id"])) {
          $groups[$row["id"]]["types"][$row["type_name"]]["products"][] = [
            'id' => $row["product_id"],
            'code_value' => $row["code_value"],
          ];
        }
      }
    }

    return $groups;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger");
    logErrors($e);
    exit;
  }
}

function getGroupWithType(int $groupId, int $typeId, int $quantity, string $returnPath = ""): array
{
  global $connection;

  try {
    $getGroupWithProuductsStmt = $connection->prepare("SELECT g.*, t.id AS type_id, t.name AS type_name, t.price
                                                      FROM `groups` g
                                                      LEFT JOIN `types` t ON g.id = t.group_id 
                                                      LEFT JOIN `products` p ON t.id = p.type_id 
                                                      WHERE (p.payment_id IS NULL) AND (g.visibility = 1) AND (g.id = ?) AND (t.id = ?)
                                                      LIMIT ?");
    $getGroupWithProuductsStmt->bind_param("iii", $groupId, $typeId, $quantity);

    $getGroupWithProuductsStmt->execute();
    if ($getGroupWithProuductsStmt->errno) {
      showSessionAlert($getGroupWithProuductsStmt->error, "danger");
      logErrors($getGroupWithProuductsStmt->error, "string");
      exit;
    }
    $getGroupsWithProuductsResults = $getGroupWithProuductsStmt->get_result();
    $getGroupWithProuductsStmt->close();

    $group = $getGroupsWithProuductsResults->fetch_assoc();

    return $group;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger");
    logErrors($e);
    exit;
  }
}

function getUser(int $user_id, string $returnPath = ""): mixed
{
  global $connection;
  try {
    $getUserStmt = $connection->prepare("SELECT * FROM `users` WHERE id = ? AND `status` != 'BLOCKED' LIMIT 1");
    $getUserStmt->bind_param("i", $user_id);
    $getUserStmt->execute();
    if ($getUserStmt->errno) {
      showSessionAlert($getUserStmt->error, "danger");
      logErrors($getUserStmt->error, "string");
      exit;
    }
    $userResult = $getUserStmt->get_result();
    $user = $userResult->fetch_assoc();
    $getUserStmt->close();
    if (!$user) {
      showSessionAlert("No user found! Please login in first.", "danger");
      logErrors("No user found! Please login in first.", "string");
      exit;
    }

    return $user;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger");
    logErrors($e);
    exit;
  }
}

function getUserWallet(int $user_id, string $returnPath = ""): mixed
{
  global $connection;
  // var_dump($user_id);
  // exit;
  try {
    $getWalletStmt = $connection->prepare("SELECT * FROM `wallets` WHERE (user_id = ?) AND (`status` != 'BLOCKED') OR (`status` IS NULL) LIMIT 1");
    $getWalletStmt->bind_param("i", $user_id);
    $getWalletStmt->execute();
    if ($getWalletStmt->errno) {
      showSessionAlert($getWalletStmt->error, "danger");
      logErrors($getWalletStmt->error, "string");
      exit;
    }
    $walletResult = $getWalletStmt->get_result();
    $wallet = $walletResult->fetch_assoc();
    // var_dump($wallet);
    // exit;
    $getWalletStmt->close();
    if (!$wallet) {
      showSessionAlert("No wallet found! Please contact the support.", "danger");
      logErrors("No wallet found! Please contact the support.", "string");
      exit;
    }
    return $wallet;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger");
    logErrors($e);
    exit;
  }
}

function getUserPayments(int $user_id, string $returnPath = ""): array
{
  global $connection;

  try {
    $getPaymentsStmt = $connection->prepare("SELECT py.*, pr.id AS product_id, pr.date AS product_date, pr.* FROM 
                                        `payments` As py
                                        INNER JOIN 
                                        `products` AS pr
                                        WHERE pr.payment_id = py.id AND py.user_id = ?");

    $getPaymentsStmt->bind_param("i", $user_id);
    $getPaymentsStmt->execute();
    if ($getPaymentsStmt->errno) {
      showSessionAlert($getPaymentsStmt->error, "danger");
      logErrors($getPaymentsStmt->error, "string");
      exit;
    }
    $paymentsResult = $getPaymentsStmt->get_result();
    $getPaymentsStmt->close();

    $payments = [];

    while ($row = $paymentsResult->fetch_assoc()) {
      $row;
      if (!isset($payments[$row["id"]])) {
        $payments[$row["id"]] = [
          'id' => $row["id"],
          'price' => $row["price"],
          'status' => $row["status"],
          'date' => $row["date"],
          'products' => []
        ];
      }

      $payments[$row["id"]]['products'][] = [
        'id' => $row["product_id"],
        'code_value' => $row["code_value"],
        'type' => $row["type"],
        'price' => $row["price"],
      ];
    }
    return $payments;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger");
    logErrors($e);
    exit;
  }
}

function getUserProducts(int $user_id, string $returnPath = ""): array
{
  global $connection;
  try {
    $getProductsStmt = $connection->prepare("SELECT pr.* FROM `products` as pr
                                          INNER JOIN `payments` AS py
                                          WHERE py.user_id = ? AND payment_id IS NOT NULL");
    $getProductsStmt->bind_param("i", $user_id);
    $getProductsStmt->execute();
    if ($getProductsStmt->errno) {
      showSessionAlert($getProductsStmt->error, "danger");
      logErrors($getProductsStmt->error, "string");
      exit;
    }
    $productsResult = $getProductsStmt->get_result();
    $getProductsStmt->close();

    $products = [];

    while ($row = $productsResult->fetch_assoc()) {
      $products[] = $row;
    }
    return $products;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger");
    logErrors($e);
    exit;
  }
}

function linkProductsWithPayment(int $paymentId, int $typeId, int $quantity, string $returnPath = ""): array
{
  global $connection;

  $errors = [];
  $products = [];

  try {
    $connection->begin_transaction();

    $getPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE id = ? AND `status` != 'PAID' LIMIT 1");
    $getPaymentStmt->bind_param("i", $paymentId);
    $getPaymentStmt->execute();
    if ($getPaymentStmt->errno) {
      // showSessionAlert($getPaymentStmt->error, "danger");
      logErrors($getPaymentStmt->error, "string");
      throw new Exception($getPaymentStmt->error);
    }

    $paymentResult = $getPaymentStmt->get_result();
    $payment = $paymentResult->fetch_assoc();
    $getPaymentStmt->close();

    if (!$payment) {
      logErrors("No payment found! Please contact the support.", "string");
      throw new Exception("No payment found! Please contact the support.");
    }
    $newStatus = "PAID";
    $updatePaymentStatusStmt = $connection->prepare("UPDATE `payments` SET `status` = ? WHERE id = ?");
    $updatePaymentStatusStmt->bind_param("si", $newStatus, $payment["id"]);
    if ($updatePaymentStatusStmt->errno) {
      $connection->rollback();
      logErrors($updatePaymentStatusStmt->error, "string");
      return [[], [$updatePaymentStatusStmt->error]];
      // showSessionAlert($updatePaymentStatusStmt->error, "danger", true, $returnPath);
      // exit;
    }
    $updatePaymentStatusStmt->close();

    $typeId = $payment["type_id"];
    $getProductsStmt = $connection->prepare("SELECT * FROM `products` WHERE type_id = ? AND payment_id IS NULL LIMIT ?");
    $getProductsStmt->bind_param("ii", $typeId, $quantity);
    $getProductsStmt->execute();
    if ($getProductsStmt->errno) {
      logErrors($getProductsStmt->errno, "string");
      return [[], [$getProductsStmt->error]];
      // showSessionAlert("Error in the Server! please contact the support.", "danger", true, $returnPath);
      // exit;
    }
    $productsResult = $getProductsStmt->get_result();

    if ($productsResult->num_rows < $quantity) {
      logErrors("No enough products quantity! it may be sold during your purchase. Please chose less quantity or contact us.", "string");
      $errors[] = "No enough products quantity! it may be sold during your purchase. Please chose less quantity or contact us.";
      // showSessionAlert("No enough quantity! Please chose less quantity or contact us.", "danger", true, $returnPath);
    };

    while ($product = $productsResult->fetch_assoc()) {
      $productId = $product["id"];
      $products[] = $product["code_value"];

      $setProductPaymentIdStmt = $connection->prepare("UPDATE `products` SET `payment_id` = ? WHERE id = ? AND `payment_id` IS NULL");
      $setProductPaymentIdStmt->bind_param("ii", $paymentId, $productId);
      if ($setProductPaymentIdStmt->errno) {
        $connection->rollback();
        logErrors($setProductPaymentIdStmt->error, "string");
        $errors[] = $setProductPaymentIdStmt->error;
        // exit;
      }
      if ($setProductPaymentIdStmt->affected_rows == 0) {
        $errors[] = "paymentId: " . $paymentId . " productId: $productId, current product payment_id " . $product["payment_id"] . " and typre_id " . $typeId . "";
        $errors[] = "Error: One of the products (ID: $productId) isn't found! It may be already sold, please contact the support.";
      }
      $setProductPaymentIdStmt->close();
    }

    $getProductsStmt->close();

    if (count($errors)) {
      logErrors($errors, "string");
      // showSessionAlert($errors, "danger", true, $returnPath);
      // exit;
    }
    $connection->commit();

    return [$products, $errors];
  } catch (Throwable $e) {
    logErrors($e);
    $errors[] = $e->getMessage();
    return [$products, $errors];
  }
}

function confirmCharge(int $chargetId, string $returnPath = ""): array
{
  global $connection;

  $errors = [];

  try {
    $connection->begin_transaction();

    $getChargeStmt = $connection->prepare("SELECT * FROM `charges` WHERE id = ? AND `status` != 'PAID' LIMIT 1");
    $getChargeStmt->bind_param("i", $chargeId);
    $getChargeStmt->execute();
    if ($getChargeStmt->errno) {
      // showSessionAlert($getChargeStmt->error, "danger");
      logErrors($getChargeStmt->error, "string");
      throw new Exception($getChargeStmt->error);
    }

    $chargeResult = $getChargeStmt->get_result();
    $charge = $chargeResult->fetch_assoc();
    $getChargeStmt->close();

    if (!$charge) {
      logErrors("No charge found! Please contact the support.", "string");
      throw new Exception("No charge found! Please contact the support.");
    }
    $newStatus = "PAID";
    $updateChargeStatusStmt = $connection->prepare("UPDATE `charges` SET `status` = ? WHERE id = ?");
    $updateChargeStatusStmt->bind_param("si", $newStatus, $charge["id"]);
    if ($updateChargeStatusStmt->errno) {
      $connection->rollback();
      logErrors($updateChargeStatusStmt->error, "string");
      throw new Exception($updateChargeStatusStmt->error);
      // showSessionAlert($updateChargeStatusStmt->error, "danger", true, $returnPath);
      // exit;
    }
    $updateChargeStatusStmt->close();

    if (count($errors)) {
      logErrors($errors, "string");
      // showSessionAlert($errors, "danger", true, $returnPath);
      // exit;
    }
    $connection->commit();

    return $errors;
  } catch (Throwable $e) {
    logErrors($e);
    $errors[] = $e->getMessage();
    return $errors;
  }
}

function getPaymentWithProducts(int $paymentId, string $returnPath = ""): array
{
  global $connection;
  try {
    $getPaymentStmt = $connection->prepare("SELECT py.*, pd.id AS product_id, pd.date AS product_date, pd.* FROM `payments` AS py INNER JOIN `products` AS pd WHERE py.id = ? AND `py.status` != 'PAID' AND pd.payment_id = py.id LIMIT 1");
    $getPaymentStmt->bind_param("i", $paymentId);
    $getPaymentStmt->execute();
    if ($getPaymentStmt->errno) {
      showSessionAlert($getPaymentStmt->error, "danger");
      logErrors($getPaymentStmt->error, "string");
      exit;
    }
    $paymentResult = $getPaymentStmt->get_result();
    $getPaymentStmt->close();

    $payment = [];

    while ($row = $paymentResult->fetch_assoc()) {
      // var_dump($row);
      if (!isset($payment["id"])) {
        $payment = [
          'id' => $row["id"],
          'user_id' => $row["user_id"],
          'group_id' => $row["group_id"],
          'prepay_id' => $row["prepay_id"],
          'type_id' => $row["type_id"],
          'price' => $row["price"],
          'quantity' => $row["quantity"],
          'use_wallet' => $row["use_wallet"],
          'status' => $row["status"],
          'products' => []
        ];
      }

      $payment["products"][] = [
        'id' => $row["product_id"],
        'code_value' => $row["code_value"],
      ];
    }
    return $payment;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger");
    logErrors($e);
    exit;
  }
}
function getWalletCharges(int $wallet_id, string $returnPath = ""): array
{
  global $connection;
  try {
    $getChargesStmt = $connection->prepare("SELECT * FROM `charges` WHERE wallet_id = ? AND `status` != 'BLOCKED' LIMIT 1");
    $getChargesStmt->bind_param("i", $wallet_id);
    $getChargesStmt->execute();
    if ($getChargesStmt->errno) {
      showSessionAlert($getChargesStmt->error, "danger");
      logErrors($getChargesStmt->error, "string");
      exit;
    }
    $chargesResult = $getChargesStmt->get_result();
    $getChargesStmt->close();

    $charges = [];

    while ($row = $chargesResult->fetch_assoc()) {
      $charges[] = $row;
    }
    return $charges;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger");
    logErrors($e);
    exit;
  }
}

// function isMobile()
// {
//   $userAgent = $_SERVER['HTTP_USER_AGENT'];
//   $mobileAgents = '/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i';

//   return preg_match($mobileAgents, $userAgent);
// }

function generateNonce()
{
  $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $nonce = '';
  for ($i = 1; $i <= 32; $i++) {
    $pos = mt_rand(0, strlen($chars) - 1);
    $char = $chars[$pos];
    $nonce .= $char;
  }
  return $nonce;
}
