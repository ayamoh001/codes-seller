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

  $typeId = (int) $_POST["type_id"];

  $getTypeStmt = $connection->prepare("SELECT * FROM `types` WHERE id = ? LIMIT 1");
  $getTypeStmt->bind_param("i", $typeId);
  $getTypeStmt->execute();
  if ($getTypeStmt->errno) {
    showSessionAlert("Error in the getting process!", "danger", true, $returnPath);
    exit;
  }
  $typeResult = $getTypeStmt->get_result();
  $type = $typeResult->fetch_assoc();
  $getTypeStmt->close();

  if (!$type) {
    showSessionAlert("No type with this ID!", "danger", true, $returnPath);
    exit;
  }

  $connection->begin_transaction();

  // check if a file is uploaded otherwise ignore it
  if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
    $file = $_FILES["file"];
    $fileName = $file["name"];
    $fileType = $file["type"];
    $fileSize = $file["size"];
    $fileTmpName = $file["tmp_name"];
    // check if the file is a text file
    if (in_array($fileType, ["text/plain", "text/csv"])) {
      $fileContent = file_get_contents($fileTmpName);
      $fileContent = str_replace("\r\n", "\n", $fileContent);
      $fileContent = explode("\n", $fileContent);
      $fileContent = array_filter($fileContent, 'trim');
      foreach ($fileContent as $code) {
        $code = (string) trim($code);
        if ($code != "" && !empty($code) && !!$code) {
          $createNewProductStmt = $connection->prepare("INSERT INTO `products`(type_id, code_value) VALUES (?, ?)");
          $createNewProductStmt->bind_param("is", $typeId, $code);
          $createNewProductStmt->execute();
          if ($createNewProductStmt->errno) {
            $connection->rollback();
            showSessionAlert("Error in the creating process!", "danger", true, $returnPath);
            exit;
          }
          $createNewProductStmt->close();
        }
      }
    }
  }

  if (isset($_POST["code_value"]) && $_POST["code_value"] != "") {
    $codeValue = $_POST["code_value"];
    $createNewProductStmt = $connection->prepare("INSERT INTO `products`(type_id, code_value) VALUES (?, ?)");
    $createNewProductStmt->bind_param("is", $typeId, $codeValue);
    $createNewProductStmt->execute();
    if ($createNewProductStmt->errno) {
      $connection->rollback();
      showSessionAlert("Error in the creating process!", "danger", true, $returnPath);
      exit;
    }
    $createNewProductStmt->close();
  }

  $connection->commit();
  showSessionAlert("New Product was created for the type successfully!", "success");
  header("Location: $baseURL/admin/");
  exit;
} catch (Throwable $e) {
  showSessionAlert("Error in the server!", "danger", true, $returnPath);
  logErrors($e);
  exit;
}
