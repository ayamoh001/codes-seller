<?php
try {
  require_once "../include/config.php";
  require_once "../include/functions.php";

  $returnPath = "admin/index.php";

  if (
    !isset($_SERVER['PHP_AUTH_USER']) ||
    !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== $adminUsername ||
    $_SERVER['PHP_AUTH_PW'] !== $adminPassword
  ) {
    header('WWW-Authenticate: Basic realm="Restricted Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentication required.';
    exit;
  }

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    showSessionAlert("Not allowed HTTP method!", "danger", true, $returnPath);
    exit;
  }

  $groupId = $_POST["group_id"];

  $getGroupStmt = $connection->prepare("SELECT * FROM `groups` WHERE id = ? LIMIT 1");
  $getGroupStmt->bind_param("i", $groupId);
  $getGroupStmt->execute();
  if ($getGroupStmt->errno) {
    showSessionAlert($getGroupStmt->error, "danger", true, $returnPath);
    exit;
  }
  $groupResult = $getGroupStmt->get_result();
  $group = $groupResult->fetch_assoc();
  $getGroupStmt->close();

  if (!$group) {
    showSessionAlert("No group with this ID!", "danger", true, $returnPath);
    exit;
  }

  $uploadPathRelative = $group["image"];

  // Check if an image is uploaded and handle the process if it exists
  if (isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
    $file_name = $_FILES["image"]["name"];
    $file_tmp = $_FILES["image"]["tmp_name"];

    $storageDirAbsolute = __DIR__ . "/../storage/groups/";
    $storageDirRelative = "/storage/groups/";

    if (!is_dir($storageDirAbsolute)) {
      mkdir($storageDirAbsolute, 0777, true);
    }

    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $newFileName = $title . time() . '.' . $ext;

    $uploadPathAbsolute = $storageDirAbsolute . $newFileName;
    $uploadPathRelative = $storageDirRelative . $newFileName;

    if (!move_uploaded_file($file_tmp, $uploadPathAbsolute)) {
      showSessionAlert("Image file not stored successfully!", "danger", true, $returnPath);
      exit;
    }
  }

  $title = $_POST["title"];
  $description = $_POST["description"];
  $sortIndex = $_POST["sort_index"];
  $visibility = (int) (bool) $_POST["visibility"];
  // var_dump($visibility);
  // exit;

  $connection->begin_transaction();
  $createNewGroupStmt = $connection->prepare("UPDATE `groups` SET title = ?, description = ?, sort_index = ?, visibility = ?, image = ? WHERE id = ?");
  $createNewGroupStmt->bind_param("ssiisi", $title, $description, $sortIndex, $visibility, $uploadPathRelative, $groupId);
  $createNewGroupStmt->execute();
  if ($createNewGroupStmt->errno) {
    $connection->rollback();
    showSessionAlert($createNewGroupStmt->error, "danger", true, $returnPath);
    exit;
  }
  $createNewGroupStmt->close();

  $connection->commit();
  showSessionAlert("The group was edited successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
