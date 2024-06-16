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
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['flash_message'] = "Not allowed HTTP method!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }
  $user_id = (int) $_POST["user_id"];
  $new_status = $_POST["new_status"];

  $connection->begin_transaction();

  $updateUserStatusStmt = $connection->prepare("UPDATE `users` SET `status` = ? WHERE id = ?");
  $updateUserStatusStmt->bind_param("si", $new_status, $user_id);
  $updateUserStatusStmt->execute();
  if ($updateUserStatusStmt->errno) {
    $_SESSION['flash_message'] = $updateUserStatusStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    $connection->rollback();
    exit;
  }
  $updateUserStatusStmt->close();

  $connection->commit();
  $_SESSION['flash_message'] = "User Status Changed successfully!";
  $_SESSION['flash_type'] = "success";
  header("Location: $baseURL/admin/users.php");
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
