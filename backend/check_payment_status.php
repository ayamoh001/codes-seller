<?php
include "../include/config.php";

if ($_SESSION["user_id"] == "") {
  echo json_encode(["error" => "Not Athenticated!"]);
  exit;
}

$user_id = $_SESSION["user_id"];
$getUserQuery = "SELECT * FROM users WHERE (id = '$user_id') AND (status != 'BLOCKED')";
$user = $connection->query($getUserQuery)->fetch_assoc();

try {
  // logic here
} catch (Throwable $e) {
  echo json_encode(["error" => "Error in the server!"]);
  echo json_encode(["error" => $e->getMessage()]);
}
