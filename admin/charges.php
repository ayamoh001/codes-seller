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

$returnPath = "admin/charges.php";

$getChargesStmt = $connection->prepare("SELECT * FROM `charges` WHERE `status` = 'PAID'");
$getChargesStmt->execute();
if ($getChargesStmt->errno) {
  logErrors($getChargesStmt->error, "string");
  showSessionAlert($getChargesStmt->error, "danger", true, $returnPath);
  exit;
}
$chargesResult = $getChargesStmt->get_result();
$getChargesStmt->close();
$charges = [];

// echo "<pre>";
while ($row = $chargesResult->fetch_assoc()) {
  // var_dump($row);
  $charges[] = $row;
}
// echo "</pre>";

$title = "Admin Dashboard - Charges";

require_once "../include/admin/header.php";
?>


<main class="py-5 bg-dark" style="min-height: 80vh;">
  <div class="container py-5">
    <?php
    printFlashMessages();
    ?>
    <section>
      <h1 class="mb-5 pb-5 h1 fw-bold text-white">All Platform Wallet Charges</h1>

      <table class="table table-striped table-dark">
        <thead>
          <tr>
            <th scope="col">Payment ID</th>
            <th scope="col">User ID</th>
            <th scope="col">Prepay ID</th>
            <th scope="col">Merchant Trade No</th>
            <th scope="col">Amount Price</th>
            <th scope="col">Status</th>
            <th scope="col">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($charges as $charge) :
          ?>
            <tr>
              <th scope="row">#<?php echo $charge["id"]; ?></th>
              <td>#<?php echo $charge["user_id"]; ?></td>
              <td><?php echo $charge["prepay_id"]; ?></td>
              <td><?php echo $charge["merchantTradeNo"]; ?></td>
              <td><?php echo $charge["amount"]; ?>$</td>
              <td><?php echo $charge["status"]; ?></td>
              <td><?php echo $charge["date"]; ?></td>
            </tr>
          <?php
          endforeach;
          ?>
        </tbody>
      </table>
    </section>

</main>

<?php
require_once "../include/admin/footer.php";
?>