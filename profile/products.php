<?php
require_once "../include/config.php";
require_once "../include/functions.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
  showSessionAlert("You are not logged in!", "danger", true, "login.php");
  exit;
}

$returnPath = "profile/products.php";

$user_id = (int) $_SESSION["user_id"];

$user = getUser($user_id, $returnPath);
$wallet = getUserWallet($user_id, $returnPath);
$products = getUserProducts($user_id, $returnPath);

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
      foreach ($products as $product) :
      ?>
        <tr>
          <th scope="row"><?php echo $product["id"]; ?></th>
          <td><?php echo $product["code_value"]; ?></td>
          <td><?php echo $product["type"]; ?></td>
          <td><?php echo $product["price"]; ?></td>
        </tr>
      <?php
      endforeach;
      ?>
    </tbody>
  </table>
</section>

<?php
require_once "../include/profile/footer.php";
?>