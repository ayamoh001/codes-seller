<?php
require_once "./include/config.php";
require_once "./include/functions.php";

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
  die("Invalid request method");
}

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
  showSessionAlert("You are not logged in!", "danger", true, "login.php");
  exit;
}

$returnPath = "";

$user_id = (int) $_SESSION["user_id"];

$user = getUser($user_id, $returnPath);
$wallet = getUserWallet($user_id, $returnPath);

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
                  <div class="col col-12 mb-md-2">
                    <form action="<?php echo $baseURL; ?>/backend/create_payment.php" method="POST" class="m-0">
                      <!-- <input type="hidden" name="useWallet" value="FALSE"> -->
                      <input type="hidden" name="groupId" value="<?php echo $groupId; ?>">
                      <input type="hidden" name="typeId" value="<?php echo $typeId; ?>">
                      <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                      <button type="submit" class="w-100 btn btn-warning btn-lg">Binance Pay</button>
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
                        <button type="submit" class="w-100 btn btn-primary btn-lg">Wallet Balance</button>
                      </form>
                    <?php
                    else :
                    ?>
                      <a href="<?php echo $baseURL; ?>/login.php" class="d-flex h-100 w-100 btn btn-primary btn-sm justify-content-center align-items-center">
                        Login to pay with wallet balance
                      </a>
                    <?php
                    endif;
                    ?>
                  </div>
                  <div class="col col-12 col-md-6">
                    <button type="button" class="btn btn-lg btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#manual-payment-request">
                      Manual Payment
                    </button>
                    <!-- Manual Payment Request Modal -->
                    <div class="modal fade" id="manual-payment-request" tabindex="-1" aria-labelledby="manual-payment-request-label" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h1 class="modal-title fs-5" id="manual-payment-request-label">Send Manual Payment Request</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <p>You can make a manual transaction via Binance to our wallet with the total amount in USDT, we will confirm it ASAP to be able to get the codes. Do not forget to provide the transaction ID.</p>
                            <p>Our Binance Code:
                              <span class="text-success">801778486</span>
                              <button class="btn btn-sm btn-outline-primary" onclick="window.navigator.clipboard.writeText('801778486')">
                                <i class="bi bi-clipboard"></i>
                              </button>
                            </p>
                            <p>Our BEP-20 Wallet Address:
                              <span class="text-success">0x99cfa4a8359ef21417992b2938fd82780fff3bd1</span>
                              <button class="btn btn-sm btn-outline-primary" onclick="window.navigator.clipboard.writeText('0x99cfa4a8359ef21417992b2938fd82780fff3bd1')">
                                <i class="bi bi-clipboard"></i>
                              </button>
                            </p>
                            <p>Our TRC-20 Wallet Address:
                              <span class="text-success">TYSEcW7fqBCb1mo3rvLZug6FQ5F7AExWv6</span>
                              <button class="btn btn-sm btn-outline-primary" onclick="window.navigator.clipboard.writeText('TYSEcW7fqBCb1mo3rvLZug6FQ5F7AExWv6')">
                                <i class="bi bi-clipboard"></i>
                              </button>
                            </p>
                            <p>Amount to transfare in USD:
                              <span class="text-success"><?php echo $totalPrice; ?> USD</span>
                              <button class="btn btn-sm btn-outline-primary" onclick="window.navigator.clipboard.writeText('<?php echo $totalPrice; ?>')">
                                <i class="bi bi-clipboard"></i>
                              </button>
                            </p>

                            <form action="<?php echo $baseURL; ?>/backend/create_payment.php" method="POST" class="m-0">
                              <input type="hidden" name="groupId" value="<?php echo $groupId; ?>">
                              <input type="hidden" name="typeId" value="<?php echo $typeId; ?>">
                              <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                              <input type="hidden" name="is_manual" value="TRUE">
                              <input type="text" name="transaction_id" value="" class="form-control" placeholder="Transaction ID..." required>
                              <div class="modal-footer p-0 pt-2">
                                <button type="submit" class="w-100 btn btn-primary">Send Request</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
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