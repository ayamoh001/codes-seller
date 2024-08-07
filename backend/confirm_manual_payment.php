<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $returnPath = "admin/manual_payments.php";

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

  $manualPaymentId = (int) $_POST["payment_id"];
  $userId = (string) $_POST["user_id"];
  $newStatus = "PAID";

  $getManualPaymentStmt = $connection->prepare("SELECT * FROM `payments` WHERE id = ? and user_id = ? LIMIT 1");
  $getManualPaymentStmt->bind_param("is", $manualPaymentId, $userId);
  $getManualPaymentStmt->execute();
  if ($getManualPaymentStmt->errno) {
    $connection->rollback();
    logErrors($getManualPaymentStmt->error, "string");
    showSessionAlert($getManualPaymentStmt->error, "danger", true, $returnPath);
    exit;
  }
  $manualPaymentResult = $getManualPaymentStmt->get_result();
  $manualPayment = $manualPaymentResult->fetch_assoc();
  $getManualPaymentStmt->close();

  if (!$manualPayment) {
    showSessionAlert("No payment with this ID!", "danger", true, $returnPath);
    exit;
  }

  $connection->begin_transaction();

  $typeId = $manualPayment["type_id"];
  $quantity = $manualPayment["quantity"];

  $updateManualPaymentStatusStmt = $connection->prepare("UPDATE `payments` SET `status` = ? WHERE id = ? AND `status` != 'PAID' AND user_id = ?");
  $updateManualPaymentStatusStmt->bind_param("sis", $newStatus, $manualPaymentId, $userId);
  $updateManualPaymentStatusStmt->execute();
  if ($updateManualPaymentStatusStmt->errno) {
    $connection->rollback();
    logErrors($updateManualPaymentStatusStmt->error, "string");
    showSessionAlert($updateManualPaymentStatusStmt->error, "danger", true, $returnPath);
    exit;
  }
  $updateManualPaymentStatusStmt->close();

  $connection->commit();

  $result = linkProductsWithPaymentOrReturnExistings($manualPaymentId, $userId, $typeId, $quantity, $returnPath);
  $products = $result[0];
  $errors = $result[1];

  showSessionAlert("Manual request confirmed successfully!", "success");
  header("Location: $baseURL/admin/manual_payments.php");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
