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

$returnPath = "admin/requests.php";
$getRequestsStmt = $connection->prepare("SELECT * FROM `requests`");
$getRequestsStmt->execute();
if ($getRequestsStmt->errno) {
  showSessionAlert($getRequestsStmt->error, "danger", true, $returnPath);
  exit;
}
$requestsResult = $getRequestsStmt->get_result();
$getRequestsStmt->close();

$title = "Admin Dashboard - Requests";

require_once "../include/admin/header.php";
?>

<main class="py-5 bg-dark" style="min-height: 80vh;">
  <div class="container py-5">
    <?php
    printFlashMessages();
    ?>

    <section>
      <h1 class="mb-5 pb-5 h1 fw-bold text-white">All Manual Requests</h1>

      <table class="table table-dark table-striped align-middle">
        <thead>
          <tr>
            <th scope="col">ID</th>
            <th scope="col">User Status</th>
            <th scope="col">User ID</th>
            <th scope="col">Transaction ID</th>
            <th scope="col">Group ID</th>
            <th scope="col">Type</th>
            <th scope="col">Amount</th>
            <th scope="col">Status</th>
            <th scope="col">Date</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          while ($request = $requestsResult->fetch_assoc()) :
          ?>
            <tr>
              <th scope="row">#<?php echo $request["id"]; ?></th>
              <td><?php echo str_contains($guestIdPrefix, $request["user_id"]) ? "Guest" : "User"; ?></td>
              <td><?php echo $request["user_id"]; ?></td>
              <td><?php echo $request["transaction_id"]; ?></td>
              <td><?php echo $request["group_id"]; ?></td>
              <td><?php echo $request["type"]; ?></td>
              <td><?php echo $request["amount"]; ?></td>
              <td><?php echo $request["date"]; ?></td>
              <td>
                <?php if ($request["status"] != "CONFIRMED") : ?>
                  <form action="<?php echo $baseURL . "/backend/confirm_manual_request.php"; ?>" method="POST">
                    <input type="hidden" name="request_id" value="<?php echo $request["id"]; ?>">
                    <button class="btn btn-sm btn-success">Confirm</button>
                  </form>
                <?php else : ?>
                  <button class="btn btn-sm btn-outline-secondary" disabled>Confirmed</button>
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