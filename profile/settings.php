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

$title = "Crypto Cards - Account Settings";
$breadcrumbs = [
  ["name" => "Home", "url" => "$baseURL/profile/"],
  ["name" => "Account Settings"]
];
require_once "../include/profile/header.php";
?>

<section>
  <h1 class="mb-5 h1 fw-bold text-white">Account Settings</h1>
  <form action="<?php echo $baseURL ?>/backend/update_user_profile.php" method="POST">

    <div class="d-flex flex-column g-2 align-items-start justify-content start w-100">
      <label class="text-sm" for="profile_picture">Profile Picture</label>
      <div class="drop-zone ratio-1x1" style="width: 160px; height: 160px;">
        <div class="drop-zone__prompt d-flex flex-column g-2 align-items-center justify-content-center w-100 h-100">
          <i class="bi bi-upload" style="width: 48px; height: 48px;"></i>
        </div>
        <input type="file" id="profile_picture" accept="image/jpeg, image/jpg, image/png, image/webp" required name="profile_picture" class="drop-zone__input">
      </div>
    </div>

    <button type="submit" class="btn btn-lg btn-warning mx-auto">Update</button>
  </form>
</section>

<?php
require_once "../include/profile/footer.php";
?>