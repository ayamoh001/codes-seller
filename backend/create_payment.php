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
  $groupResult = $getGroupStmt->get_result();
  $group = $groupResult->fetch_assoc();
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

  // TODO: update the price calculation process
  // $totalPrice = $group["price"] * $quantity;

  // TODO: start the Payment API here


  $status = "PENDING";
  $metadata1 = "";
  $metadata2 = "";

  $connection->begin_transaction();
  $createPaymentStmt = $connection->prepare("INSERT INTO payments(group_id, product_id, status, price, metadata1, metdata2) VALUES (?,?,?,?,?,?)");
  $createPaymentStmt->bind_param("iisdss", $groupId, $productId, $status, $totalPrice, $metadata2, $metadata1);
  if ($createPaymentStmt->errno) {
    echo json_encode(["error" => "Error in the Server! please contact the support."]);
    echo json_encode(["error" => $createPaymentStmt->error]);
    $connection->rollback();
    exit;
  }
  $createPaymentStmt->close();

  $connection->commit();
  echo json_encode(["message" => "Success"]);
} catch (Throwable $e) {
  echo json_encode(["error" => "Error in the server!"]);
  echo json_encode(["error" => $e->getMessage()]);
  exit;
}
