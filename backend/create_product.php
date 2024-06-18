<?php
try {
  require_once "../include/config.php";

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
    $_SESSION['flash_message'] = "Not allowed HTTP method!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }

  $groupId = $_POST["group_id"];

  $getGroupStmt = $connection->prepare("SELECT * FROM `groups` WHERE id = ? LIMIT 1");
  $getGroupStmt->bind_param("i", $groupId);
  $getGroupStmt->execute();
  if ($getGroupStmt->errno) {
    $_SESSION['flash_message'] = $getGroupStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }
  $groupResult = $getGroupStmt->get_result();
  $group = $groupResult->fetch_assoc();
  $getGroupStmt->close();

  if (!$group) {
    $_SESSION['flash_message'] = "No group with this ID!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
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
    $_SESSION['flash_message'] = $createNewProductStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    $connection->rollback();
    exit;
  }
  $createNewProductStmt->close();

  $connection->commit();
  $_SESSION['flash_message'] = "New Product was created for the group successfully!";
  $_SESSION['flash_type'] = "success";
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  $_SESSION['flash_message'] = "Error in the server!";
  $_SESSION['flash_message'] = $e->getMessage();
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/admin/");

  $errorMessage = $e->getFile() . " | " . $e->getLine() . " | " . $e->getMessage();
  file_put_contents($errorLogsFilePath, $errorMessage, FILE_APPEND);
  exit;
}
