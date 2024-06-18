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
$getUserStmt = $connection->prepare("SELECT * FROM `users` WHERE id = ? AND status != 'BLOCKED' LIMIT 1");
$getUserStmt->bind_param("i", $user_id);
$getUserStmt->execute();
if ($getUserStmt->errno) {
  $_SESSION['flash_message'] = "Error in user retrivin proccess! please try again.";
  $_SESSION['flash_message'] = $getUserStmt->error;
  $_SESSION['flash_type'] = "danger";
  header("location: $baseURL/login.php");
  exit;
}
$userResult = $getUserStmt->get_result();
$user = $userResult->fetch_assoc();
if (!$user) {
  $_SESSION['flash_message'] = "No user found with this ID!";
  $_SESSION['flash_type'] = "danger";
  header("location: $baseURL/login.php");
  exit;
}
$getUserStmt->close();

// get the wallet
$getWalletStmt = $connection->prepare("SELECT * FROM `wallets` WHERE user_id = ? AND status != 'BLOCKED' LIMIT 1");
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

$title = "Crypto Cards - Account Settings";
$breadcrumbs = [
  ["name" => "Home", "url" => "$baseURL/profile/"],
  ["name" => "Account Settings"]
];
require_once "../include/profile/header.php";
?>

<section>
  <h1 class="mb-5 h1 fw-bold text-white">Account Settings</h1>
  <form action="<?php echo $baseURL ?>/backend/update_user_profile.php" method="POST" class="mx-4 mx-md-5 px-md-5 " enctype="multipart/form-data">
    <div class="mb-3 d-flex flex-column align-items-start justify-content start w-100">
      <label class="text-sm mb-2" for="profile_picture">Profile Picture</label>
      <div class="drop-zone ratio-1x1" style="width: 160px; height: 160px;">
        <div class="drop-zone__prompt d-flex flex-column g-2 align-items-center justify-content-center w-100 h-100">
          <i class="bi bi-upload" style="width: 48px; height: 48px;"></i>
        </div>
        <input type="file" id="profile_picture" accept="image/jpeg, image/jpg, image/png, image/webp" name="profile_picture" class="drop-zone__input">
      </div>
    </div>
    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" class="form-control" id="username" name="username" placeholder="username...">
    </div>
    <div class="mb-3">
      <label for="new_password" class="form-label">New Password</label>
      <input type="password" class="form-control" id="new_password" name="new_password" placeholder="new password...">
    </div>
    <div class="mb-3">
      <label for="new_password_confirm" class="form-label">New Password confirmation</label>
      <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" placeholder="new password confirmation...">
    </div>
    <button type="submit" class="btn btn-lg btn-warning mx-auto w-100">Update</button>
  </form>
</section>

<?php
require_once "../include/profile/footer.php";
?>