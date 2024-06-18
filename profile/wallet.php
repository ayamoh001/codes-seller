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
$getUserStmt = $connection->prepare("SELECT * FROM `users` WHERE id = ? AND `status` != 'BLOCKED' LIMIT 1");
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
$getWalletStmt = $connection->prepare("SELECT * FROM `wallets` WHERE user_id = ? AND `status` != 'BLOCKED' LIMIT 1");
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
if (!$wallet) {
  $_SESSION['flash_message'] = "No wallet found for this user!";
  $_SESSION['flash_type'] = "danger";
  header("location: $baseURL/profile/");
  exit;
}

// get charges
$getChargesStmt = $connection->prepare("SELECT * FROM `charges` WHERE wallet_id = ? AND `status` != 'BLOCKED' LIMIT 1");
$getChargesStmt->bind_param("i", $wallet["id"]);
$getChargesStmt->execute();
if ($getChargesStmt->errno) {
  $_SESSION['flash_message'] = "Error in the charges retriving process! please try again.";
  $_SESSION['flash_message'] = $getChargesStmt->error;
  $_SESSION['flash_type'] = "danger";
  header("location: $baseURL/profile/");
  exit;
}
$chargesResult = $getChargesStmt->get_result();
$getChargesStmt->close();

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
          while ($charge = $chargesResult->fetch_assoc()) :
          ?>
            <tr>
              <th scope="row"><?php echo $charge["id"]; ?></th>
              <td><?php echo $charge["prepay_id"]; ?></td>
              <td><?php echo $charge["amount"]; ?></td>
              <td><?php echo $charge["status"]; ?></td>
              <td><?php echo $charge["date"]; ?></td>
            </tr>
          <?php
          endwhile;
          ?>
        </tbody>
      </table>
    </div>

    <form class="col-12" action="<?php echo $baseURL; ?>/backend/wallet_charge.php" method="POST">
      <div class="mb-3">
        <label for="new_password_confirm" class="form-label">Amount to charge your wallet</label>
        <input type="number" min="1" class="form-control" name="amount" placeholder="amount in USD" required>
      </div>
      <button type="submit" class="btn btn-lg btn-primary mx-auto w-100 fw-bold">Charge Now With Binance (USD)</button>
    </form>
  </div>
</section>

<?php
require_once "../include/profile/footer.php";
?>