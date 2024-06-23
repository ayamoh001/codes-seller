<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $_SERVER['PHP_AUTH_USER'] = "";
  $_SERVER['PHP_AUTH_PW'] = "";

  header("Location: $baseURL");
} catch (Throwable $e) {
  $returnPath = "admin/login.php";
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
