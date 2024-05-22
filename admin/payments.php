<?php
require_once "../include/config.php";

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


$getPaymentsStmt = $connection->prepare("SELECT py.*, pr.id AS product_id, pr.price AS product_price, pr.date AS product_date, pr.* FROM 
                                        payments As py 
                                        INNER JOIN 
                                        products AS pr
                                        WHERE pr.payment_id = py.id");
$getPaymentsStmt->execute();
if ($getPaymentsStmt->errno) {
  $_SESSION['flash_message'] = $getPaymentsStmt->error;
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/admin/payments.php");
  exit;
}
$paymentsResult = $getPaymentsStmt->get_result();
$getPaymentsStmt->close();

$payments = [];

// while ($row = $paymentsResult->fetch_assoc()) {
//   $row;
//   if (!isset($payments[$row["id"]])) {
//     $payments[$row["id"]] = [
//       'id' => $row["id"],
//       'price' => $row["price"],
//       'user_id' => $row["user_id"],
//       'group_id' => $row["group_id"],
//       'status' => $row["status"],
//       'date' => $row["date"],
//       'products' => []
//     ];
//   }

//   $payments[$row["id"]]['products'][] = [
//     'id' => $row["product_id"],
//     'code_value' => $row["code_value"],
//     'type' => $row["type"],
//     'price' => $row["price"],
//   ];
// }

// Convert associative array to indexed array
// $payments = array_values($payments);

$title = "Admin Dashboard - Payments";

require_once "../include/admin/header.php";
?>


<main class="py-5 bg-dark" style="min-height: 100vh;">
  <div class="container py-5">
    <?php
    if (isset($_SESSION['flash_message'])) {
      echo '<div class="alert alert-' . $_SESSION['flash_type'] . '">' . $_SESSION['flash_message'] . '</div>';

      unset($_SESSION['flash_message']);
      unset($_SESSION['flash_type']);
    }
    ?>

    <table class="table table-dark">
      <thead>
        <tr>
          <th scope="col">Payment ID</th>
          <th scope="col">Product ID</th>
          <th scope="col">Product Price</th>
          <th scope="col">User ID</th>
          <th scope="col">Date</th>
        </tr>
      </thead>
      <tbody>
        <?php
        while ($payment = $paymentsResult->fetch_assoc()) :
        ?>
          <tr>
            <th scope="row" rowspan="<?php echo $productsInRow ?>"><?php echo $payment["id"]; ?></th>
            <td><?php echo $payment["product_id"]; ?></td>
            <td><?php echo $payment["product_price"]; ?></td>
            <td><?php echo $payment["user_id"]; ?></td>
            <td><?php echo $payment["date"]; ?></td>
          </tr>
        <?php
        endwhile;
        ?>
      </tbody>
    </table>
</main>
</div>
</m>

<?php
require_once "../include/admin/footer.php";
?>