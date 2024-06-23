<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $groups = getGroups();

  // var_dump($groups);
  // exit;
  echo json_encode($groups);
} catch (Throwable $e) {
  echo json_encode(["error" => "Error in the server!"]);
  logErrors($e);
  exit;
}
