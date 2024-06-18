<?php
try {
  require_once "../include/config.php";

  $getGroupsWithProuductsStmt = $connection->prepare("SELECT g.*, p.id AS product_id, p.type, p.price
                                                    FROM `groups` g
                                                    LEFT JOIN `products` p ON g.id = p.group_id
                                                    WHERE p.payment_id IS NULL
                                                    ORDER BY g.sort_index, g.id");

  $getGroupsWithProuductsStmt->execute();
  if ($getGroupsWithProuductsStmt->errno) {
    $_SESSION['flash_message'] = $getGroupsWithProuductsStmt->error;
    $_SESSION['flash_type'] = "danger";
    header("Location: $baseURL/admin/");
    exit;
  }
  $getGroupsWithProuductsResults = $getGroupsWithProuductsStmt->get_result();
  $getGroupsWithProuductsStmt->close();

  $groups = [];

  while ($row = $getGroupsWithProuductsResults->fetch_assoc()) {
    // var_dump($row);
    if (!isset($groups[$row["id"]])) {
      $groups[$row["id"]] = [
        'id' => $row["id"],
        'title' => $row["title"],
        'description' => $row["description"],
        'image' => $row["image"],
        'sort_index' => $row["sort_index"],
        'visibility' => $row["visibility"],
        'date' => $row["date"],
        'products' => []
      ];
    }

    if (isset($row["product_id"])) {
      $groups[$row["id"]]["products"][$row["type"]][] = [
        'id' => $row["product_id"],
        'type' => $row["type"],
        'price' => $row["price"],
        'date' => $row["date"],
      ];
    }
  }

  echo json_encode($groups);
} catch (Throwable $e) {
  echo json_encode(["error" => $errorMessage]);
  $errorMessage = $e->getFile() . " | " . $e->getLine() . " | " . $e->getMessage();
  file_put_contents($errorLogsFilePath, $errorMessage, FILE_APPEND);
  exit;
}
