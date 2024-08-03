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

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    showSessionAlert("Not allowed HTTP method!", "danger", true, $returnPath);
    exit;
  }

  $typeId = (int) $_POST["type_id"];

  $getTypeStmt = $connection->prepare("SELECT * FROM `types` WHERE id = ? LIMIT 1");
  $getTypeStmt->bind_param("i", $typeId);
  $getTypeStmt->execute();
  if ($getTypeStmt->errno) {
    logErrors($getTypeStmt->error, "string");
    showSessionAlert($getTypeStmt->error, "danger", true, $returnPath);
    exit;
  }
  $typeResult = $getTypeStmt->get_result();
  $type = $typeResult->fetch_assoc();
  $getTypeStmt->close();

  if (!$type) {
    showSessionAlert("No Type with this ID!", "danger", true, $returnPath);
    exit;
  }

  $name = $_POST["name"];
  $price = (float) $_POST["price"];
  $sortIndex = (int) $_POST["sort_index"];

  $connection->begin_transaction();
  $createNewTypeStmt = $connection->prepare("UPDATE `types` SET name = ?, price = ?, sort_index = ? WHERE id = ?");
  $createNewTypeStmt->bind_param("sdii", $name, $price, $sortIndex, $typeId);
  $createNewTypeStmt->execute();
  if ($createNewTypeStmt->errno) {
    $connection->rollback();
    logErrors($createNewTypeStmt->error, "string");
    showSessionAlert($createNewTypeStmt->error, "danger", true, $returnPath);
    exit;
  }
  $createNewTypeStmt->close();

  $connection->commit();
  showSessionAlert("The group was edited successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
