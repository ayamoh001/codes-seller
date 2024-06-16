<?php
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

try {
  if ($_SERVER["REQUEST_METHOD"] !== "POST") { // DELETE is not supported by the HTML forms
    $_SESSION['flash_message'] = "Not allowed HTTP method!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }

  $productId = $_POST["product_id"];

  $connection->begin_transaction();
  $deleteProductStmt = $connection->prepare("DELETE FROM `products` WHERE id = ? AND payment_id IS NULL LIMIT 1");
  $deleteProductStmt->bind_param("i", $productId);
  $deleteProductStmt->execute();
  if ($deleteProductStmt->errno) {
    $_SESSION['flash_message'] = $deleteProductStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    $connection->rollback();
    exit;
  }
  if ($deleteProductStmt->affected_rows == 0) {
    $_SESSION['flash_message'] = "No product was deleted, be sure the product is exists and not sold yet.";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    $connection->rollback();
    exit;
  };

  $connection->commit();
  $_SESSION['flash_message'] = "The product was deleted successfully!";
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
