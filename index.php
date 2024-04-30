<?php
include "./include/config.php";

$groups = [
  [
    "id" => "1",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content.",
    "price" => 2.50,
    "image" => "/storage/groups/car-image.webp",
  ],
  [
    "id" => "2",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content.",
    "price" => 2.50,
    "image" => "/storage/groups/car-image.webp",
  ],
  [
    "id" => "3",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content.",
    "price" => 2.50,
    "image" => "/storage/groups/car-image.webp",
  ],
  [
    "id" => "4",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content.",
    "price" => 2.50,
    "image" => "/storage/groups/car-image.webp",
  ],
  [
    "id" => "5",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content.",
    "price" => 2.50,
    "image" => "/storage/groups/car-image.webp",
  ],
  [
    "id" => "6",
    "title" => "Card title",
    "description" => "Some quick example text to build on the card title and make up the bulk of the card's content.",
    "price" => 2.50,
    "image" => "/storage/groups/car-image.webp",
  ],
];

$title = "إسم الموقع - الرئيسية";
include "./include/header.php";
?>

<!-- Hero Section -->
<section class="hero-section bg-dark text-light py-5">
  <div class="container text-center py-5">
    <h1 class="display-4 fw-bold">Welcome to Your Website</h1>
    <p class="lead">Your best digital products' deals</p>
    <a href="#products-groups-list" class="btn btn-primary btn-lg px-4">Get Started</a>
  </div>
</section>

<!-- Cards Section -->
<section id="products-groups-list" class="cards-section py-5">
  <div class="container">
    <div class="row">
      <?php for ($i = 0; $i < count($groups); $i++) : ?>
        <div class="col-md-4 p-3">
          <div class="card">
            <img src="<?php echo $baseURL . $groups[$i]["image"]; ?>" class="card-img-top" alt="<?php echo $groups[$i]["title"]; ?>">
            <div class="card-body">
              <h5 class="card-title"><?php echo $groups[$i]["title"]; ?></h5>
              <p class="card-text"><?php echo $groups[$i]["description"]; ?></p>
              <button type="button" class="btn btn-primary fw-bold w-100" data-bs-toggle="modal" data-bs-target="#group-modal-<?php echo $groups[$i]["id"]; ?>">
                <?php echo $groups[$i]["price"]; ?> USD
              </button>
            </div>
          </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="group-modal-<?php echo $groups[$i]["id"]; ?>" aria-labelledby="exampleModalLabel<?php echo $groups[$i]["id"]; ?>" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel<?php echo $groups[$i]["id"]; ?>"><?php echo $groups[$i]["title"]; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div id="group-info">
                  <?php echo $groups[$i]["description"]; ?>
                </div>
                <div id="payment-status" style="display: none;"></div>
                <div id="product-info" style="display: none;"></div>
              </div>
              <div class="modal-footer">
                <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> -->
                <button type="button" class="btn btn-primary btn-lg w-100 fw-bold">Buy for <?php echo $groups[$i]["price"] . " USD"; ?></button>
              </div>
            </div>
          </div>
        </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<?php
include "./include/footer.php";
?>