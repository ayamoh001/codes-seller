<?php
require_once "../include/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  $_SESSION['flash_message'] = "Not allowed HTTP method!";
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/profile/edit_profile/");
  exit;
}

if ($_SESSION["user_id"] == "") {
  $_SESSION['flash_message'] = "Not Athenticated!";
  exit;
}

try {
  // get the user
  $user_id = (int) $_SESSION["user_id"];
  $getUserStmt = $connection->prepare("SELECT * FROM users WHERE id = ? AND status != 'BLOCKED' LIMIT 1");
  $getUserStmt->bind_param("i", $user_id);
  $getUserStmt->execute();
  if ($getUserStmt->errno) {
    $_SESSION['flash_message'] = "Error in the auth proccess! please try again.";
    $_SESSION['flash_message'] = $getUserStmt->error;
    exit;
  }
  $userResult = $getUserStmt->get_result();
  $user = $userResult->fetch_assoc();
  $getUserStmt->close();

  if (!isset($_FILES["profile_picture"]) || $_FILES["profile_picture"]["error"] != UPLOAD_ERR_OK) {
    $_SESSION['flash_message'] = "No file uploaded or file upload error occurred.";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/profile/edit_profile/");
    exit;
  }

  $file_name = $_FILES["profile_picture"]["name"];
  $upload_dir = "../storage/profile_pictures/"; // storage dir

  $ext = pathinfo($file_name, PATHINFO_EXTENSION);
  $upload_path = $upload_dir . $title . time() . '.' . $ext;
  if (!move_uploaded_file($file_tmp, $upload_path)) {
    $_SESSION['flash_message'] = "Profile picture file not stored successfully!";
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/profile/edit_profile/");
    exit;
  }

  $connection->begin_transaction();
  $updateUserProfilePictureStmt = $connection->prepare("UPDATE groups SET profile_picture = ? WHERE id = ?");
  $updateUserProfilePictureStmt->bind_param("ss", $upload_dir, $user_id);
  $updateUserProfilePictureStmt->execute();
  if ($updateUserProfilePictureStmt->errno) {
    $_SESSION['flash_message'] = $updateUserProfilePictureStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/profile/edit_profile/");
    $connection->rollback();
    exit;
  }
  $updateUserProfilePictureStmt->close();

  $connection->commit();
  $_SESSION['flash_message'] = "Your profile picture was uploaded successfuly!";
  $_SESSION['flash_type'] = "success";
  header("Location: $baseURL/profile/edit_profile/");
  exit;
} catch (Throwable $e) {
  $_SESSION['flash_message'] = "Error in the server!";
  $_SESSION['flash_message'] = $e->getMessage();
  exit;
}
