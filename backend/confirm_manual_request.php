<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $returnPath = "admin/requests.php";

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
  $request_id = (int) $_POST["request_id"];
  $new_status = "CONFIRMED";

  $connection->begin_transaction();

  $updateRequestStatusStmt = $connection->prepare("UPDATE `requests` SET `status` = ? WHERE id = ?");
  $updateRequestStatusStmt->bind_param("si", $new_status, $request_id);
  $updateRequestStatusStmt->execute();
  if ($updateRequestStatusStmt->errno) {
    $connection->rollback();
    showSessionAlert($updateRequestStatusStmt->error, "danger", true, $returnPath);
    exit;
  }
  $updateRequestStatusStmt->close();

  $connection->commit();
  // TODO: send email to the user

  showSessionAlert("Manual request confirmed successfully!", "success");
  header("Location: $baseURL/admin/requests.php");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
