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
  <h1 class="mb-5 h1 fw-bold text-white text-capitalize">All of your codes</h1>
  <table class="table table-striped table-dark">
    <thead>
      <tr>
        <th scope="col">ID</th>
        <th scope="col">Group</th>
        <th scope="col">Card Code</th>
        <th scope="col">Type</th>
        <th scope="col">Price</th>
        <th scope="col">Date</th>
      </tr>
    </thead>
    <tbody>
      <?php
      foreach ($products as $product) :
      ?>
        <tr>
          <th scope="row">#<?php echo $product["id"]; ?></th>
          <td class=""><?php echo $product["group_title"]; ?></td>
          <td class="fw-bold text-warning"><?php echo $product["code_value"]; ?></td>
          <td class="fw-bold"><?php echo $product["type_name"]; ?></td>
          <td class="fw-bold text-info"><?php echo $product["type_price"]; ?>$</td>
          <td><?php echo $product["date"]; ?></td>
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