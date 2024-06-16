<?php
require_once "../include/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
  $_SESSION['flash_message'] = "You are not logged in!";
  $_SESSION['flash_type'] = "danger";
  header("location: $baseURL/login.php");
  exit;
}

$user_id = "";
$user = null;

// get the user
$user_id = (int) $_SESSION["user_id"];
$getUserStmt = $connection->prepare("SELECT * FROM `users` WHERE id = ? AND status != 'BLOCKED' LIMIT 1");
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
if (!$user) {
  $_SESSION['flash_message'] = "No user found with this ID!";
  $_SESSION['flash_type'] = "danger";
  header("location: $baseURL/login.php");
  exit;
}

// get the wallet
$getWalletStmt = $connection->prepare("SELECT * FROM `wallets` WHERE user_id = ? AND status != 'BLOCKED' LIMIT 1");
$getWalletStmt->bind_param("i", $user_id);
$getWalletStmt->execute();
if ($getWalletStmt->errno) {
  $_SESSION['flash_message'] = "Error in the wallet retriving process! please try again.";
  $_SESSION['flash_message'] = $getWalletStmt->error;
  $_SESSION['flash_type'] = "danger";
  exit;
}
$walletResult = $getWalletStmt->get_result();
$wallet = $walletResult->fetch_assoc();
$getWalletStmt->close();

$getProductsStmt = $connection->prepare("SELECT pr.* FROM `products` as pr
                                          INNER JOIN `payments` AS py
                                          WHERE py.user_id = ? AND payment_id IS NOT NULL");

$getProductsStmt->bind_param("i", $user_id);
$getProductsStmt->execute();
if ($getProductsStmt->errno) {
  $_SESSION['flash_message'] = $getProductsStmt->error;
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/profile/products.php");
  exit;
}
$productsResult = $getProductsStmt->get_result();
$getProductsStmt->close();

$title = "Crypto Cards - Products";

$breadcrumbs = [
  ["name" => "Home", "url" => "$baseURL/profile/"],
  ["name" => "Products"]
];
require_once "../include/profile/header.php";
?>

<section>
  <h1 class="mb-5 h1 fw-bold text-white text-capitalize">All of your products</h1>
  <table class="table table-dark">
    <thead>
      <tr>
        <th scope="col">Product ID</th>
        <th scope="col">Card Code</th>
        <th scope="col">Type</th>
        <th scope="col">Price</th>
      </tr>
    </thead>
    <tbody>
      <?php
      while ($product = $productsResult->fetch_assoc()) :
      ?>
        <tr>
          <th scope="row"><?php echo $product["id"]; ?></th>
          <td><?php echo $product["code_value"]; ?></td>
          <td><?php echo $product["type"]; ?></td>
          <td><?php echo $product["price"]; ?></td>
        </tr>
      <?php
      endwhile;
      ?>
    </tbody>
  </table>
</section>

<?php
require_once "../include/profile/footer.php";
?>