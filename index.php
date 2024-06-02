<?php
require_once "./include/config.php";

$user_id = "";
$user = null;
if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
  // get the user
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

  // get the wallet
  $getWalletStmt = $connection->prepare("SELECT * FROM wallet WHERE user_id = ? AND status != 'BLOCKED' LIMIT 1");
  $getWalletStmt->bind_param("i", $user_id);
  $getWalletStmt->execute();
  if ($getWalletStmt->errno) {
    echo json_encode(["error" => "Error in the wallet retriving process! please try again."]);
    echo json_encode(["error" => $getWalletStmt->error]);
    exit;
  }
  $walletResult = $getWalletStmt->get_result();
  $wallet = $walletResult->fetch_assoc();
  $getWalletStmt->close();
}


// Get groups and classify products by its type
$getGroupsWithProuductsStmt = $connection->prepare("SELECT g.*, p.id AS product_id, p.type, p.price
                                                    FROM `groups` g
                                                    LEFT JOIN products p ON g.id = p.group_id
                                                    WHERE p.payment_id IS NULL AND g.visibility = 1
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

// $groups = [
//   "1" => [
//     "id" => "1",
//     "title" => "Card title",
//     "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
//     "image" => "/storage/groups/car-image.webp",
//     "date" => "2024-05-09 00:00:00",
//     "products" => [
//       "5$" => [
//         [
//           "id" => "1",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-11",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "15",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-12",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "125",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-13",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "25$" => [
//         [
//           "id" => "2",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-2",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "3",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-3",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "50$" => [
//         [
//           "id" => "4",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-4",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "5",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-5",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//     ],
//   ],
//   "2" => [
//     "id" => "2",
//     "title" => "Card title",
//     "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
//     "image" => "/storage/groups/car-image.webp",
//     "date" => "2024-05-09 00:00:00",
//     "products" => [
//       "5$" => [
//         [
//           "id" => "1",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-11",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "15",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-12",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "125",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-13",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "25$" => [
//         [
//           "id" => "2",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-2",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "3",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-3",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "50$" => [
//         [
//           "id" => "4",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-4",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "5",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-5",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//     ],
//   ],
//   "3" => [
//     "id" => "3",
//     "title" => "Card title",
//     "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
//     "image" => "/storage/groups/car-image.webp",
//     "date" => "2024-05-09 00:00:00",
//     "products" => [
//       "5$" => [
//         [
//           "id" => "1",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-11",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "15",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-12",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "125",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-13",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "25$" => [
//         [
//           "id" => "2",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-2",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "3",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-3",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "50$" => [
//         [
//           "id" => "4",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-4",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "5",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-5",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//     ],
//   ],
//   "4" => [
//     "id" => "4",
//     "title" => "Card title",
//     "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
//     "image" => "/storage/groups/car-image.webp",
//     "date" => "2024-05-09 00:00:00",
//     "products" => [
//       "5$" => [
//         [
//           "id" => "1",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-11",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "15",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-12",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "125",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-13",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "25$" => [
//         [
//           "id" => "2",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-2",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "3",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-3",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "50$" => [
//         [
//           "id" => "4",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-4",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "5",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-5",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//     ],
//   ],
//   "5" => [
//     "id" => "5",
//     "title" => "Card title",
//     "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
//     "image" => "/storage/groups/car-image.webp",
//     "date" => "2024-05-09 00:00:00",
//     "products" => [
//       "5$" => [
//         [
//           "id" => "1",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-11",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "15",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-12",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "125",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-13",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "25$" => [
//         [
//           "id" => "2",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-2",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "3",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-3",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "50$" => [
//         [
//           "id" => "4",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-4",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "5",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-5",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//     ],
//   ],
//   "6" => [
//     "id" => "6",
//     "title" => "Card title",
//     "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
//     "image" => "/storage/groups/car-image.webp",
//     "date" => "2024-05-09 00:00:00",
//     "products" => [
//       "5$" => [
//         [
//           "id" => "1",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-11",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "15",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-12",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "125",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-13",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "25$" => [
//         [
//           "id" => "2",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-2",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "3",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-3",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "50$" => [
//         [
//           "id" => "4",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-4",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "5",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-5",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//     ],
//   ],
//   "7" => [
//     "id" => "7",
//     "title" => "Card title",
//     "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
//     "image" => "/storage/groups/car-image.webp",
//     "date" => "2024-05-09 00:00:00",
//     "products" => [
//       "5$" => [
//         [
//           "id" => "1",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-11",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "15",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-12",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "125",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-13",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "25$" => [
//         [
//           "id" => "2",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-2",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "3",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-3",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "50$" => [
//         [
//           "id" => "4",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-4",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "5",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-5",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//     ],
//   ],
//   "8" => [
//     "id" => "8",
//     "title" => "Card title",
//     "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
//     "image" => "/storage/groups/car-image.webp",
//     "date" => "2024-05-09 00:00:00",
//     "products" => [
//       "5$" => [
//         [
//           "id" => "1",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-11",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "15",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-12",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "125",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-13",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "25$" => [
//         [
//           "id" => "2",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-2",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "3",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-3",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "50$" => [
//         [
//           "id" => "4",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-4",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "5",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-5",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//     ],
//   ],
//   "9" => [
//     "id" => "9",
//     "title" => "Card title",
//     "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
//     "image" => "/storage/groups/car-image.webp",
//     "date" => "2024-05-09 00:00:00",
//     "products" => [
//       "5$" => [
//         [
//           "id" => "1",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-11",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "15",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-12",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "125",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-13",
//           "type" => "5$",
//           "price" => 4.5,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "25$" => [
//         [
//           "id" => "2",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-2",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "3",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-3",
//           "type" => "25$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//       "50$" => [
//         [
//           "id" => "4",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-4",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//         [
//           "id" => "5",
//           "group_id" => "1",
//           "payment_id" => null,
//           "code_value" => "code-value-of-5",
//           "type" => "50$",
//           "price" => 20,
//           "date" => "2024-05-09 00:00:00",
//         ],
//       ],
//     ],
//   ],
// ];

$canonicalPath = "";
$title = "Crypto Cards - Home";
require_once "./include/header.php";
?>

<main>
  <!-- Hero Section -->
  <section class="hero-section bg-dark text-light py-5">
    <div class="container text-center">
      <h1 class="display-4 fw-bold">INSTANT DELIVERY</h1>
      <p class="lead">Choose your code type</p>
      <a href="<?php echo $baseURL . ($user_id == "" ? "/login.php" : "/profile/"); ?>" class="btn btn-primary btn-lg px-5 fw-bold mt-2"><?php echo $user_id == "" ? "LOGIN NOW" : "Your Account"; ?></a>
    </div>
  </section>

  <!-- Cards Section -->
  <section id="products-groups-list" class="cards-section bg-dark pb-5">
    <div class="container">
      <div class="row">
        <?php foreach ($groups as $group) : ?>
          <div class="col-md-4 p-3">
            <div class="card h-100">
              <div class="card-body h-100">
                <div class="d-flex gap-2 mb-3">
                  <img src="<?php echo $baseURL . $group["image"]; ?>" class="w-25 rounded" style="aspect-ratio: 16/9 !important;" alt="<?php echo $group["title"]; ?>">
                  <h5 class="card-title fs-6 w-75 my-auto line-clamp-2"><?php echo $group["title"]; ?></h5>
                </div>
                <p class="card-text line-clamp-2"><?php echo $group["description"]; ?></p>
                <ul id="types-radios-container-<?php echo $group["id"]; ?>" class="d-flex gap-2 list-unstyled">
                  <?php
                  $isFirst = true;
                  foreach ($group["products"] as $type => $products) :
                  ?>
                    <li data-type-id="radio-type-<?php echo $group["id"] . "-" . $type ?>">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="products-types-radio" id="products-types-radio-<?php echo $group["id"] . "-" . $type ?>">
                        <label class="form-check-label" for="products-types-radio-label-<?php echo $group["id"] ?>">
                          <?php echo $type ?>
                        </label>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
                <div id="quantities-container-<?php echo $group["id"]; ?>">
                  <?php
                  $isFirst = true;
                  foreach ($group["products"] as $type => $products) :
                  ?>
                    <input type="number" min="1" max="<?php echo count($products); ?>" class="form-control mb-3" id="products-of-type-quantity-<?php echo $group["id"] . "-" . $type; ?>" placeholder="1" style="display: <?php echo $isFirst ? "block" : "none";
                                                                                                                                                                                                                          $isFirst = false; ?>;">
                  <?php endforeach; ?>
                </div>
                <div class="d-flex gap-2 mt-auto">
                  <button id="button-for-group-<?php echo $group["id"]; ?>" type="button" class="btn btn-primary fw-bold w-100">
                    <span id="total-price-for-group-<?php echo $group["id"]; ?>">---</span> USD
                  </button>
                  <a type="button" class="btn btn-outline-primary fw-bold w-100" href="https://wa.me/+601167999817" target="_blank" rel="noopener noreferrer">
                    Ask
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal -->
          <!-- <div class="modal fade" id="group-modal-<?php echo $group["id"]; ?>" aria-labelledby="group-modal-label-<?php echo $group["id"]; ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="group-modal-label-<?php echo $group["id"]; ?>"><?php echo $group["title"]; ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div id="loader-for-group-modal-<?php echo $group["id"]; ?>" style="display: none;">
                    <div class='loader-container my-5'>
                      <div id='loader' class='loader'></div>
                    </div>
                  </div>
                  <div id="payment-status-for-group-modal-<?php echo $group["id"]; ?>" style="display: block;">

                  </div>
                </div>
                <div class="modal-footer">
                  <button id="submit-payment-button-<?php echo $group["id"]; ?>" type="button" class="btn btn-primary btn-lg w-100 fw-bold">Buy for <span id="total-price-for-group-modal-<?php echo $group["id"]; ?>">---</span> USD</button>
                </div>
              </div>
            </div>
          </div> -->

        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<script type="module">
  const groups = await fetch("./backend/get_groups.php").then(res => res.json())

  async function submitPayment(groupId, quantity, type, button) {
    console.log({
      groupId,
      quantity,
      type,
      button
    })
    button.disabled = true
    try {
      const createPaymentResult = await fetch("<?php echo $baseURL; ?>/backend/create_payment.php", {
        credentials: "include",
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          quantity,
          type,
          groupId,
        })
      })
      const createPaymentResultData = await createPaymentResult
        // .json()
        .text()

      console.log({
        createPaymentResultData
      })
      window.location.href = createPaymentResultData.data.checkoutUrl
    } catch (e) {
      alert("Error in the proccess! Please contact the support.")
      console.log({
        e
      })
    } finally {
      button.disabled = false
    }
  }

  console.log({
    groups
  })

  for (let i in groups) {
    let total = 0
    let selectedType = ""
    let selectedQuantity = 1
    const quantitiesContainer = document.querySelector(`#quantities-container-${i}`)
    const products = groups[i].products
    const submitButton = document.querySelector(`#button-for-group-${i}`)
    const totalPriceButton = document.querySelector(`#total-price-for-group-${i}`)

    submitButton.addEventListener("click", () => {
      submitPayment(groups[i].id, selectedQuantity, selectedType, submitButton)
    })

    function updateTotalPrice() {
      total = 0
      groups[i].products[selectedType].map((product, i) => {
        if (i < selectedQuantity) {
          total = (Number(total) + Number(product.price)).toString()
        }
      })
      console.log({
        total
      })
      totalPriceButton.innerHTML = total
    }

    for (let type in products) {
      if (selectedType == "") {
        selectedType = type
      }
      const quantitiesInputsForType = quantitiesContainer.querySelectorAll(`[id^="products-of-type-quantity-"]`)
      // console.log({
      //   quantitiesInputsForType
      // })
      const radioCheck = document.querySelector(`#products-types-radio-${i}-${CSS.escape(type)}`)

      radioCheck.addEventListener("click", () => {
        selectedType = type
        // hide siblings
        quantitiesInputsForType.forEach(quantityOfTypeInput => {
          quantityOfTypeInput.style.display = "none"
          quantityOfTypeInput.addEventListener("change", () => {
            selectedQuantity = quantityOfTypeInput.value
            console.log({
              selectedQuantity
            })
            updateTotalPrice()
          })
        })
        // show target qunatity
        const quantityOfType = document.querySelector(`#products-of-type-quantity-${i}-${CSS.escape(type)}`)
        quantityOfType.style.display = "block"

        updateTotalPrice()
      })
    }
    // const quantitiesContainer = document.querySelector(`#quantities-container-${groups[i].id}`)

    const groupModal = document.querySelector(`#group-modal-${groups[i].id}`)
  }
</script>
<?php
require_once "./include/footer.php";
?>