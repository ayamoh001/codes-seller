<?php
require_once "./include/config.php";
require_once "./include/functions.php";

$canonicalPath = "/signup.php";
$title = "Crypto Cards - Signup";
require_once "./include/header.php";
?>

<!-- Sign Up form  -->
<main class="py-5 bg-dark" style="min-height: 80vh;">
  <div class="container py-5">
    <?php
    printFlashMessages();
    ?>
    <section class="row justify-content-center py-5">
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
    </section>
  </div>
</main>

<?php
require_once "./include/footer.php";
?>