<?php
require_once "./include/config.php";
require_once "./include/functions.php";

$canonicalPath = "/login.php";
$title = "Crypto Cards - Login";
require_once "./include/header.php";
?>

<!-- Login form  -->
<main class="py-5 bg-dark">
  <div class="container my-5">
    <?php
    printFlashMessages();
    ?>
    <section class="row justify-content-center py-5">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h2 class="mb-4 text-center">Login Form</h2>
            <form action="<?php echo $baseURL; ?>/backend/auth.php" method="POST">
              <input type="hidden" name="do" value="login">
              <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
              </div>
              <div class="mb-3 d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-bold py-2">Login</button>
              </div>
              <div>
                <span class="d-block text-muted text-center w-100 m-auto">You don't have an account? <a class="link fw-bold" href="<?php echo $baseURL . "/signup.php"; ?>">Sign Up Now</a></span>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
</main>

<?php
require_once "./include/footer.php";
?>