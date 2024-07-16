<?php
require_once "../include/config.php";
require_once "../include/functions.php";

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

$returnPath = "admin/users.php";
$getUsersStmt = $connection->prepare("SELECT * FROM `users`");
$getUsersStmt->execute();
if ($getUsersStmt->errno) {
  showSessionAlert($getUsersStmt->error, "danger", true, $returnPath);
  exit;
}
$usersResult = $getUsersStmt->get_result();
$getUsersStmt->close();

$title = "Admin Dashboard - Users";

require_once "../include/admin/header.php";
?>

<main class="py-5 bg-dark" style="min-height: 80vh;">
  <div class="container py-5">
    <?php
    printFlashMessages();
    ?>

    <section>
      <h1 class="mb-5 pb-5 h1 fw-bold text-white">All Platform Registed Users</h1>

      <table class="table table-dark table-striped align-middle">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">Picture</th>
            <th scope="col">Username</th>
            <th scope="col">Email</th>
            <th scope="col">Status</th>
            <th scope="col">Registration Date</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          while ($user = $usersResult->fetch_assoc()) :
          ?>
            <tr>
              <th scope="row">#<?php echo $user["id"]; ?></th>
              <td>
                <img class="rounded-pill overflow-hidden" src="<?php echo $baseURL . (isset($user["profile_picture"]) && $user["profile_picture"] != "" ? $user["profile_picture"] : "/assets/images/profile-picture.png") ?>" alt="profile picture" width="42">
              </td>
              <td><?php echo $user["username"]; ?></td>
              <td><?php echo $user["email"]; ?></td>
              <td><?php echo $user["status"]; ?></td>
              <td><?php echo $user["date"]; ?></td>
              <td>
                <?php if ($user["status"] != "BLOCKED") : ?>
                  <form action="<?php echo $baseURL . "/backend/update_user_status.php"; ?>" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                    <input type="hidden" name="new_status" value="BLOCKED">
                    <button class="btn btn-sm btn-danger">Block</button>
                  </form>
                <?php else : ?>
                  <form action="<?php echo $baseURL . "/backend/update_user_status.php"; ?>" method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                    <input type="hidden" name="new_status" value="ACTIVE">
                    <button class="btn btn-sm btn-success">Activate</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php
          endwhile;
          ?>
        </tbody>
      </table>
    </section>

  </div>
</main>

<?php
require_once "../include/admin/footer.php";
?>