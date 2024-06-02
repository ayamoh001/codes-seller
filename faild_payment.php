<?php
require_once "./include/config.php";

$title = "Crypto Cards - Payment Faild";
require_once "./include/header.php";
?>

<main class="bg-dark text-white py-5 min-vh-100">
  <div class="container py-5 my-auto text-center">
    <div class="d-flex flex-column align-items-center justify-content-center">
      <i class="bi bi-x-circle text-danger display-1"></i>
      <h1 class="mt-3">Payment Failed</h1>
      <p class="mt-3 w-50">Unfortunately, we were unable to process your payment. Please try again or contact support if the issue persists.</p>
      <button onclick="history.back()" class="btn btn-primary btn-lg fw-bold mt-3">
        <i class="bi bi-arrow-left"></i>
        <span>Go Back</span>
      </button>
    </div>
  </div>
</main>

<?php
require_once "./include/footer.php";
?>