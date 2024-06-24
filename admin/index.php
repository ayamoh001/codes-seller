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

$returnPath = "admin/index.php";

$groups = getGroupsForAdmin();
// echo "<pre>";
// var_dump($groups);
// echo "</pre>";

$title = "Admin Dashboard - Home";

require_once "../include/admin/header.php";
?>

<main class="py-5 bg-dark" style="min-height: 100vh;">
  <div class="container py-5">
    <?php
    printFlashMessages();
    ?>


    <section>
      <h1 class="mb-5 h1 fw-bold text-white">All Platform Registed Users</h1>

      <button type="button" class="btn btn-primary btn-lg fw-bold ratio-16x9 mb-5 px-4 d-flex gap-2" data-bs-toggle="modal" data-bs-target="#create-group-modal-create-new-group">
        <span>Create New Group</span>
        <i class="bi bi-plus-circle"></i>
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
                  <form action="<?php echo $baseURL; ?>/backend/create_group.php" method="POST" enctype="multipart/form-data" class="m-0 p-0">
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
                    <div class="mb-3 form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="switch" name="visibility" checked>
                      <label class="form-check-label" for="switch">Visible for Visitors</label>
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

    <section class="d-flex flex-column gap-4">
      <?php
      foreach ($groups as $group) :
      ?>
        <div class="card w-100">
          <div class="card-body">
            <div class="d-flex gap-2 mb-3">
              <img src="<?php echo $baseURL . $group["image"]; ?>" class="rounded ratio-16x9" alt="<?php echo $group["title"]; ?>" width="96">
              <h5 class="card-title xw-75 my-auto line-clamp-1"><?php echo $group["title"]; ?></h5>
            </div>
            <p class="card-text line-clamp-2"><?php echo $group["description"]; ?></p>
            <?php if (count($group["types"]) > 0) : ?>
              <div class="bg-body-secondary overflow-auto my-4" style="max-height: 160px;">
                <table class="table table-secondary w-100 m-0">
                  <thead>
                    <tr>
                      <th scope="col" class="fw-normal">ID</th>
                      <th scope="col" class="fw-normal">Name</th>
                      <th scope="col" class="fw-normal">Price</th>
                      <th scope="col" class="fw-normal">Products</th>
                      <th scope="col" class="fw-normal">Sort</th>
                      <th scope="col" class="fw-normal">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    foreach ($group["types"] as $type) :
                      // var_dump($type);
                    ?>
                      <tr>
                        <th scope="row"><?php echo $type["id"]; ?></th>
                        <td class="fw-bold"><?php echo $type["name"]; ?></td>
                        <td class="fw-bold"><?php echo $type["price"]; ?></td>
                        <td class="fw-bold text-success"><?php echo count($type["products"]) ? count($type["products"]) : "<p style='width: fit-content;' class='fw-normal bg-danger rounded-pill m-0 px-2 py-1'><span class='fs-6 text-white'>OUT OF STOCK</span></p>";  ?></td>
                        <td class="fw-bold"><?php echo $type["sort_index"]; ?></td>
                        <td class="d-flex gap-2">
                          <div style="height: fit-content;">
                            <!-- List products of this type Button -->
                            <button type="button" class="btn btn-outline-primary btn-sm fw-bold w-100 d-flex gap-2 justify-content-center" data-bs-toggle="modal" data-bs-target="#add-product-modal-<?php echo $type["id"]; ?>">
                              <i class="bi bi-eye"></i>
                            </button>
                            <!-- List products of this type Modal -->
                            <div class="modal fade" id="add-product-modal-<?php echo $type["id"] ?>" aria-labelledby="add-product-label-<?php echo $type["id"] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="add-product-label-<?php echo $type["id"] ?>">All products of this type</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                                    <div class="d-flex gap-2 flex-column">
                                      <?php foreach ($type["products"] as $product) : ?>
                                        <div class="d-flex gap-2 w-100 align-items-center justify-content-between">
                                          <p class="fw-bold m-0"><?php echo $product["code_value"]; ?></p>
                                          <form action="<?php echo $baseURL; ?>/backend/delete_product.php" method="POST" enctype="multipart/form-data" class="m-0 p-0">
                                            <input type="hidden" name="product_id" value="<?php echo $product["id"] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">
                                              <i class="bi bi-trash"></i>
                                            </button>
                                          </form>
                                        </div>
                                      <?php endforeach; ?>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div style="height: fit-content;">
                            <!-- Add products to type Button -->
                            <button type="button" class="btn btn-primary btn-sm fw-bold w-100 d-flex gap-2 justify-content-center" data-bs-toggle="modal" data-bs-target="#add-product-modal-<?php echo $type["id"]; ?>">
                              <i class="bi bi-plus-circle"></i>
                            </button>
                            <!-- Add products to type Modal -->
                            <div class="modal fade" id="add-product-modal-<?php echo $type["id"] ?>" aria-labelledby="add-product-label-<?php echo $type["id"] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="add-product-label-<?php echo $type["id"] ?>">Add products to this type</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="row justify-content-center">
                                      <div class="col-md-6 w-100 m-0">
                                        <form action="<?php echo $baseURL; ?>/backend/create_product.php" method="POST" enctype="multipart/form-data" class="m-0 p-0">
                                          <input type="hidden" name="type_id" value="<?php echo $type["id"] ?>">
                                          <div class="mb-3">
                                            <label for="file" class="form-label">Add multiple codes (txt/csv files)</label>
                                            <input type="file" class="form-control" id="file" name="file" accept=".txt,.csv">
                                          </div>
                                          <div class="d-flex gap-2 w-100 align-items-center justify-content-center">
                                            <hr class="w-100">
                                            <span class="fw-bold">OR</span>
                                            <hr class="w-100">
                                          </div>
                                          <div class="mb-3">
                                            <label for="code_value" class="form-label">Code Value</label>
                                            <input type="text" class="form-control" id="code_value" name="code_value" placeholder="code value...">
                                          </div>
                                          <button type="submit" class="btn btn-primary w-100 fw-bold">Add Product(s)</button>
                                        </form>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div style="height: fit-content;">
                            <!-- Edit type Button -->
                            <button type="button" class="btn btn-warning btn-sm fw-bold w-100 d-flex gap-2 justify-content-center" data-bs-toggle="modal" data-bs-target="#edit-type-modal-<?php echo $type["id"]; ?>">
                              <i class="bi bi-pencil-square"></i>
                            </button>
                            <!-- Edit type Modal -->
                            <div class="modal fade" id="edit-type-modal-<?php echo $type["id"] ?>" aria-labelledby="edit-type-label-<?php echo $type["id"] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="edit-type-label-<?php echo $type["id"] ?>">Edit this type of products</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="row justify-content-center">
                                      <div class="col-md-6 w-100 m-0">
                                        <form action="<?php echo $baseURL; ?>/backend/edit_type.php" method="POST" enctype="multipart/form-data" class="m-0 p-0">
                                          <input type="hidden" name="type_id" value="<?php echo $type["id"] ?>">
                                          <div class="mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="name" required value="<?php echo $type["name"] ?>">
                                          </div>
                                          <div class="mb-3">
                                            <label for="price" class="form-label">Price</label>
                                            <input type="number" class="form-control" step="0.01" id="price" name="price" placeholder="price" required value="<?php echo $type["price"] ?>">
                                          </div>
                                          <div class="mb-3">
                                            <label for="sort" class="form-label">Sort</label>
                                            <input type="number" min="1" class="form-control" id="sort" name="sort_index" placeholder="0" required value="<?php echo $type["sort_index"] ?>">
                                          </div>
                                          <button type="submit" class="btn btn-warning w-100 fw-bold">Edit Type</button>
                                        </form>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div style="height: fit-content;">
                            <!-- delete type Button -->
                            <button type="button" class="btn btn-danger btn-sm fw-bold w-100 d-flex gap-2 justify-content-center" data-bs-toggle="modal" data-bs-target="#delete-type-modal-<?php echo $type["id"]; ?>">
                              <i class="bi bi-trash"></i>
                            </button>
                            <!-- delete type Modal -->
                            <div class="modal fade" id="delete-type-modal-<?php echo $type["id"] ?>" aria-labelledby="delete-type-label-<?php echo $type["id"] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="delete-type-label-<?php echo $type["id"] ?>">Are you sure you want to delete this type?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <div class="row justify-content-center">
                                      <div class="col-md-6 w-100 m-0">
                                        <form action="<?php echo $baseURL; ?>/backend/delete_type.php" method="POST" enctype="multipart/form-data" class="m-0 p-0">
                                          <input type="hidden" name="type_id" value="<?php echo $type["id"] ?>">
                                          <button type="submit" class="btn btn-danger w-100 fw-bold">Delete Type</button>
                                        </form>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </td>
                      </tr>
                    <?php
                    endforeach;
                    ?>
                  </tbody>
                </table>
              </div>
            <?php else : ?>
              <div class="alert alert-warning p-3" role="alert">
                <h4 class="alert-heading m-0">Out of stock!</h4>
                <p class="alert-text m-0">Add more types and products to this groups.</p>
              </div>
            <?php endif; ?>
            <div class="d-flex gap-2">
              <!-- Add Product button -->
              <button type="button" class="btn btn-primary fw-bold w-100 d-flex gap-2 justify-content-center" data-bs-toggle="modal" data-bs-target="#add-type-modal-<?php echo $group["id"]; ?>">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Add New Type</span>
              </button>
              <!-- Add type Modal -->
              <div class="modal fade" id="add-type-modal-<?php echo $group["id"] ?>" aria-labelledby="add-type-label-<?php echo $group["id"] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="add-type-label-<?php echo $group["id"] ?>">Add new cards type for this group</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row justify-content-center">
                        <div class="col-md-6 w-100 m-0">
                          <form action="<?php echo $baseURL; ?>/backend/create_type.php" method="POST" enctype="multipart/form-data" class="m-0 p-0">
                            <input type="hidden" name="group_id" value="<?php echo $group["id"] ?>">
                            <div class="mb-3">
                              <label for="type" class="form-label">Type Name (e.g 50$)</label>
                              <input type="text" class="form-control" id="type_name" name="type_name" placeholder="25$" required>
                            </div>
                            <div class="mb-3">
                              <label for="price" class="form-label">Price ($)</label>
                              <input type="number" min="0.01" step="0.01" class="form-control" id="price" name="price" placeholder="00.00" required>
                            </div>
                            <div class="mb-3">
                              <label for="sort" class="form-label">Sort</label>
                              <input type="number" min="1" class="form-control" id="sort" name="sort_index" placeholder="0" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Add New Type</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Edit Group Button -->
              <button type="button" class="btn btn-warning fw-bold w-100 d-flex gap-2 justify-content-center" data-bs-toggle="modal" data-bs-target="#edit-group-modal-<?php echo $group["id"]; ?>">
                <i class="bi bi-pencil-square"></i>
                <span>Edit Group</span>
              </button>
              <!-- Edit Group Modal -->
              <div class="modal fade" id="edit-group-modal-<?php echo $group["id"] ?>" aria-labelledby="edit-group-label-<?php echo $group["id"] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="edit-group-label-<?php echo $group["id"] ?>">Edit this group of types</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row justify-content-center">
                        <div class="col-md-6 w-100 m-0">
                          <form action="<?php echo $baseURL; ?>/backend/edit_group.php" method="POST" enctype="multipart/form-data" class="m-0 p-0">
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
                            <div class="mb-3 form-check form-switch">
                              <input class="form-check-input" type="checkbox" role="switch" id="switch" name="visibility" <?php echo !!$group["visibility"] ? "checked" : "" ?>>
                              <label class="form-check-label" for="switch">Visible for Visitors</label>
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