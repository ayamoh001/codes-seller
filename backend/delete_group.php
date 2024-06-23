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

  $groupId = $_POST["group_id"];

  $connection->begin_transaction();
  $deleteGroupStmt = $connection->prepare("DELETE FROM `groups` WHERE id = ? LIMIT 1");
  $deleteGroupStmt->bind_param("i", $groupId);
  $deleteGroupStmt->execute();
  if ($deleteGroupStmt->errno) {
    $connection->rollback();
    showSessionAlert("Error in the deleting process!", "danger", true, $returnPath);
    exit;
  }
  $deleteGroupStmt->close();

  $deleteRelatedProductsStmt = $connection->prepare("DELETE FROM `products` WHERE group_id = ? AND payments_id = NULL");
  $deleteRelatedProductsStmt->bind_param("i", $groupId);
  $deleteRelatedProductsStmt->execute();
  if ($deleteRelatedProductsStmt->errno) {
    $connection->rollback();
    showSessionAlert("Error in the deleting process!", "danger", true, $returnPath);
    exit;
  }
  $deleteRelatedProductsStmt->close();

  $connection->commit();
  showSessionAlert("Group and its codes were deleted successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
