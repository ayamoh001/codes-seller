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
$getPaymentsStmt = $connection->prepare("SELECT py.*, pr.id AS product_id, pr.date AS product_date, pr.* FROM 
                                        `payments` As py
                                        INNER JOIN 
                                        `products` AS pr
                                        WHERE pr.payment_id = py.id");
$getPaymentsStmt->execute();
if ($getPaymentsStmt->errno) {
  showSessionAlert($getPaymentsStmt->error, "danger", true, $returnPath);
  exit;
}
$paymentsResult = $getPaymentsStmt->get_result();
$getPaymentsStmt->close();

$payments = [];

while ($row = $paymentsResult->fetch_assoc()) {
  $row;
  if (!isset($payments[$row["id"]])) {
    $payments[$row["id"]] = [
      'id' => $row["id"],
      'price' => $row["price"],
      'user_id' => $row["user_id"],
      'group_id' => $row["group_id"],
      'status' => $row["status"],
      'date' => $row["date"],
      'products' => []
    ];
  }

  $payments[$row["id"]]['products'][] = [
    'id' => $row["product_id"],
    'code_value' => $row["code_value"],
    'type' => $row["type"],
    'price' => $row["price"],
  ];
}

$title = "Admin Dashboard - Payments";

require_once "../include/admin/header.php";
?>


<main class="py-5 bg-dark" style="min-height: 100vh;">
  <div class="container py-5">
    <?php
    printFlashMessages();
    ?>
    <section>
      <h1 class="mb-5 pb-5 h1 fw-bold text-white">All Platform Payments By Users/Geusts</h1>

      <table class="table table-dark">
        <thead>
          <tr>
            <th scope="col">Payment ID</th>
            <th scope="col">User ID</th>
            <th scope="col">Group ID</th>
            <th scope="col">Prepay ID</th>
            <th scope="col">Quantity</th>
            <th scope="col">Amount Price</th>
            <th scope="col">Status</th>
            <th scope="col">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php
          while ($payment = $paymentsResult->fetch_assoc()) :
          ?>
            <tr>
              <th scope="row"><?php echo $payment["id"]; ?></th>
              <td><?php echo $payment["product_id"]; ?></td>
              <td><?php echo $payment["user_id"]; ?></td>
              <td><?php echo $payment["group_id"]; ?></td>
              <td><?php echo $payment["prepay_id"]; ?></td>
              <td><?php echo count($payment["products"]); ?></td>
              <td><?php echo $payment["price"]; ?></td>
              <td><?php echo $payment["status"]; ?></td>
              <td><?php echo $payment["date"]; ?></td>
            </tr>
          <?php
          endwhile;
          ?>
        </tbody>
      </table>
    </section>

</main>

<?php
require_once "../include/admin/footer.php";
?>