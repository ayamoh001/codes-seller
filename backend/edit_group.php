<?php
include "../include/config.php";

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
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }

  $getGroupStmt = $connection->prepare("SELECT * FROM groups WHERE id = ? LIMIT 1");
  $getGroupStmt->bind_param("i", $groupId);
  $getGroupStmt->execute();
  if ($getGroupStmt->errno) {
    $_SESSION['flash_message'] = $getGroupStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }
  $getGroupStmt->bind_result($group);
  $getGroupStmt->fetch();
  $getGroupStmt->close();

  if (!$group) {
    $_SESSION['flash_message'] = "No group with this ID!";
    $_SESSION['flash_type'] = "danger";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }

  if (!isset($_FILES["image"]) || $_FILES["image"]["error"] != UPLOAD_ERR_OK) {
    $_SESSION['flash_message'] = "No file uploaded or file upload error occurred.";
    $_SESSION['flash_type'] = "danger";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }

  $file_name = $_FILES["image"]["name"];
  $upload_dir = "../storage/groups/"; // storage dir

  $ext = pathinfo($file_name, PATHINFO_EXTENSION);
  $upload_path = $upload_dir . $title . time() . '.' . $ext;
  if (!move_uploaded_file($file_tmp, $upload_path)) {
    $_SESSION['flash_message'] = "Image file not stored successfully!";
    $_SESSION['flash_type'] = "danger";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }

  $title = $_POST["title"];
  $description = $_POST["description"];
  $price = (float) $_POST["price"];

  $createNewGroupStmt = $conn->prepare("UPDATE groups SET title = ?, description = ?, price = ?, image = ? WHERE id = ?");
  $createNewGroupStmt->bind_param("ssdss", $title, $description, $price, $upload_dir, $groupId);
  $createNewGroupStmt->execute();
  if ($createNewGroupStmt->errno) {
    $_SESSION['flash_message'] = $createNewGroupStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
  }
  $createNewGroupStmt->close();

  $_SESSION['flash_message'] = "New group was created successfully!";
  $_SESSION['flash_type'] = "success";
  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
} catch (Throwable $e) {
  echo "Error in the server!";
  echo $e->getMessage();
  exit;
}
