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
$getUserStmt = $connection->prepare("SELECT * FROM users WHERE id = ? AND status != 'BLOCKED' LIMIT 1");
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

// get the wallet
$getWalletStmt = $connection->prepare("SELECT * FROM wallet WHERE user_id = ? AND status != 'BLOCKED' LIMIT 1");
$getWalletStmt->bind_param("i", $user_id);
$getWalletStmt->execute();
if ($getWalletStmt->errno) {
  echo json_encode(["error" => "Error in the wallet retriving process! please try again."]);
  echo json_encode(["error" => $getWalletStmt->error]);
  exit;
}
$walletResult = $getWalletStmt->get_result();
$wallet = $walletResult->fetch_assoc();
$getWalletStmt->close();

$title = "Crypto Cards - Profile";
$breadcrumbs = [
  ["name" => "Profile Home Page", "url" => "$baseURL/profile/"],
];

require_once "../include/profile/header.php";
?>


<div class="row g-3">
  <div class="col-4">
    <div class="card bg-dark text-light bg-gradient border-light-subtle w-full">
      <div class="card-body">
        <h5 class="card-title">000</h5>
        <p class="card-text">of cards has been bought</p>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card bg-dark text-light bg-gradient border-light-subtle w-full">
      <div class="card-body">
        <h5 class="card-title">000</h5>
        <p class="card-text">of cards has been bought</p>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card bg-dark text-light bg-gradient border-light-subtle w-full">
      <div class="card-body">
        <h5 class="card-title">000</h5>
        <p class="card-text">of cards has been bought</p>
      </div>
    </div>
  </div>
</div>


<?php
require_once "../include/profile/footer.php";
?>