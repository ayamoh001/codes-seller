<?php
require_once "../include/config.php";
require_once "../include/functions.php";

$returnPath = "profile/wallet.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
  showSessionAlert("You are not logged in!", "danger", true, "login.php");
  exit;
}

$user_id = (int) $_SESSION["user_id"];
$user = getUser($user_id, $returnPath);
$wallet = getUserWallet($user_id, $returnPath);

// get charges
$charges = getWalletCharges($wallet["id"], $returnPath);

$title = "Crypto Cards - Wallet";
$breadcrumbs = [
  ["name" => "Home", "url" => "$baseURL/profile/"],
  ["name" => "Wallet"]
];
require_once "../include/profile/header.php";
?>

<section>
  <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-5">
    <h1 class="h2 fw-bold text-white">Your Wallet & Previous Charges</h1>
    <h2 class="h1 fw-bold text-info"><?php echo number_format($wallet["balance"], 2); ?> USD</h2>
  </div>

  <div class="row flex-column align-items-start justify-content-between g-4">
    <form class="col-12" action="<?php echo $baseURL; ?>/backend/create_charge.php" method="POST">
      <div class="mb-3">
        <label for="new_password_confirm" class="form-label">Amount to charge your wallet</label>
        <input type="number" min="1" step="0.01" class="form-control" name="amount" placeholder="amount in USD" required>
      </div>
      <button type="submit" class="btn btn-lg btn-primary mx-auto w-100 fw-bold">Charge Now With Binance (USD)</button>
    </form>

    <div class="col-12">
      <table class="table table-dark" style="max-height: 70vh; overflow-y: auto;">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Prepay ID</th>
            <th scope="col">Amount</th>
            <th scope="col">Status</th>
            <th scope="col">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($charges as $charge) :
          ?>
            <tr>
              <th scope="row"><?php echo $charge["id"]; ?></th>
              <td><?php echo $charge["prepay_id"]; ?></td>
              <td><?php echo $charge["amount"]; ?></td>
              <td><?php echo $charge["status"]; ?></td>
              <td><?php echo $charge["date"]; ?></td>
            </tr>
          <?php
          endforeach;
          ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php
require_once "../include/profile/footer.php";
?>