<?php
require_once "./include/config.php";
require_once "./include/functions.php";

$user_id = "";
$user = null;
$returnPath = "policies.php";
if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
  try {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $wallet = getUserWallet($user_id, $returnPath);
  } catch (Throwable $e) {
    showSessionAlert("Error in the server!", "danger", true, $returnPath);
    logErrors($e);
    exit;
  }
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