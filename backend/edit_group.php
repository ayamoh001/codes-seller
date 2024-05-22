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

  $getGroupStmt = $connection->prepare("SELECT * FROM groups WHERE id = ? LIMIT 1");
  $getGroupStmt->bind_param("i", $groupId);
  $getGroupStmt->execute();
  if ($getGroupStmt->errno) {
    $_SESSION['flash_message'] = $getGroupStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }
  $groupResult = $getGroupStmt->get_result();
  $group = $groupResult->fetch_assoc();
  $getGroupStmt->close();

  if (!$group) {
    $_SESSION['flash_message'] = "No group with this ID!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }

  // Check if an image is uploaded and handle the process if it exists
  if (isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
    $file_name = $_FILES["image"]["name"];
    $file_tmp = $_FILES["image"]["tmp_name"];
    $upload_dir = "../storage/groups/"; // storage dir

    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $upload_path = $upload_dir . $title . time() . '.' . $ext;

    // Move the uploaded file to the destination directory
    if (!move_uploaded_file($file_tmp, $upload_path)) {
      $_SESSION['flash_message'] = "Image file not stored successfully!";
      $_SESSION['flash_type'] = "danger";
      header("Location: $baseURL/admin/");
      exit;
    }
  }

  $title = $_POST["title"];
  $description = $_POST["description"];
  $sortIndex = $_POST["sort_index"];

  $connection->begin_transaction();
  $createNewGroupStmt = $connection->prepare("UPDATE groups SET title = ?, description = ?, sort_index = ?, image = ? WHERE id = ?");
  $createNewGroupStmt->bind_param("ssisi", $title, $description, $sortIndex, $upload_dir, $groupId);
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
