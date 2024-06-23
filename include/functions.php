<?php
// require_once "./include/config.php";

function logErrors($e)
{
  global $errorLogsFilePath;
  $errorMessage = $e->getTrace() . " | " . $e->getLine() . " | " . $e->getMessage();
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
    $getGroupsWithProuductsStmt = $connection->prepare("SELECT g.*, p.id AS product_id, p.type, p.price
                                                      FROM `groups` g
                                                      LEFT JOIN `products` p ON g.id = p.group_id
                                                      WHERE p.payment_id IS NULL AND g.visibility = 1
                                                      ORDER BY g.sort_index, g.id");

    $getGroupsWithProuductsStmt->execute();
    if ($getGroupsWithProuductsStmt->errno) {
      showSessionAlert($getGroupsWithProuductsStmt->error, "danger", true, $returnPath);
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
          'products' => []
        ];
      }

      if (isset($row["product_id"])) {
        $groups[$row["id"]]["products"][$row["type"]][] = [
          'id' => $row["product_id"],
          'type' => $row["type"],
          'price' => $row["price"],
          'date' => $row["date"],
        ];
      }
    }

    return $groups;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger", true, $returnPath);
    logErrors($e);
    exit;
  }
}

function getGroupProductsOfType(int $groupId, string $type, int $quantity, string $returnPath = ""): array
{
  global $connection;

  try {
    $getGroupWithProuductsStmt = $connection->prepare("SELECT g.*, p.id AS product_id, p.type, p.price
                                                      FROM `groups` g
                                                      LEFT JOIN `products` p ON g.id = p.group_id
                                                      WHERE (p.payment_id IS NULL) AND (g.visibility = 1) AND (g.id = ?) AND (p.type = ?)
                                                      LIMIT ?");
    $getGroupWithProuductsStmt->bind_param("isi", $groupId, $type, $quantity);

    $getGroupWithProuductsStmt->execute();
    if ($getGroupWithProuductsStmt->errno) {
      showSessionAlert($getGroupWithProuductsStmt->error, "danger", true, $returnPath);
      exit;
    }
    $getGroupsWithProuductsResults = $getGroupWithProuductsStmt->get_result();
    $getGroupWithProuductsStmt->close();

    $group = [];

    while ($row = $getGroupsWithProuductsResults->fetch_assoc()) {
      // var_dump($row);
      if (!isset($group["id"])) {
        $group = [
          'id' => $row["id"],
          'title' => $row["title"],
          'description' => $row["description"],
          'image' => $row["image"],
          'sort_index' => $row["sort_index"],
          'visibility' => $row["visibility"],
          'date' => $row["date"],
          'products' => []
        ];
      }

      if (isset($row["product_id"])) {
        $group["products"][] = [
          'id' => $row["product_id"],
          'type' => $row["type"],
          'price' => $row["price"],
          'date' => $row["date"],
        ];
      }
    }

    return $group;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger", true, $returnPath);
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
      showSessionAlert($getUserStmt->error, "danger", true, $returnPath);
      exit;
    }
    $userResult = $getUserStmt->get_result();
    $user = $userResult->fetch_assoc();
    $getUserStmt->close();
    if (!$user) {
      showSessionAlert("No user found! Please login in first.", "danger", true, $returnPath);
      exit;
    }

    return $user;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger", true, $returnPath);
    logErrors($e);
    exit;
  }
}

function getUserWallet(int $user_id, string $returnPath = ""): mixed
{
  global $connection;
  try {
    $getWalletStmt = $connection->prepare("SELECT * FROM `wallets` WHERE user_id = ? AND `status` != 'BLOCKED' LIMIT 1");
    $getWalletStmt->bind_param("i", $user_id);
    $getWalletStmt->execute();
    if ($getWalletStmt->errno) {
      showSessionAlert($getWalletStmt->error, "danger", true, $returnPath);
      exit;
    }
    $walletResult = $getWalletStmt->get_result();
    $wallet = $walletResult->fetch_assoc();
    $getWalletStmt->close();
    if (!$wallet) {
      showSessionAlert("No wallet found! Please contact the support.", "danger", true, $returnPath);
      exit;
    }
    return $wallet;
  } catch (Throwable $e) {
    showSessionAlert("Unexpected error! Please contact the support.", "danger", true, $returnPath);
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
      showSessionAlert($getPaymentsStmt->error, "danger", true, $returnPath);
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
    showSessionAlert("Unexpected error! Please contact the support.", "danger", true, $returnPath);
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
      showSessionAlert($getProductsStmt->error, "danger", true, $returnPath);
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
    showSessionAlert("Unexpected error! Please contact the support.", "danger", true, $returnPath);
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
      showSessionAlert($getChargesStmt->error, "danger", true, $returnPath);
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
    showSessionAlert("Unexpected error! Please contact the support.", "danger", true, $returnPath);
    logErrors($e);
    exit;
  }
}
