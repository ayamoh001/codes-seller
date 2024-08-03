<?php
require_once "../include/config.php";
require_once "../include/functions.php";

try {
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

  if (!isset($_FILES["image"]) || $_FILES["image"]["error"] != UPLOAD_ERR_OK) {
    showSessionAlert("No file uploaded or file upload error occurred.", "danger", true, $returnPath);
    exit;
  }

  $title = $_POST["title"];
  $description = $_POST["description"];
  $sortIndex = $_POST["sort_index"];
  $visibility = (int) (bool) $_POST["visibility"];
  $file_name = $_FILES["image"]["name"];
  $file_tmp = $_FILES["image"]["tmp_name"];

  $storageDirAbsolute = __DIR__ . "/../storage/groups/";
  $storageDirRelative = "/storage/groups/";
  if (!is_dir($storageDirAbsolute)) {
    mkdir($storageDirAbsolute, 0777, true);
  }

  $ext = pathinfo($file_name, PATHINFO_EXTENSION);
  $newFileName = $title . time() . '.' . $ext;

  $uploadPathAbsolute = $storageDirAbsolute . $newFileName;
  $uploadPathRelative = $storageDirRelative . $newFileName;

  if (!move_uploaded_file($file_tmp, $uploadPathAbsolute)) {
    showSessionAlert("Image file not stored successfully!", "danger", true, $returnPath);
    exit;
  }

  $connection->begin_transaction();
  $createNewGroupStmt = $connection->prepare("INSERT INTO `groups`(title, description, image, sort_index, visibility) VALUES (?, ?, ?, ?, ?)");
  $createNewGroupStmt->bind_param("sssii", $title, $description, $uploadPathRelative, $sortIndex, $visibility);
  $createNewGroupStmt->execute();
  if ($createNewGroupStmt->errno) {
    $connection->rollback();
    logErrors($createNewGroupStmt->error, "string");
    showSessionAlert($createNewGroupStmt->error, "danger", true, $returnPath);
    exit;
  }
  $createNewGroupStmt->close();

  $connection->commit();
  showSessionAlert("New group was created successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
