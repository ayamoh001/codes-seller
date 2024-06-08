<?php

require_once "../include/config.php";

try {
  $getGroupsWithProuductsStmt = $connection->prepare("SELECT g.*, p.id AS product_id, p.type, p.price
                                                    FROM `groups` g
                                                    LEFT JOIN products p ON g.id = p.group_id
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

  echo json_encode($groups);
} catch (Throwable $e) {
  echo json_encode(["error" => $errorMessage]);
  $errorMessage = $e->getFile() . " | " . $e->getLine() . " | " . $e->getMessage();
  file_put_contents($errorLogsFilePath, $errorMessage, FILE_APPEND);
  exit;
}