<?php
require_once "./include/config.php";

$user_id = "";
$user = null;
if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
  // get the user
  $user_id = (int) $_SESSION["user_id"];
  $getUserStmt = $connection->prepare("SELECT * FROM `users` WHERE id = ? AND status != 'BLOCKED' LIMIT 1");
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
  $getWalletStmt = $connection->prepare("SELECT * FROM `wallets` WHERE user_id = ? AND status != 'BLOCKED' LIMIT 1");
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
}

$canonicalPath = "/policies.php";
$title = "Crypto Cards - Policies";
require_once "./include/header.php";
?>


<main class="bg-dark text-white py-5 min-vh-100">
  <div class="container py-5 my-auto text-center">
    <p class="fs-4 fw-bold">Policies content (soon...)</p>
  </div>
</main>

<?php
require_once "./include/footer.php";
?>