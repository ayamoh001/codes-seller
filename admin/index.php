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

// TODO: optimize for clients
$getGroupsWithProuductsStmt = $connection->prepare("SELECT g.*, p.id AS product_id, p.code_value, p.type, p.price, p.payment_id
                                                    FROM `groups` g
                                                    LEFT JOIN products p ON g.id = p.group_id
                                                    WHERE p.payment_id IS NULL
                                                    ORDER BY g.sort_index, g.id");

$getGroupsWithProuductsStmt->execute();
if ($getGroupsWithProuductsStmt->errno) {
  $_SESSION['flash_message'] = $getGroupsWithProuductsStmt->error;
  $_SESSION['flash_type'] = "danger";
  header("Location: $baseURL/admin/");
  // $connection->rollback();
  exit;
}
$getGroupsWithProuductsResults = $getGroupsWithProuductsStmt->get_result();
$getGroupsWithProuductsStmt->close();

$groups = [];

while ($row = $getGroupsWithProuductsResults->fetch_assoc()) {
  // var_dump($row);
  if (!isset($groups[$row["id"]])) {
    $groups[$row["id"]] = [
      'id' => $row["id"],
      'title' => $row["title"],
      'description' => $row["description"],
      'image' => $row["image"],
      'sort_index' => $row["sort_index"],
      'date' => $row["date"],
      'products' => []
    ];
  }

  if (isset($row["product_id"])) {
    $groups[$row["id"]]["products"][$row["type"]][] = [
      'id' => $row["product_id"],
      'code_value' => $row["code_value"],
      'type' => $row["type"],
      'payment_id' => $row["payment_id"],
      'price' => $row["price"],
    ];
  }
}

// Convert associative array to indexed array
$groups = array_values($groups);

$title = "Admin Dashboard - Home";

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

    <section>
      <button type="button" class="btn btn-primary fw-bold ratio-16x9 mb-5" data-bs-toggle="modal" data-bs-target="#create-group-modal-create-new-group">
        Create New Group
      </button>

      <div class="modal fade" id="create-group-modal-create-new-group" aria-labelledby="create-exampleModalLabelnew-group" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="create-exampleModalLabelnew-group">Create New Group for Products</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="row justify-content-center">
                <div class="col-md-6 w-100 m-0">
                  <form action="<?php echo $baseURL; ?>/backend/create_group.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                      <label for="title" class="form-label">Title</label>
                      <input type="text" class="form-control" id="title" name="title" placeholder="Title" required>
                    </div>
                    <div class="mb-3">
                      <label for="description" class="form-label">Description</label>
                      <input type="text" class="form-control" id="description" name="description" placeholder="description" required>
                    </div>
                    <div class="mb-3">
                      <label for="sort" class="form-label">Sort</label>
                      <input type="number" min="1" class="form-control" id="sort" name="sort_index" placeholder="0" required>
                    </div>
                    <div class="mb-3">
                      <label for="image" class="form-label">Image</label>
                      <input type="file" class="form-control" id="image" name="image" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create</button>
                  </form>
                </div>
              </div>
            </div>
            <!-- <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary">Create</button>
            </div> -->
          </div>
        </div>
      </div>
    </section>

    <section>
      <?php
      foreach ($groups as $group) :
      ?>
        <div class="card w-100">
          <div class="card-body">
            <div class="d-flex gap-2 mb-3">
              <img src="<?php echo $baseURL . $group["image"]; ?>" class="rounded ratio-16x9" alt="<?php echo $group["title"]; ?>">
              <h5 class="card-title xw-75 my-auto line-clamp-1"><?php echo $group["title"]; ?></h5>
            </div>
            <p class="card-text line-clamp-2"><?php echo $group["description"]; ?></p>
            <table class="table w-100">
              <thead>
                <tr>
                  <th scope="col">Product ID</th>
                  <th scope="col">Price</th>
                  <th scope="col">Type</th>
                  <th scope="col">Code Value</th>
                  <th scope="col">Payment ID</th>
                  <th scope="col">Create Date</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($group["products"] as $product) :
                  var_dump($product);
                ?>
                  <tr>
                    <th scope="row"><?php echo $product["id"]; ?></th>
                    <td><?php echo $product["price"]; ?></td>
                    <td><?php echo $product["type"]; ?></td>
                    <td><?php echo $product["code_value"]; ?></td>
                    <td><?php echo $product["payment_id"]; ?></td>
                    <td><?php echo $product["date"]; ?></td>
                  </tr>
                <?php
                endforeach;
                ?>
              </tbody>
            </table>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-primary fw-bold w-100" data-bs-toggle="modal" data-bs-target="#add-product-modal-<?php echo $group["id"]; ?>">
                Add Product
              </button>
              <div class="modal fade" id="add-product-modal-<?php echo $group["id"] ?>" aria-labelledby="add-product-label-<?php echo $group["id"] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="add-product-label-<?php echo $group["id"] ?>">Add new product for this group</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row justify-content-center">
                        <div class="col-md-6 w-100 m-0">
                          <form action="<?php echo $baseURL; ?>/backend/create_product.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="group_id" value="<?php echo $group["id"] ?>">
                            <div class="mb-3">
                              <label for="code_value" class="form-label">Code Value</label>
                              <input type="text" class="form-control" id="code_value" name="code_value" placeholder="code_value" required>
                            </div>
                            <div class="mb-3">
                              <label for="type" class="form-label">Type/Class</label>
                              <input type="text" class="form-control" id="type" name="type" placeholder="25$" required>
                            </div>
                            <div class="mb-3">
                              <label for="type" class="form-label">Price ($)</label>
                              <input type="number" min="0.01" step="0.1" class="form-control" id="price" name="price" placeholder="00.00" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Add</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn-warning fw-bold w-100" data-bs-toggle="modal" data-bs-target="#edit-group-modal-<?php echo $group["id"]; ?>">
                Edit
              </button>
              <div class="modal fade" id="edit-group-modal-<?php echo $group["id"] ?>" aria-labelledby="edit-group-label-<?php echo $group["id"] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="edit-group-label-<?php echo $group["id"] ?>">Edit this group of products</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row justify-content-center">
                        <div class="col-md-6 w-100 m-0">
                          <form action="<?php echo $baseURL; ?>/backend/edit_group.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="group_id" value="<?php echo $group["id"] ?>">
                            <div class="mb-3">
                              <label for="title" class="form-label">Title</label>
                              <input type="text" class="form-control" id="title" name="title" placeholder="title" required value="<?php echo $group["title"] ?>">
                            </div>
                            <div class="mb-3">
                              <label for="description" class="form-label">Description</label>
                              <input type="text" class="form-control" id="description" name="description" placeholder="description" required value="<?php echo $group["description"] ?>">
                            </div>
                            <div class="mb-3">
                              <label for="sort" class="form-label">Sort</label>
                              <input type="number" min="1" class="form-control" id="sort" name="sort_index" placeholder="0" required value="<?php echo $group["sort_index"] ?>">
                            </div>
                            <div class="mb-3">
                              <label for="image" class="form-label">New Image</label>
                              <input type="file" class="form-control" id="image" name="image">
                            </div>
                            <button type="submit" class="btn btn-warning w-100 fw-bold">Edit</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      <?php
      endforeach;
      ?>
    </section>
  </div>
</main>


<?php
require_once "../include/admin/footer.php";
?>