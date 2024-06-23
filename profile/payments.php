<?php
require_once "../include/config.php";
require_once "../include/functions.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
  showSessionAlert("You are not logged in!", "danger", true, "login.php");
  exit;
}

$returnPath = "profile/payments.php";

$user_id = (int) $_SESSION["user_id"];

$user = getUser($user_id, $returnPath);
$wallet = getUserWallet($user_id, $returnPath);
$payments = getUserPayments($user_id, $returnPath);

$title = "Crypto Cards - Payments";
$breadcrumbs = [
  ["name" => "Home", "url" => "$baseURL/profile/"],
  ["name" => "Payments"]
];
require_once "../include/profile/header.php";
?>

<section>
  <h1 class="mb-5 h1 fw-bold text-white text-capitalize">All of your payments</h1>
  <table class="table table-dark">
    <thead>
      <tr>
        <th scope="col">Payment ID</th>
        <th scope="col">Prepay ID</th>
        <th scope="col">Quantity</th>
        <th scope="col">Amount Price</th>
        <th scope="col">Date</th>
      </tr>
    </thead>
    <tbody>
      <?php
      foreach ($payments as $payment) :
      ?>
        <tr>
          <th scope="row"><?php echo $payment["id"]; ?></th>
          <td><?php echo $payment["prepay_id"]; ?></td>
          <td><?php echo count($payment["products"]); ?></td>
          <td><?php echo $payment["price"]; ?></td>
          <td><?php echo $payment["status"]; ?></td>
          <td><?php echo $payment["date"]; ?></td>
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