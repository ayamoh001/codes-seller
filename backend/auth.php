<?php
try {
  require '../include/config.php';
  header('Clear-Site-Data: "cache"');

  if (isset($_POST["do"])) {
    $do = $_POST["do"];

    if ($do == "login") {
      $email = trim($_POST['email']);
      $password = trim($_POST['password']);

      $getUserQuery = "SELECT id, `password` FROM `users` WHERE email = ? AND (status != 'BLOCKED')";
      $stmt = $connection->prepare($getUserQuery);
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
          $_SESSION['user_id'] = $user['id'];
          header("Location: $baseURL/profile/");
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
      $username = trim($_POST['username']);
      $email = trim($_POST['email']);
      $password = trim($_POST['password']);

      $hashPassword = password_hash($password, PASSWORD_BCRYPT, ["cost" => 4]);
      $otp_code = rand(111111, 999999);

      // Check for duplicate email
      $getDuplicateEmailQuery = "SELECT count(*) as count FROM `users` WHERE email = ?";
      $stmt = $connection->prepare($getDuplicateEmailQuery);
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      if ($row['count'] >= 1) {
        $_SESSION['flash_message'] = "Duplicated Email!";
        $_SESSION['flash_type'] = "danger";
        header("location: $baseURL/signup.php");
        exit;
      }

      // Check for duplicate username
      $getDuplicateUsernameQuery = "SELECT count(*) as count FROM `users` WHERE username = ?";
      $stmt = $connection->prepare($getDuplicateUsernameQuery);
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();

      if ($row['count'] >= 1) {
        $_SESSION['flash_message'] = "Duplicated Username!";
        $_SESSION['flash_type'] = "danger";
        header("location: $baseURL/signup.php");
        exit;
      }

      // Insert new user
      $connection->begin_transaction();
      $createUserQuery = "INSERT INTO `users` (username, email, password, otp_code, status) VALUES (?, ?, ?, ?, 'ACTIVE')";
      $stmt = $connection->prepare($createUserQuery);
      $stmt->bind_param("ssss", $username, $email, $hashPassword, $otp_code);
      $stmt->execute();
      if ($stmt->errno) {
        $connection->rollback();
        $_SESSION['flash_message'] = "Failed to create new user! Please try again later.";
        $_SESSION['flash_type'] = "danger";
        header("location: $baseURL/signup.php");
        exit;
      }
      $user_id = $connection->insert_id;
      $_SESSION['user_id'] = $user_id;

      // Create user wallet
      $createUserWalletQuery = "INSERT INTO `wallets` (user_id, balance) VALUES (?, 0)";
      $stmt = $connection->prepare($createUserWalletQuery);
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      if ($stmt->errno) {
        $connection->rollback();
        $_SESSION['flash_message'] = "Failed to create new user wallet! Please try again later.";
        $_SESSION['flash_message'] = $stmt->error;
        $_SESSION['flash_type'] = "danger";
        header("location: $baseURL/signup.php");
        exit;
      }
      // TODO: change the guest ids for this session to the user id

      $connection->commit();
      header("Location: $baseURL/profile/");
      exit;
    } else if ($do == "logout") {
      session_destroy();
      header("Location: $baseURL/login.php");
      exit;
    }
  } else {
    echo "Invalid HTTP method or missing action!";
    exit;
  }
} catch (Throwable $e) {
  $_SESSION['flash_message'] = "Error in the server!";
  $_SESSION['flash_message'] = $e->getMessage();
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/login.php");

  $errorMessage = $e->getFile() . " | " . $e->getLine() . " | " . $e->getMessage();
  file_put_contents($errorLogsFilePath, $errorMessage, FILE_APPEND);
  exit;
}
