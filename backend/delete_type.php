<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $returnPath = "admin/index.php";

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

  $typeId = $_POST["type_id"];

  $connection->begin_transaction();
  $deleteTypeStmt = $connection->prepare("DELETE FROM `types` WHERE id = ? LIMIT 1");
  $deleteTypeStmt->bind_param("i", $typeId);
  $deleteTypeStmt->execute();
  if ($deleteTypeStmt->errno) {
    $connection->rollback();
    logErrors($deleteTypeStmt->error, "string");
    showSessionAlert("Error in the deleting process!", "danger", true, $returnPath);
    exit;
  }
  $deleteTypeStmt->close();

  $deleteRelatedProductsStmt = $connection->prepare("DELETE FROM `products` WHERE type_id = ?");
  $deleteRelatedProductsStmt->bind_param("i", $typeId);
  $deleteRelatedProductsStmt->execute();
  if ($deleteRelatedProductsStmt->errno) {
    $connection->rollback();
    logErrors($deleteRelatedProductsStmt->error, "string");
    showSessionAlert("Error in the deleting process!", "danger", true, $returnPath);
    exit;
  }
  $deleteRelatedProductsStmt->close();

  $connection->commit();
  showSessionAlert("Type and its codes were deleted successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
