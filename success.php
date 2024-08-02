<?php
require_once "./include/config.php";
require_once "./include/functions.php";

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
  die("Invalid request method");
}

$returnPath = "success.php";

$user_id = "";
if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
  $user_id = (int) $_SESSION["user_id"];
  $user = getUser($user_id, $returnPath);
  $wallet = getUserWallet($user_id, $returnPath);
}

$errors = (int) $_GET['errors'];
logErrors($errors, "string");
$paymentId = (int) $_GET['paymentId'];
$payment = getPaymentWithProducts($paymentId, $returnPath);

echo "<pre>";
var_dump($paymentgroup);
echo "</pre>";

$canonicalPath = "/success.php";
$title = "Crypto Cards - Payment Success";
require_once "./include/header.php";
?>

<!-- Login form  -->
<main class="py-5 bg-dark" style="min-height: 80vh;">
  <div class="container my-5">
    <?php
    printFlashMessages();
    ?>
    <section>
      <div class="container">
        <div class="card" style="width: 18rem;">
          <div class="card-header">
            Codes
          </div>
          <ul class="list-group list-group-flush">
            <?php
            foreach ($payment["products"] as $product) {
            ?>
              <li class="list-group-item"><?php echo $product["code"]; ?></li>
            <?php
            }
            ?>
          </ul>
        </div>
    </section>
  </div>
</main>


<?php
require_once "./include/footer.php";
?>