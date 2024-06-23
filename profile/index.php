<?php
require_once "../include/config.php";
require_once "../include/functions.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
  showSessionAlert("You are not logged in!", "danger", true, "login.php");
  exit;
}

$returnPath = "profile/index.php";

$user_id = (int) $_SESSION["user_id"];

$user = getUser($user_id, $returnPath);
$wallet = getUserWallet($user_id, $returnPath);

$title = "Crypto Cards - Profile Page";
$breadcrumbs = [
  ["name" => "Home", "url" => "$baseURL/profile/"],
  ["name" => "Profile Page"]
];
require_once "../include/profile/header.php";
?>

<section>
  <h1 class="mb-5 h1 fw-bold text-white">Profile Page</h1>

  <div class="row g-4">
    <div class="col-md-3">
      <div class="card rounded-4 h-100">
        <a href="<?php echo $baseURL; ?>/profile/settings.php" class="card-body h-100 text-decoration-none">
          <h5 class="card-title">Account Settings</h5>
          <p class="card-text text-muted">Edit your account password, username, and picture.</p>
        </a>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card rounded-4 h-100">
        <a href="<?php echo $baseURL; ?>/profile/products.php" class="card-body h-100 text-decoration-none">
          <h5 class="card-title">Purchased Products</h5>
          <p class="card-text text-muted">View your purchased products (Codes) lists.</p>
        </a>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card rounded-4 h-100">
        <a href="<?php echo $baseURL; ?>/profile/wallet.php" class="card-body h-100 text-decoration-none">
          <h5 class="card-title">Wallet (Balance)</h5>
          <p class="card-text text-muted">Go to your wallet to see and charge your balance.</p>
        </a>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card rounded-4 h-100">
        <a href="<?php echo $baseURL; ?>/profile/payments.php" class="card-body h-100 text-decoration-none">
          <h5 class="card-title">Payments History</h5>
          <p class="card-text text-muted">Display your payments history and information.</p>
        </a>
      </div>
    </div>

  </div>
</section>

<?php
require_once "../include/profile/footer.php";
?>