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

  if ($_SERVER["REQUEST_METHOD"] !== "POST") { // DELETE is not supported by the HTML forms
    showSessionAlert("Not allowed HTTP method!", "danger", true, $returnPath);
    exit;
  }

  $productId = $_POST["product_id"];

  $connection->begin_transaction();
  $deleteProductStmt = $connection->prepare("DELETE FROM `products` WHERE id = ? AND payment_id IS NULL LIMIT 1");
  $deleteProductStmt->bind_param("i", $productId);
  $deleteProductStmt->execute();
  if ($deleteProductStmt->errno) {
    showSessionAlert($deleteProductStmt->error, "danger", true, $returnPath);
    $connection->rollback();
    exit;
  }
  if ($deleteProductStmt->affected_rows == 0) {
    showSessionAlert("No product was deleted, be sure the product is exists and not sold yet.", "danger", true, $returnPath);
    $connection->rollback();
    exit;
  };

  $connection->commit();
  showSessionAlert("The product was deleted successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
