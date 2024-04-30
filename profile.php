<?php
include "./include/config.php";

if ($_SESSION["user_id"] == "") {
  $_SESSION['flash_message'] = "You are not logged in!";
  $_SESSION['flash_type'] = "danger";
  header("location: $baseURL/login.php");
  exit;
}

$user_id = $_SESSION["user_id"];
$getUserQuery = "SELECT * FROM users WHERE (id = '$user_id') AND (status != 'BLOCKED')";
$user = $connection->query($getUserQuery)->fetch_assoc();

$title = "إسم الموقع - الحساب";
include "./include/profile/header.php";
?>


<?php
include "./include/profile/footer.php";
?>