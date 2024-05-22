<?php
require_once "../include/config.php";

if ($_SESSION["user_id"] == "") {
  echo json_encode(["error" => "Not Athenticated!"]);
  exit;
}

try {
  $user_id = (int) $_SESSION["user_id"];
  $getUserStmt = $connection->prepare("SELECT * FROM users WHERE id = ? AND status != 'BLOCKED' LIMIT 1");
  $getUserStmt->bind_param("i", $user_id);
  $getUserStmt->execute();
  if ($getUserStmt->errno) {
    echo json_encode(["error" => "Error in the auth proccess! please try again."]);
    echo json_encode(["error" => $getUserStmt->error]);
    exit;
  }
  $userResult = $getUserStmt->get_result();
  $user = $userResult->fetch_assoc();
  $getUserStmt->close();


  // start the process

} catch (Throwable $e) {
  echo json_encode(["error" => "Error in the server!"]);
  echo json_encode(["error" => $e->getMessage()]);
}
