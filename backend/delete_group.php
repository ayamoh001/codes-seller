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

  $groupId = $_POST["group_id"];

  $deleteGroupStmt = $connection->prepare("DELETE FROM groups WHERE id = ? LIMIT 1");
  $deleteGroupStmt->bind_param("i", $groupId);
  $deleteGroupStmt->execute();
  if ($deleteGroupStmt->errno) {
    $_SESSION['flash_message'] = $deleteGroupStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }

  // Still thinking
  // $deleteProductsStmt = $connection->prepare("DELETE FROM products WHERE group_id = ? AND sold != 1");
  // $deleteProductsStmt->bind_param("i", $groupId);
  // $deleteProductsStmt->execute();
  // if ($deleteProductsStmt->errno) {
  //   $_SESSION['flash_message'] = $deleteProductsStmt->error;
  //   $_SESSION['flash_type'] = "danger";
  //   header("Location: " . $_SERVER['HTTP_REFERER']);
  //   exit;
  // }

  $_SESSION['flash_message'] = "Group and its codes were deleted successfully!";
  $_SESSION['flash_type'] = "success";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
} catch (Throwable $e) {
  echo "Error in the server!";
  echo $e->getMessage();
}
