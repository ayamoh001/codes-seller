<?php
require '../include/config.php';
header('Clear-Site-Data: "cache"');

try {
  if (isset($_POST["do"])) {
    $do = $_POST["do"];

    if ($do == "login") {
      $email = $connection->escape_string(trim($_POST['email']));
      $password = $connection->escape_string(trim($_POST['password']));

      $getUserQuery = "SELECT id, `password` FROM users WHERE email = '$email' AND (status != 'BLOCKED')";
      $users = $connection->query($getUserQuery);
      if ($users->num_rows > 0) {
        $user = $users->fetch_assoc();
        if (password_verify($password, $user['password'])) {
          $_SESSION['user_id'] = $user['id'];
          header("Location: $baseURL/profile.php");
        } else {
          $_SESSION['flash_message'] = "Invalid Credentials!";
          $_SESSION['flash_type'] = "danger";
          header("Location: $baseURL/login.php");
        }
      } else {
        $_SESSION['flash_message'] = "Invalid Credentials!";
        $_SESSION['flash_type'] = "danger";
        header("Location: $baseURL/login.php");
      }
    } else if ($do == "signup") {
      $username = $connection->escape_string(trim($_POST['username']));
      $email = $connection->escape_string(trim($_POST['email']));
      $password = $connection->escape_string(trim($_POST['password']));

      $hashPassword = password_hash($password, PASSWORD_BCRYPT, ["cost" => 4]);
      $otp_code = rand(111111, 999999);

      // duplicat email
      $getDuplicateEmailQuery = $connection->query("SELECT count(*) FROM users WHERE email = '$email' LIMIT 1")->fetch_array();
      if ($getDuplicateEmailQuery['count(*)'] >= 1) {
        $_SESSION['flash_message'] = "Duplicated Email!";
        $_SESSION['flash_type'] = "danger";
        header("location: $baseURL/signup.php");
        exit;
      }
      // duplicat username
      $getDuplicateUsernameQuery = $connection->query("SELECT count(*) FROM users WHERE username = '$username' LIMIT 1")->fetch_array();
      if ($getDuplicateUsernameQuery['count(*)'] >= 1) {
        $_SESSION['flash_message'] = "Duplicated Username!";
        $_SESSION['flash_type'] = "danger";
        header("location: $baseURL/signup.php");
        exit;
      }

      // if (isset($_FILES["cat_image"]["name"])) {
      //   $target_dir = '../uploads/';
      //   $file_name = round(microtime(true)) . rand(99, 9999);
      //   $filetype = explode(".", $_FILES['cat_image']['name']);
      //   $filetype = end($filetype);
      //   if (in_array(strtolower($filetype), ["pdf", "jpg", "jpeg", "png", "webp", "txt", "doc", "docx"])) {
      //     if (move_uploaded_file($_FILES["cat_image"]["tmp_name"], $target_dir . $file_name . '.' . $filetype)) {
      //       $cat_image = $file_name . '.' . $filetype;
      //       $cat_image = $connection->escape_string($cat_image);
      //     }
      //   } else {
      //     $_SESSION['flash_message'] = "Duplicated Email!";
      //     $_SESSION['flash_type'] = "danger";
      //     header("location: $baseURL/signup.php");
      //     exit;
      //   }
      // } else {
      //   $cat_image = '';
      //   $_SESSION['flash_message'] = "Duplicated Email!";
      //   $_SESSION['flash_type'] = "danger";
      //   header("location: $baseURL/signup.php");
      //   exit;
      // }

      $createUserQuery = "INSERT INTO users (username, email, password, otp_code, status) VALUES ('$username', '$email', '$hashPassword', '$otp_code', 'UNVERIFIED')";
      if ($connection->query($createUserQuery) === TRUE) {
        $user_id = $connection->insert_id;
        $_SESSION['user_id'] = $user_id;

        // creat user wallet TODO: later
        // $createUserWalletQuery = "INSERT INTO wallet (user_id, balance) VALUES ('$user_id', 0)";
        // if ($connection->query($createUserWalletQuery) === TRUE) {
        header("Location: $baseURL/profile.php");
        exit;
        // }
      } else {
        $_SESSION['flash_message'] = "Faild to create new user! Please try again later.";
        $_SESSION['flash_type'] = "danger";
        header("location: $baseURL/signup.php");
        exit;
      }
    } else if ($do == "logout") {
      session_destroy();
      header("Location: $baseURL/login.php");
      exit;
    }
  } else if (isset($_GET["do"])) {
    $do = $_GET["do"];
  } else {
    echo "Invalide HTTP method or missing action!";
    exit;
  }
} catch (Throwable $e) {
  $_SESSION['flash_message'] = "Error in the server! " . $e->getMessage();
  $_SESSION['flash_type'] = "danger";

  isset($_SERVER['HTTP_REFERER']) ? header("Location: " . $_SERVER['HTTP_REFERER']) : header("Location: $baseURL");
  exit;
}
