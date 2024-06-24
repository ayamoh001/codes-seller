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

$groups = getGroups();
echo "<pre>";
var_dump($groups);
echo "</pre>";

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
            <div class="bg-body-secondary overflow-auto my-4" style="max-height: 160px;">
              <table class="table table-secondary w-100">
                <thead>
                  <tr>
                    <th scope="col">Type ID</th>
                    <th scope="col">Type Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Products</th>
                    <th scope="col">Sort Index</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($group["types"] as $type) :
                    // var_dump($type);
                  ?>
                    <tr>
                      <th scope="row"><?php echo $type["id"]; ?></th>
                      <td><?php echo $type["name"]; ?></td>
                      <td><?php echo $type["price"]; ?></td>
                      <td><?php echo count($type["products"]); ?></td>
                      <td><?php echo $type["sort_index"]; ?></td>
                      <td class="d-flex gap-2">
                        <div>
                          <input type="hidden" name="type_id" value="<?php echo $type["id"]; ?>">
                          <button class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></button>
                        </div>
                        <form action="<?php echo $baseURL; ?>/backend/delete_type.php" method="POST">
                          <input type="hidden" name="type_id" value="<?php echo $type["id"]; ?>">
                          <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                  <?php
                  endforeach;
                  ?>
                </tbody>
              </table>
            </div>
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
                          <form action="<?php echo $baseURL; ?>/backend/create_type.php" method="POST" enctype="multipart/form-data">
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