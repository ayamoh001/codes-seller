<?php
require_once "./include/config.php";
require_once "./include/functions.php";

// printFlashMessages();
// exit;
$returnPath = "";
$user_id = "";
if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
  $user_id = (int) $_SESSION["user_id"];
  $user = getUser($user_id, $returnPath);
  $wallet = getUserWallet($user_id, $returnPath);
}

$groups = getGroups();
// echo "<pre>";
// var_dump($groups);
// echo "</pre>";

$canonicalPath = "";
$title = "Crypto Cards - Home";
require_once "./include/header.php";
?>

<main class="py-5 bg-dark">
  <div class="container my-5">

    <!-- Hero Section -->
    <section class="hero-section bg-dark text-light mb-5">
      <div class="text-center">
        <h1 class="display-4 fw-bold">INSTANT DELIVERY</h1>
        <a href="<?php echo $baseURL . ((isset($user_id) && $user_id != "") ? "/profile/" : "/login.php"); ?>" class="btn btn-primary btn-lg px-5 fw-bold my-2"><?php echo $user_id == "" ? "LOGIN NOW" : "Your Account"; ?></a>
        <p class="lead">And buy your code type</p>
      </div>
    </section>

    <!-- Cards Section -->
    <section id="products-groups-list" class="cards-section">
      <div class="row align-items-center justify-content-center">
        <?php foreach ($groups as $group) : ?>
          <div class="col-md-4 p-3">
            <div class="card h-100">
              <div class="card-body h-100">
                <div class="d-flex gap-2 mb-3">
                  <img src="<?php echo $baseURL . $group["image"]; ?>" class="w-50 rounded" style="aspect-ratio: 16/9 !important;" alt="<?php echo $group["title"]; ?>">
                  <div class="w-50 d-flex flex-column xalign-items-center justify-content-center">
                    <h5 class="card-title fs-6 w-75 line-clamp-2"><?php echo $group["title"]; ?></h5>
                    <p class="card-text line-clamp-2 text-muted"><?php echo $group["description"]; ?></p>
                  </div>
                </div>
                <?php
                if (count($group["types"])) :
                ?>
                  <select id="products-types-select-<?php echo $group["id"]; ?>" class="form-select mb-2">
                    <?php
                    $isFirst = true;
                    foreach ($group["types"] as $type => $products) :
                    ?>
                      <option <?php if ($isFirst) {
                                echo "selected";
                                $isFirst = false;
                              } ?> value="<?php echo $type; ?>"><?php echo $type; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div id="quantities-container-<?php echo $group["id"]; ?>">
                    <?php
                    $isFirst = true;
                    foreach ($group["types"] as $type => $typeData) :
                    ?>
                      <input type="number" min="1" max="<?php echo count($typeData["products"]); ?>" class="form-control mb-3" id="products-of-type-quantity-<?php echo $group["id"] . "-" . $type; ?>" placeholder="1" style="display: <?php echo $isFirst ? "block" : "none";
                                                                                                                                                                                                                                        $isFirst && $isFirst = false ?>;">
                    <?php endforeach; ?>
                  </div>
                <?php else : ?>
                  <div class="alert alert-warning p-3" role="alert">
                    <h4 class="alert-heading m-0">Out of stock!</h4>
                    <p class="alert-text m-0">Sorry, but this cards are out of stock.</p>
                  </div>
                <?php endif; ?>
                <div class="d-flex gap-2 mt-auto">
                  <button id="button-for-group-<?php echo $group["id"]; ?>" type="button" class="btn btn-primary fw-bold w-100">
                    <span id="total-price-for-group-<?php echo $group["id"]; ?>">0.0</span> USD
                  </button>
                  <a type="button" class="btn btn-outline-primary fw-bold w-100" href="https://wa.me/+60176940955" target="_blank" rel="noopener noreferrer">
                    Ask for it
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
</main>

<script type="module">
  const groups = await fetch("./backend/get_groups.php").then(res => res.json())
  // make the groups variable global
  window.groups = groups

  // console.log({
  //   groups
  // })
  async function proccedToCheckout(groupId, quantity, type, button) {
    console.log({
      groupId,
      quantity,
      type,
      button
    })
    button.disabled = true
    window.location.href = "./checkout.php?groupId=" + groupId + "&quantity=" + quantity + "&type=" + type
  }

  console.log({
    groups
  })

  for (let i in groups) {
    if (groups[i].types.length == 0) continue;

    let total = 0
    let selectedType = Object.keys(groups[i].types)[0]
    let selectedQuantity = 1
    let typeSelectInput = document.querySelector(`#products-types-select-${i}`)
    const quantitiesContainer = document.querySelector(`#quantities-container-${i}`)
    const types = groups[i].types
    const submitButton = document.querySelector(`#button-for-group-${i}`)
    const totalPriceButton = document.querySelector(`#total-price-for-group-${i}`)

    submitButton.addEventListener("click", () => {
      proccedToCheckout(groups[i].id, selectedQuantity, selectedType, submitButton)
    })

    function updateTotalPrice() {
      totalPriceButton.disabled = false
      total = groups[i].types[selectedType].price * selectedQuantity
      console.log({
        total
      })
      totalPriceButton.innerHTML = total
    }
    updateTotalPrice()

    const quantitiesInputsForType = quantitiesContainer.querySelectorAll(`[id^="products-of-type-quantity-"]`)
    quantitiesInputsForType.forEach(quantityOfTypeInput => {
      quantityOfTypeInput.addEventListener("change", () => {
        let value = quantityOfTypeInput.value
        if (value > types[selectedType].products.length) {
          quantityOfTypeInput.value = types[selectedType].products.length
        }
        selectedQuantity = quantityOfTypeInput.value
        console.log({
          selectedQuantity
        })
        updateTotalPrice()
      })
    })

    typeSelectInput.addEventListener("change", () => {
      selectedType = typeSelectInput.value
      // hide siblings
      const quantitiesInputsForType = quantitiesContainer.querySelectorAll(`[id^="products-of-type-quantity-"]`)
      quantitiesInputsForType.forEach(quantityOfTypeInput => {
        quantityOfTypeInput.style.display = "none"
      })

      // show target qunatity input
      const quantityOfType = document.querySelector(`#products-of-type-quantity-${i}-${CSS.escape(selectedType)}`)
      quantityOfType.style.display = "block"

      updateTotalPrice()
    })
  }
</script>
<?php
require_once "./include/footer.php";
?>