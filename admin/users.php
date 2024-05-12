<?php
include "../include/config.php";

if (
  !isset($_SERVER['PHP_AUTH_USER']) ||
  !isset($_SERVER['PHP_AUTH_PW']) ||
  $_SERVER['PHP_AUTH_USER'] !== $adminUsername ||
  $_SERVER['PHP_AUTH_PW'] !== $adminPassword
) {
  header('WWW-Authenticate: Basic realm="Restricted Area"');
  header('HTTP/1.0 401 Unauthorized');
  echo 'Authentication required.';
  exit;
}

$getUsersStmt = $connection->prepare("SELECT * FROM users");
$getUsersStmt->execute();
if ($getUsersStmt->errno) {
  $_SESSION['flash_message'] = $getUsersStmt->error;
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/admin/users.php");
  exit;
}
$groupResult = $getUsersStmt->get_result();
$group = $groupResult->fetch_assoc();
$getUsersStmt->close();

$title = "Admin Dashboard - Users";

include "../include/admin/header.php";
?>

<section class="py-5 bg-dark">
  <div class="container py-5">
    <?php
    if (isset($_SESSION['flash_message'])) {
      echo '<div class="alert alert-' . $_SESSION['flash_type'] . '">' . $_SESSION['flash_message'] . '</div>';

      unset($_SESSION['flash_message']);
      unset($_SESSION['flash_type']);
    }
    ?>
    <main>
      admin users
    </main>

  </div>
</section>

<?php
include "../include/admin/footer.php";
?>