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
  $quantity = (int) $_POST["quantity"] ?? 1;
  $groupId = (int) $_POST["group_id"];

  $getGroupStmt = $connection->prepare("SELECT * FROM groups WHERE id = ? LIMIT 1");
  $getGroupStmt->bind_param("i", $groupId);
  $getGroupStmt->execute();
  if ($getGroupStmt->errno) {
    echo json_encode(["error" => "Error in the Server! please contact the support."]);
    echo json_encode(["error" => $getGroupStmt->error]);
    exit;
  }
  $getGroupStmt->bind_result($group);
  $getGroupStmt->fetch();
  $getGroupStmt->close();

  if (!$group) {
    echo json_encode(["error" => "No group with this ID!"]);
    exit;
  }

  // check products count
  $getProductsStmt = $connection->prepare("SELECT count(*) FROM products WHERE group_id = ? AND status = 'AVAILABLE' LIMIT ?");
  $getProductsStmt->bind_param("ii", $groupId, $quantity);
  if ($getProductsStmt->errno) {
    echo json_encode(["error" => "Error in the Server! please contact the support."]);
    echo json_encode(["error" => $getProductsStmt->error]);
    exit;
  }
  $getProductsStmt->bind_result($productsCount);
  $getProductsStmt->fetch();
  $getProductsStmt->close();

  if ($productsCount < $quantity) {
    echo json_encode(["error" => "No enough quantity! please chose less quantity or contact us."]);
    exit;
  }

  $totalPrice = $group["price"] * $quantity;
  // TODO: start the Payment API


  $status = "PENDING";
  $metadata1 = "";
  $metadata2 = "";

  $createPaymentStmt = "INSERT INTO payments(group_id, product_id, status, price, metadata1, metdata2) VALUES (?,?,?,?,?,?)";
  $getProductsStmt->bind_param("iisdss", $groupId, $productId, $status, $totalPrice, $metadata2, $metadata1);
  if ($getProductsStmt->errno) {
    echo json_encode(["error" => "Error in the Server! please contact the support."]);
    echo json_encode(["error" => $getProductsStmt->error]);
    exit;
  }
  $getProductsStmt->bind_result($productsCount);
  $getProductsStmt->fetch();
  $getProductsStmt->close();
  echo json_encode(["message" => "Success"]);
} catch (Throwable $e) {
  echo json_encode(["error" => "Error in the server!"]);
  echo json_encode(["error" => $e->getMessage()]);
  exit;
}
