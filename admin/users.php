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
  logErrors($getUsersStmt->error, "string");
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
                <div class="d-flex justify-content-start align-content-center gap-2">
                  <div id="change-password-<?php echo $user["id"]; ?>">
                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#change-password-modal-<?php echo $user["id"]; ?>">
                      Change Password
                    </button>
                    <!-- Change Modal -->
                    <div class="modal fade text-dark" id="change-password-modal-<?php echo $user["id"]; ?>" tabindex="-1" aria-labelledby="change-password-modal-label-<?php echo $user["id"]; ?>" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h1 class="modal-title fs-5" id="change-password-modal-label-<?php echo $user["id"]; ?>">Modal title</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <form action="<?php echo $baseURL . "/backend/change_user_password.php"; ?>" method="POST">
                              <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">
                              <input type="text" name="newPassword" value="" class="form-control" placeholder="New Password...">
                              <div class="modal-footer p-0 pt-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-success">Save</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div id="user-status-<?php echo $user["id"]; ?>">
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
                  </div>
                </div>
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