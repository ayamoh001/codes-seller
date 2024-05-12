<?php
include "../include/config.php";

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

try {
  if ($_SERVER["REQUEST_METHOD"] !== "POST") { // DELETE is not supported by the HTML forms
    $_SESSION['flash_message'] = "Not allowed HTTP method!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }

  $groupId = $_POST["group_id"];

  $connection->begin_transaction();
  $deleteGroupStmt = $connection->prepare("DELETE FROM groups WHERE id = ? LIMIT 1");
  $deleteGroupStmt->bind_param("i", $groupId);
  $deleteGroupStmt->execute();
  if ($deleteGroupStmt->errno) {
    $_SESSION['flash_message'] = $deleteGroupStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    $connection->rollback();
    exit;
  }
  $deleteGroupStmt->close();

  $deleteRelatedProductsStmt = $connection->prepare("DELETE FROM products WHERE group_id = ? AND payments_id = NULL");
  $deleteRelatedProductsStmt->bind_param("i", $groupId);
  $deleteRelatedProductsStmt->execute();
  if ($deleteRelatedProductsStmt->errno) {
    $_SESSION['flash_message'] = $deleteRelatedProductsStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    $connection->rollback();
    exit;
  }
  $deleteRelatedProductsStmt->close();

  $connection->commit();
  $_SESSION['flash_message'] = "Group and its codes were deleted successfully!";
  $_SESSION['flash_type'] = "success";
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  $_SESSION['flash_message'] = "Error in the server! " . $e->getMessage();
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/admin/");
  exit;
}
