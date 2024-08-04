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

$returnPath = "admin/payments.php";

$getPaymentsStmt = $connection->prepare("SELECT py.*, gr.id AS group_id, gr.title AS group_title, ty.id AS type_id, ty.name AS type_name, ty.price AS type_price 
                                          FROM `payments` AS py
                                          LEFT JOIN `types` AS ty ON py.type_id = ty.id
                                          LEFT JOIN `groups` AS gr ON gr.id = ty.group_id
                                          WHERE py.status = 'PAID'
                                           ");
// $getPaymentsStmt = $connection->prepare("SELECT * FROM `payments` WHERE is_manual = 1");
$getPaymentsStmt->execute();
if ($getPaymentsStmt->errno) {
  logErrors($getPaymentsStmt->error, "string");
  showSessionAlert($getPaymentsStmt->error, "danger", true, $returnPath);
  exit;
}
$paymentsResult = $getPaymentsStmt->get_result();
$getPaymentsStmt->close();
$payments = [];

// echo "<pre>";
while ($row = $paymentsResult->fetch_assoc()) {
  // var_dump($row);
  $payments[] = $row;
}
// echo "</pre>";

$title = "Admin Dashboard - Payments";

require_once "../include/admin/header.php";
?>


<main class="py-5 bg-dark" style="min-height: 80vh;">
  <div class="container py-5">
    <?php
    printFlashMessages();
    ?>
    <section>
      <h1 class="mb-5 pb-5 h1 fw-bold text-white">All Platform Payments By Users/Geusts</h1>

      <table class="table table-striped table-dark">
        <thead>
          <tr>
            <th scope="col">Payment ID</th>
            <th scope="col">User ID</th>
            <th scope="col">Group ID</th>
            <th scope="col">Type ID</th>
            <th scope="col">Prepay ID</th>
            <th scope="col">Merchant Trade No</th>
            <th scope="col">Quantity</th>
            <th scope="col">Amount Price</th>
            <th scope="col">Using Wallet</th>
            <th scope="col">Status</th>
            <th scope="col">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ($payments as $payment) :
          ?>
            <tr>
              <th scope="row">#<?php echo $payment["id"]; ?></th>
              <td>#<?php echo $payment["user_id"]; ?></td>
              <td>#<?php echo $payment["group_id"]; ?></td>
              <td>#<?php echo $payment["type_id"]; ?></td>
              <td><?php echo $payment["prepay_id"]; ?></td>
              <td><?php echo $payment["merchantTradeNo"]; ?></td>
              <td><?php echo $payment["quantity"]; ?></td>
              <td><?php echo $payment["price"]; ?></td>
              <td><?php echo ($payment["use_wallet"] ? "YES" : "NO"); ?></td>
              <td><?php echo $payment["status"]; ?></td>
              <td><?php echo $payment["date"]; ?></td>
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