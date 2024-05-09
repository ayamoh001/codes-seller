<?php
include "./include/config.php";

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

$groups = [
  [
    "id" => "1",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
    "image" => "/storage/groups/car-image.webp",
    "date" => "2024-05-09 00:00:00",
    "products" => [
      [
        "id" => "1",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-1",
        "type" => "5$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "2",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-2",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "3",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-3",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "4",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-4",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "5",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-5",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
    ],
  ],
  [
    "id" => "2",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
    "image" => "/storage/groups/car-image.webp",
    "date" => "2024-05-09 00:00:00",
    "products" => [
      [
        "id" => "1",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-1",
        "type" => "5$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "2",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-2",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "3",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-3",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "4",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-4",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "5",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-5",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
    ],
  ],
  [
    "id" => "3",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
    "image" => "/storage/groups/car-image.webp",
    "date" => "2024-05-09 00:00:00",
    "products" => [
      [
        "id" => "1",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-1",
        "type" => "5$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "2",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-2",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "3",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-3",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "4",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-4",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "5",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-5",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
    ],
  ],
  [
    "id" => "4",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
    "image" => "/storage/groups/car-image.webp",
    "date" => "2024-05-09 00:00:00",
    "products" => [
      [
        "id" => "1",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-1",
        "type" => "5$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "2",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-2",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "3",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-3",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "4",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-4",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "5",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-5",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
    ],
  ],
  [
    "id" => "5",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
    "image" => "/storage/groups/car-image.webp",
    "date" => "2024-05-09 00:00:00",
    "products" => [
      [
        "id" => "1",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-1",
        "type" => "5$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "2",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-2",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "3",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-3",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "4",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-4",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "5",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-5",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
    ],
  ],
  [
    "id" => "6",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
    "image" => "/storage/groups/car-image.webp",
    "date" => "2024-05-09 00:00:00",
    "products" => [
      [
        "id" => "1",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-1",
        "type" => "5$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "2",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-2",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "3",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-3",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "4",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-4",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "5",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-5",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
    ],
  ],
  [
    "id" => "7",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
    "image" => "/storage/groups/car-image.webp",
    "date" => "2024-05-09 00:00:00",
    "products" => [
      [
        "id" => "1",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-1",
        "type" => "5$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "2",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-2",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "3",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-3",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "4",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-4",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "5",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-5",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
    ],
  ],
  [
    "id" => "8",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
    "image" => "/storage/groups/car-image.webp",
    "date" => "2024-05-09 00:00:00",
    "products" => [
      [
        "id" => "1",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-1",
        "type" => "5$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "2",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-2",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "3",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-3",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "4",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-4",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "5",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-5",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
    ],
  ],
  [
    "id" => "9",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content. the bulk of the card's content.",
    "image" => "/storage/groups/car-image.webp",
    "date" => "2024-05-09 00:00:00",
    "products" => [
      [
        "id" => "1",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-1",
        "type" => "5$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "2",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-2",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "3",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-3",
        "type" => "25$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "4",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-4",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
      [
        "id" => "5",
        "group_id" => "1",
        "payment_id" => null,
        "code_value" => "code-value-of-5",
        "type" => "50$",
        "price" => 20,
        "date" => "2024-05-09 00:00:00",
      ],
    ],
  ],
];

$title = "Crypto Cards - Home";
include "./include/header.php";
?>

<main>
  <!-- Hero Section -->
  <section class="hero-section bg-dark text-light py-5">
    <div class="container text-center">
      <h1 class="display-4 fw-bold">INSTANT DELIVERY</h1>
      <p class="lead">Choose your code type</p>
      <a href="<?php echo $baseURL . ($user_id == "" ? "/login.php" : "/profile.php"); ?>" class="btn btn-primary btn-lg px-5 fw-bold mt-2"><?php echo $user_id == "" ? "LOGIN NOW" : "Your Account"; ?></a>
    </div>
  </section>

  <!-- Cards Section -->
  <section id="products-groups-list" class="cards-section bg-dark pb-5">
    <div class="container">
      <div class="row">
        <?php for ($i = 0; $i < count($groups); $i++) : ?>
          <div class="col-md-4 p-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex gap-2 mb-3">
                  <img src="<?php echo $baseURL . $groups[$i]["image"]; ?>" class="w-25 rounded ratio-16x9" alt="<?php echo $groups[$i]["title"]; ?>">
                  <h5 class="card-title xw-75 my-auto line-clamp-1"><?php echo $groups[$i]["title"]; ?></h5>
                </div>
                <p class="card-text line-clamp-2"><?php echo $groups[$i]["description"]; ?></p>
                <input type="number" min="1" max="2" class="form-control mb-3" id="quantity-<?php echo $i; ?>" placeholder="1">
                <div class="d-flex gap-2">
                  <a class="btn btn-primary fw-bold w-100" href="https://wa.me/+601167999817" target="_blank" rel="noopener noreferrer">
                    --- USD
                  </a>
                  <button type="button" class="btn btn-outline-primary fw-bold w-100" data-bs-toggle="modal" data-bs-target="#group-modal-<?php echo $groups[$i]["id"]; ?>">
                    Ask
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal -->
          <div class="modal fade" id="group-modal-<?php echo $groups[$i]["id"]; ?>" aria-labelledby="exampleModalLabel<?php echo $groups[$i]["id"]; ?>" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel<?php echo $groups[$i]["id"]; ?>"><?php echo $groups[$i]["title"]; ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div id="loader" style="display: none;"></div>
                  <div id="payment-status" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                  <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> -->
                  <button type="button" class="btn btn-primary btn-lg w-100 fw-bold">Buy for <?php echo $groups[$i]["price"] . " USD"; ?></button>
                </div>
              </div>
            </div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>
</main>

<?php
include "./include/footer.php";
?>