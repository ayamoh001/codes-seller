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

  if (!isset($_FILES["image"]) || $_FILES["image"]["error"] != UPLOAD_ERR_OK) {
    $_SESSION['flash_message'] = "No file uploaded or file upload error occurred.";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }

  $title = $_POST["title"];
  $description = $_POST["description"];
  $sortIndex = $_POST["sort_index"];
  $file_name = $_FILES["image"]["name"];
  $file_tmp = $_FILES["image"]["tmp_name"];
  // var_dump($_FILES["image"]);
  $uploadDirectory = "../storage/groups/"; // storage dir

  $ext = pathinfo($file_name, PATHINFO_EXTENSION);
  $upload_path = $uploadDirectory . $title . time() . '.' . $ext;
  if (!move_uploaded_file($file_tmp, $upload_path)) {
    $_SESSION['flash_message'] = "Image file not stored successfully!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }

  $connection->begin_transaction();
  $createNewGroupStmt = $connection->prepare("INSERT INTO groups(title, description, image, sort_index) VALUES (?, ?, ?, ?)");
  $createNewGroupStmt->bind_param("sssi", $title, $description, $uploadDirectory, $sortIndex);
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
  $_SESSION['flash_message'] = "Error in the server! " . $e->getMessage();
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/admin/");
  exit;
}
