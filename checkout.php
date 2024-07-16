<?php
require_once "./include/config.php";
require_once "./include/functions.php";

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
  die("Invalid request method");
}

$returnPath = "checkout.php";

$user_id = "";
if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
  $user_id = (int) $_SESSION["user_id"];
  $user = getUser($user_id, $returnPath);
  $wallet = getUserWallet($user_id, $returnPath);
}

$groupId = (int) $_GET['groupId'];
$typeId = (int) $_GET['typeId'];
$quantity = (int)$_GET['quantity'];

$group = getGroupWithType($groupId, $typeId, $quantity, $returnPath);

// echo "<pre>";
// var_dump($group);
// echo "</pre>";

// it is acctually the type price
$totalPrice = $group["price"] * $quantity;

$canonicalPath = "/checkout.php";
$title = "Crypto Cards - Checkout";
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
        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="card rounded-4 border-0">
              <div class="card-header bg-warning text-white rounded-top-4">
                <h2 class="mb-0">Checkout Now</h2>
              </div>
              <div class="card-body p-4">
                <div class="mb-4">
                  <!-- <h4>Product Details:</h4> -->
                  <div class="d-flex flex-column justify-content-between align-items-start">
                    <div>
                      <h5 class="mb-0 fw-bold"><?php echo $group["title"]; ?></h5>
                      <p class="text-muted fs-6"><?php echo $group["description"]; ?></p>
                    </div>
                    <!-- <div>
                      <h6 class="mb-2"><span class="fw-bold"><?php echo $group["type_name"]; ?></span> Type</h6>
                    </div> -->
                    <div>
                      <label class="text-muted">Type: </label>
                      <span><?php echo $group["type_name"]; ?></span>
                    </div>
                    <div>
                      <label class="text-muted">Quantity: </label>
                      <span><?php echo $quantity; ?></span>
                    </div>
                  </div>
                </div>

                <div class="mb-4">
                  <p class="h2 fw-bold">Total: <span class="text-success"><?php echo $totalPrice; ?> USD</span></p>
                </div>

                <div class="mb-2">
                  <h4>Pay with:</h4>
                </div>

                <div class="row gap-2 gap-md-0">
                  <div class="col col-12 col-md-6">
                    <form action="<?php echo $baseURL; ?>/backend/create_payment.php" method="POST" class="m-0">
                      <!-- <input type="hidden" name="useWallet" value="FALSE"> -->
                      <input type="hidden" name="groupId" value="<?php echo $groupId; ?>">
                      <input type="hidden" name="typeId" value="<?php echo $typeId; ?>">
                      <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                      <button type="submit" class="w-100 btn btn-secondary btn-lg">Binance Pay (soon)</button>
                    </form>
                  </div>
                  <div class="col col-12 col-md-6">
                    <?php
                    if (isset($user) && $user["id"] != "") :
                    ?>
                      <form action="<?php echo $baseURL; ?>/backend/create_payment.php" method="POST" class="m-0">
                        <input type="hidden" name="useWallet" value="TRUE">
                        <input type="hidden" name="groupId" value="<?php echo $groupId; ?>">
                        <input type="hidden" name="typeId" value="<?php echo $typeId; ?>">
                        <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                        <button type="submit" class="w-100 btn btn-outline-primary btn-lg">Wallet Balance</button>
                      </form>
                    <?php
                    else :
                    ?>
                      <a href="<?php echo $baseURL; ?>/login.php" class="d-flex h-100 w-100 btn btn-outline-primary btn-sm justify-content-center align-items-center">
                        Login to pay with wallet balance
                      </a>
                    <?php
                    endif;
                    ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>
  </div>
</main>


<?php
require_once "./include/footer.php";
?>