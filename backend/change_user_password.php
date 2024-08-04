<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $returnPath = "admin/users.php";

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
  $user_id = (int) $_POST["user_id"];
  $newPassword = $_POST["newPassword"];

  $newHashPassword = password_hash($newPassword, PASSWORD_BCRYPT, ["cost" => 4]);
  $connection->begin_transaction();

  $updateUserStatusStmt = $connection->prepare("UPDATE `users` SET `password` = ? WHERE id = ?");
  $updateUserStatusStmt->bind_param("si", $newHashPassword, $user_id);
  $updateUserStatusStmt->execute();
  if ($updateUserStatusStmt->errno) {
    $connection->rollback();
    logErrors($updateUserStatusStmt->error, "string");
    showSessionAlert($updateUserStatusStmt->error, "danger", true, $returnPath);
    exit;
  }
  $updateUserStatusStmt->close();

  $connection->commit();
  showSessionAlert("User Password Changed successfully!", "success");
  header("Location: $baseURL/admin/users.php");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
