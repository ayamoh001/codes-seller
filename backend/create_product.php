<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $returnPath = "admin/products.php";

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

  $typeId = $_POST["type_id"];

  $getTypeStmt = $connection->prepare("SELECT * FROM `types` WHERE id = ? LIMIT 1");
  $getTypeStmt->bind_param("i", $typeId);
  $getTypeStmt->execute();
  if ($getTypeStmt->errno) {
    showSessionAlert("Error in the getting process!", "danger", true, $returnPath);
    exit;
  }
  $typeResult = $getTypeStmt->get_result();
  $type = $typeResult->fetch_assoc();
  $getTypeStmt->close();

  if (!$type) {
    showSessionAlert("No type with this ID!", "danger", true, $returnPath);
    exit;
  }

  $codeValue = $_POST["code_value"];

  $connection->begin_transaction();
  $createNewProductStmt = $connection->prepare("INSERT INTO `products`(type_id, code_value) VALUES (?, ?)");
  $createNewProductStmt->bind_param("ss", $typeId, $codeValue);
  $createNewProductStmt->execute();
  if ($createNewProductStmt->errno) {
    showSessionAlert("Error in the creating process!", "danger", true, $returnPath);
    $connection->rollback();
    exit;
  }
  $createNewProductStmt->close();

  $connection->commit();
  showSessionAlert("New Product was created for the type successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
