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
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }

  $productId = $_POST["product_id"];

  $connection->begin_transaction();
  $deleteProductStmt = $connection->prepare("DELETE FROM products WHERE id = ? LIMIT 1");
  $deleteProductStmt->bind_param("i", $productId);
  $deleteProductStmt->execute();
  if ($deleteProductStmt->errno) {
    $_SESSION['flash_message'] = $deleteProductStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    $connection->rollback();
    exit;
  }

  $connection->commit();
  $_SESSION['flash_message'] = "A code product was deleted successfully!";
  $_SESSION['flash_type'] = "success";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
} catch (Throwable $e) {
  $_SESSION['flash_message'] = "Error in the server! " . $e->getMessage();
  $_SESSION['flash_type'] = "danger";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}
