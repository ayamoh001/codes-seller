<?php
try {
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

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['flash_message'] = "Not allowed HTTP method!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }

  if (!isset($_FILES["image"]) || $_FILES["image"]["error"] != UPLOAD_ERR_OK) {
    $_SESSION['flash_message'] = "No file uploaded or file upload error occurred.";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
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
    $_SESSION['flash_message'] = "Image file not stored successfully!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }

  $connection->begin_transaction();
  $createNewGroupStmt = $connection->prepare("INSERT INTO `groups`(title, description, image, sort_index, visibility) VALUES (?, ?, ?, ?, ?)");
  $createNewGroupStmt->bind_param("sssii", $title, $description, $uploadPathRelative, $sortIndex, $visibility);
  $createNewGroupStmt->execute();
  if ($createNewGroupStmt->errno) {
    $_SESSION['flash_message'] = $createNewGroupStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    $connection->rollback();
    exit;
  }
  $createNewGroupStmt->close();

  $connection->commit();
  $_SESSION['flash_message'] = "New group was created successfully!";
  $_SESSION['flash_type'] = "success";
  header("Location: $baseURL/admin/");
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
