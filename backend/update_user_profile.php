<?php
require_once "../include/config.php";
require_once "../include/functions.php";

try {
  $returnPath = "profile/settings.php";

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    showSessionAlert("Not allowed HTTP method!", "danger", true, $returnPath);
    exit;
  }

  if ($_SESSION["user_id"] == "") {
    showSessionAlert("Not Athenticated!", "danger", true, $returnPath);
    exit;
  }

  // get the user
  $user_id = (int) $_SESSION["user_id"];
  $user = getUser($user_id, $returnPath);

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
      showSessionAlert("Image wasn't stored successfully!", "danger", true, $returnPath);
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
    $checkUsernameUniquenessStmt = $connection->prepare("SELECT id FROM `users` WHERE username = ? LIMIT 1");
    $checkUsernameUniquenessStmt->bind_param("s", $newUsername);
    $checkUsernameUniquenessStmt->execute();
    if ($checkUsernameUniquenessStmt->errno) {
      showSessionAlert("Error while checking username uniqueness!", "danger", true, $returnPath);
      exit;
    }
    $checkUsernameUniquenessResult = $checkUsernameUniquenessStmt->get_result();
    if ($checkUsernameUniquenessResult->num_rows > 0) {
      showSessionAlert("Username already exists!", "danger", true, $returnPath);
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
      showSessionAlert("Passwords don't match!", "danger", true, $returnPath);
      exit;
    } else {
      $hashPassword = password_hash($newPassword, PASSWORD_BCRYPT, ["cost" => 4]);
    }
  }

  $connection->begin_transaction();

  $updateUserProfilePictureStmt = $connection->prepare("UPDATE `users` SET username = ?, `password` = ?, profile_picture = ? WHERE id = ?");
  $updateUserProfilePictureStmt->bind_param("sssi", $username,  $hashPassword, $uploadPathRelative, $user_id);
  $updateUserProfilePictureStmt->execute();
  if ($updateUserProfilePictureStmt->errno) {
    $connection->rollback();
    showSessionAlert("Error in the updating process!", "danger", true, $returnPath);
    exit;
  }
  $updateUserProfilePictureStmt->close();

  $connection->commit();

  showSessionAlert("Your Account settings were updated successfuly!", "success");
  header("Location: $baseURL/profile/settings.php");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
