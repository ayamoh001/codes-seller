<?php
require_once "./include/config.php";

$title = "Crypto Cards - Signup";
require_once "./include/header.php";
?>

<!-- Sign Up form  -->
<section class="py-5 bg-dark">
  <div class="container py-5">
    <?php
    if (isset($_SESSION['flash_message'])) {
      echo '<div class="alert alert-' . $_SESSION['flash_type'] . '">' . $_SESSION['flash_message'] . '</div>';

      unset($_SESSION['flash_message']);
      unset($_SESSION['flash_type']);
    }
    ?>
    <main class="row justify-content-center py-5">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <h2 class="mb-4 text-center">Sign Up Form</h2>
            <form action="<?php echo $baseURL; ?>/backend/auth.php" method="POST">
              <input type="hidden" name="do" value="signup">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" aria-describedby="usernameHelp" placeholder="Enter username">
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
              </div>
              <div class="mb-3 d-grid gap-2">
                <button type="submit" class="btn btn-primary fw-bold py-2">Sign Up</button>
              </div>
              <div>
                <span class="d-block text-muted text-center w-100 m-auto">do you have an account? <a class="link fw-bold" href="<?php echo $baseURL . "/login.php"; ?>">Login Now</a></span>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>
</section>

<?php
require_once "./include/footer.php";
?>