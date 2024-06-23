<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $returnPath = "admin/products.php";

  if (
    !isset($_SERVER['PHP_AUTH_USER']) ||
    !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $adminUsername ||
    $_SERVER['PHP_AUTH_PW'] !== $adminPassword
  ) {
    header('WWW-Authenticate: Basic realm="Restricted Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentication required.';
    exit;
  }

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    showSessionAlert("Not allowed HTTP method!", "danger", true, $returnPath);
    exit;
  }

  $groupId = $_POST["group_id"];

  $getGroupStmt = $connection->prepare("SELECT * FROM `groups` WHERE id = ? LIMIT 1");
  $getGroupStmt->bind_param("i", $groupId);
  $getGroupStmt->execute();
  if ($getGroupStmt->errno) {
    showSessionAlert("Error in the getting process!", "danger", true, $returnPath);
    exit;
  }
  $groupResult = $getGroupStmt->get_result();
  $group = $groupResult->fetch_assoc();
  $getGroupStmt->close();

  if (!$group) {
    showSessionAlert("No group with this ID!", "danger", true, $returnPath);
    exit;
  }

  $price = number_format((float)$_POST["price"], 2, '.', '');
  $codeValue = $_POST["code_value"];
  $type = $_POST["type"];

  $connection->begin_transaction();
  $createNewProductStmt = $connection->prepare("INSERT INTO `products`(group_id, code_value, price, type) VALUES (?, ?, ?, ?)");
  $createNewProductStmt->bind_param("ssss", $groupId, $codeValue, $price, $type);
  $createNewProductStmt->execute();
  if ($createNewProductStmt->errno) {
    showSessionAlert("Error in the creating process!", "danger", true, $returnPath);
    $connection->rollback();
    exit;
  }
  $createNewProductStmt->close();

  $connection->commit();
  showSessionAlert("New Product was created for the group successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
