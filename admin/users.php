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
$usersResult = $getUsersStmt->get_result();
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
      <table class="table">
        <thead>
          <tr>
            <th scope="col">Id</th>
            <th scope="col">Picture</th>
            <th scope="col">Username</th>
            <th scope="col">Email</th>
            <th scope="col">Status</th>
            <th scope="col">Registration Data</th>
          </tr>
        </thead>
        <tbody>
          <?php
          while ($user = $usersResult->fetch_assoc()) :
          ?>
            <tr>
              <th scope="row"><?php echo $user["id"]; ?></th>
              <td>
                <img src="<?php echo $baseURL . (isset($user["profile_picture"]) && $user["profile_picture"] != "" ? $user["profile_picture"] : "/assets/images/profile-picture.png") ?>" alt="profile picture" width="42">
              </td>
              <td><?php echo $user["username"]; ?></td>
              <td><?php echo $user["email"]; ?></td>
              <td><?php echo $user["status"]; ?></td>
              <td><?php echo $user["date"]; ?></td>
            </tr>
          <?php
          endwhile;
          ?>
        </tbody>
      </table>
    </main>

  </div>
</section>

<?php
include "../include/admin/footer.php";
?>