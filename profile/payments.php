<?php
require_once "../include/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == "") {
  $_SESSION['flash_message'] = "You are not logged in!";
  $_SESSION['flash_type'] = "danger";
  header("location: $baseURL/login.php");
  exit;
}

$user_id = "";
$user = null;

// get the user
$user_id = (int) $_SESSION["user_id"];
$getUserStmt = $connection->prepare("SELECT * FROM `users` WHERE id = ? AND `status` != 'BLOCKED' LIMIT 1");
$getUserStmt->bind_param("i", $user_id);
$getUserStmt->execute();
if ($getUserStmt->errno) {
  echo json_encode(["error" => "Error in the auth proccess! please try again."]);
  echo json_encode(["error" => $getUserStmt->error]);
  exit;
}
$userResult = $getUserStmt->get_result();
$user = $userResult->fetch_assoc();
$getUserStmt->close();
if (!$user) {
  $_SESSION['flash_message'] = "No user found with this ID!";
  $_SESSION['flash_type'] = "danger";
  header("location: $baseURL/login.php");
  exit;
}

// get the wallet
$getWalletStmt = $connection->prepare("SELECT * FROM `wallets` WHERE user_id = ? AND `status` != 'BLOCKED' LIMIT 1");
$getWalletStmt->bind_param("i", $user_id);
$getWalletStmt->execute();
if ($getWalletStmt->errno) {
  $_SESSION['flash_message'] = "Error in the wallet retriving process! please try again.";
  $_SESSION['flash_message'] = $getWalletStmt->error;
  $_SESSION['flash_type'] = "danger";
  exit;
}
$walletResult = $getWalletStmt->get_result();
$wallet = $walletResult->fetch_assoc();
$getWalletStmt->close();

$getPaymentsStmt = $connection->prepare("SELECT py.*, pr.id AS product_id, pr.date AS product_date, pr.* FROM 
                                        `payments` As py
                                        INNER JOIN 
                                        `products` AS pr
                                        WHERE pr.payment_id = py.id AND py.user_id = ?");

$getPaymentsStmt->bind_param("i", $user_id);
$getPaymentsStmt->execute();
if ($getPaymentsStmt->errno) {
  $_SESSION['flash_message'] = $getPaymentsStmt->error;
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/profile/payments.php");
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

$title = "Crypto Cards - Payments";
$breadcrumbs = [
  ["name" => "Home", "url" => "$baseURL/profile/"],
  ["name" => "Payments"]
];
require_once "../include/profile/header.php";
?>

<section>
  <h1 class="mb-5 h1 fw-bold text-white text-capitalize">All of your payments</h1>
  <table class="table table-dark">
    <thead>
      <tr>
        <th scope="col">Payment ID</th>
        <th scope="col">Prepay ID</th>
        <th scope="col">Quantity</th>
        <th scope="col">Amount Price</th>
        <th scope="col">Date</th>
      </tr>
    </thead>
    <tbody>
      <?php
      while ($payment = $paymentsResult->fetch_assoc()) :
      ?>
        <tr>
          <th scope="row"><?php echo $payment["id"]; ?></th>
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

<?php
require_once "../include/profile/footer.php";
?>