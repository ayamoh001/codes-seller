<?php
require_once "../include/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  $_SESSION['flash_message'] = "Not allowed HTTP method!";
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/profile/index.php");
  exit;
}

if ($_SESSION["user_id"] == "") {
  $_SESSION['flash_message'] = "Not Athenticated!";
  $_SESSION['flash_type'] = "danger";
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
    $_SESSION['flash_type'] = "danger";
    exit;
  }
  $userResult = $getUserStmt->get_result();
  $user = $userResult->fetch_assoc();
  if (!$user) {
    $_SESSION['flash_message'] = "No user found! Please login in first.";
    $_SESSION['flash_type'] = "danger";
    exit;
  }
  $getUserStmt->close();

  $uploadPathRelative = $user["profile_picture"];
  if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == UPLOAD_ERR_OK) {
    $file_name = $_FILES["profile_picture"]["name"];
    $file_tmp = $_FILES["profile_picture"]["tmp_name"];

    $storageDirAbsolute = __DIR__ . "/../storage/profile_pictures/";
    $storageDirRelative = "/storage/profile_pictures/";
    if (!is_dir($storageDirAbsolute)) {
      mkdir($storageDirAbsolute, 0777, true);
    }

    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $newFileName = $title . time() . '.' . $ext;

    $uploadPathAbsolute = $storageDirAbsolute . $newFileName;
    $uploadPathRelative = $storageDirRelative . $newFileName;

    if (!move_uploaded_file($file_tmp, $uploadPathAbsolute)) {
      $_SESSION['flash_message'] = "Image wasn't stored successfully!";
      $_SESSION['flash_type'] = "danger";
      header("Location: $baseURL/profile/index.php");
      exit;
    }
  }
  // for debugging
  // else {
  //   $_SESSION['flash_message'] = "No Image Selected!";
  //   $_SESSION['flash_type'] = "danger";
  //   header("Location: $baseURL/profile/index.php");
  //   exit;
  // }

  $username = $user["username"];
  if (isset($_POST["username"]) && $_POST["username"] != "") {
    $newUsername = $_POST["username"];
    $checkUsernameUniquenessStmt = $connection->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $checkUsernameUniquenessStmt->bind_param("s", $newUsername);
    $checkUsernameUniquenessStmt->execute();
    if ($checkUsernameUniquenessStmt->errno) {
      $_SESSION['flash_message'] = "Error while checking username uniqueness!";
      $_SESSION['flash_type'] = "danger";
      header("Location: $baseURL/profile/index.php");
      exit;
    }
    $checkUsernameUniquenessResult = $checkUsernameUniquenessStmt->get_result();
    if ($checkUsernameUniquenessResult->num_rows > 0) {
      $_SESSION['flash_message'] = "Username already exists!";
      $_SESSION['flash_type'] = "danger";
      header("Location: $baseURL/profile/index.php");
      exit;
    }
    $checkUsernameUniquenessStmt->close();
    $username = $newUsername;
  }


  $hashPassword = $user["password"];

  if (isset($_POST["new_password"]) && $_POST["new_password"] != "") {
    $newPassword = $_POST["new_password"];
    $newPasswordConfirm = $_POST["new_password_confirm"];

    if ($newPassword != $newPasswordConfirm) {
      $_SESSION['flash_message'] = "Passwords don't match!";
      $_SESSION['flash_type'] = "danger";
      header("Location: $baseURL/profile/index.php");
      exit;
    } else {
      $hashPassword = password_hash($newPassword, PASSWORD_BCRYPT, ["cost" => 4]);
    }
  }

  $connection->begin_transaction();

  $updateUserProfilePictureStmt = $connection->prepare("UPDATE users SET username = ?, `password` = ?, profile_picture = ? WHERE id = ?");
  $updateUserProfilePictureStmt->bind_param("sssi", $username,  $hashPassword, $uploadPathRelative, $user_id);
  $updateUserProfilePictureStmt->execute();
  if ($updateUserProfilePictureStmt->errno) {
    $_SESSION['flash_message'] = "Error in the updating process!";
    $_SESSION['flash_message'] = $updateUserProfilePictureStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/profile/index.php");
    $connection->rollback();
    exit;
  }
  $updateUserProfilePictureStmt->close();

  $connection->commit();

  $_SESSION['flash_message'] = "Your Account index were updated successfuly!";
  $_SESSION['flash_type'] = "success";
  header("Location: $baseURL/profile/index.php");
  exit;
} catch (Throwable $e) {
  $_SESSION['flash_message'] = "Error in the server!";
  $_SESSION['flash_message'] = $e->getMessage();
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/profile/index.php");

  $errorMessage = $e->getFile() . " | " . $e->getLine() . " | " . $e->getMessage();
  file_put_contents($errorLogsFilePath, $errorMessage, FILE_APPEND);
  exit;
}
