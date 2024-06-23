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
  $new_status = $_POST["new_status"];

  $connection->begin_transaction();

  $updateUserStatusStmt = $connection->prepare("UPDATE `users` SET `status` = ? WHERE id = ?");
  $updateUserStatusStmt->bind_param("si", $new_status, $user_id);
  $updateUserStatusStmt->execute();
  if ($updateUserStatusStmt->errno) {
    $connection->rollback();
    showSessionAlert($updateUserStatusStmt->error, "danger", true, $returnPath);
    exit;
  }
  $updateUserStatusStmt->close();

  $connection->commit();
  showSessionAlert("User Status Changed successfully!", "success");
  header("Location: $baseURL/admin/users.php");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
