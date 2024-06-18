<?php
try {
  require_once "../include/config.php";

  $_SERVER['PHP_AUTH_USER'] = "";
  $_SERVER['PHP_AUTH_PW'] = "";

  header("Location: $baseURL");
} catch (Throwable $e) {
  $_SESSION['flash_message'] = "Error in the server!";
  $_SESSION['flash_message'] = $e->getMessage();
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/admin/login.php");

  $errorMessage = $e->getFile() . " | " . $e->getLine() . " | " . $e->getMessage();
  file_put_contents($errorLogsFilePath, $errorMessage, FILE_APPEND);
  exit;
}
