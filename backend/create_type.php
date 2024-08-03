<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $returnPath = "admin/";

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

  $groupId = $_POST["group_id"];

  $getGroupStmt = $connection->prepare("SELECT * FROM `groups` WHERE id = ? LIMIT 1");
  $getGroupStmt->bind_param("i", $groupId);
  $getGroupStmt->execute();
  if ($getGroupStmt->errno) {
    logErrors($getGroupStmt->error, "string");
    showSessionAlert("Error in the getting process!", "danger", true, $returnPath);
    exit;
  }
  $groupResult = $getGroupStmt->get_result();
  $group = $groupResult->fetch_assoc();
  $getGroupStmt->close();

  if (!$group) {
    showSessionAlert("No group with this ID!", "danger", true, $returnPath);
    exit;
  }

  $typeName = $_POST["type_name"];
  $sortIndex = (int)$_POST["sort_index"];
  $price = (float)$_POST["price"];
  // var_dump($price);
  // exit;

  $connection->begin_transaction();
  $createNewProductStmt = $connection->prepare("INSERT INTO `types`(group_id, name, price, sort_index) VALUES (?, ?, ?, ?)");
  $createNewProductStmt->bind_param("isdi", $groupId, $typeName, $price, $sortIndex);
  $createNewProductStmt->execute();
  if ($createNewProductStmt->errno) {
    $connection->rollback();
    logErrors($createNewProductStmt->error, "string");
    showSessionAlert("Error in the creating process!", "danger", true, $returnPath);
    exit;
  }
  $createNewProductStmt->close();

  $connection->commit();
  showSessionAlert("New Type was created for the group successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
