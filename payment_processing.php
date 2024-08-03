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

$paymentId = $_GET['paymentId'] ?? "";
$transactionId = $_GET['transactionId'] ?? "";
$deepLink = $_GET['deepLink'] ?? "";
$webLink = $_GET['webLink'] ?? "";


$canonicalPath = "/payment_processing.php";
$title = "Crypto Cards - Payment Processing";
require_once "./include/header.php";
?>

<main class="py-5 bg-dark" style="min-height: 80vh;">
  <div class="container py-5 text-white d-flex flex-column gap-4 align-items-center justify-content-center">
    <?php
    printFlashMessages();
    ?>
    <div id="waiting-section" class="text-center">
      <div class="spinner-border text-warning mb-3" role="status">
        <span class="visually-hidden">Processing...</span>
      </div>
      <h1 class="h4 mb-3">Redirecting to the checkout</h1>
      <p class="lead">Please wait while we redirect you to the app or the web page.</p>
      <p class="fs-6">If the app does not open automatically, you will be redirected to Binance website shortly.</p>
      <div id="timer" class="display-4 fw-bold"></div>
    </div>

    <div id="result-section" class="text-center" style="display: none;">
      <h1 class="h4 mb-3 text-warning">Payment Successful!</h1>
      <p class="lead">These are your purchased codes, please copy them now.</p>
      <?php if (!$user_id) echo '<p class="mt-2 fs-6">If you lost while you have no account please contact the support.</p>'; ?>
      <table id="codes-table" class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <!-- will be filled by JS on success-->
        </tbody>
      </table>
      <button id="copy-all-button" class="btn btn-lg btn-primary">
        <i class="bi bi-clipboard"></i>
        <span>Copy All</span>
      </button>
    </div>

    <div id="errors-section" class="text-center" style="display: none;">
      <h1 class="h4 mb-3 text-danger">Errors Detected!</h1>
      <p class="lead">An error occurred while processing your payment. Please contact us with these inforamtion.</p>
      <table id="errors-table" class="table table-danger table-striped table-bordered table-hover text-start">
        <thead>
          <tr>
            <th>#</th>
            <th>Error</th>
          </tr>
        </thead>
        <tbody>
          <!-- will be filled by JS on success-->
        </tbody>
      </table>
    </div>
  </div>
</main>

<script type="text/javascript">
  function redirectToApp(deepLink, webLink) {
    if (!deepLink && !webLink) {
      return;
    }

    var start = new Date().getTime();
    var timeout = setTimeout(function() {
      var end = new Date().getTime();
      if (end - start < 2000) {
        console.log("open web link");
        window.open(webLink, '_blank');
      }
    }, 1500);

    window.location.href = deepLink;

    window.onblur = function() {
      clearTimeout(timeout);
    };
  }

  function checkPaymentStatus(paymentId) {
    if (typeof(EventSource) !== "undefined") {
      console.log("start checking payment status");
      const source = new EventSource('<?php echo $baseURL ?>/backend/check_payment_status.php?paymentId=' + paymentId);
      // Stop receiving SSE after 15 minutes
      setTimeout(() => {
        console.log('Closing SSE connection.');
        source.close();
      }, 1000 * 60 * 15);

      source.onmessage = function(event) {
        const data = JSON.parse(event.data);
        console.log({
          data
        })

        if (data.error) {
          console.error({
            error: data.error
          });
        }

        if (data.message) {
          console.log({
            message: data.message
          });
        }

        if (data.success) {
          source.close();

          const waitingSection = document.querySelector("#waiting-section")
          const resultSection = document.querySelector("#result-section")
          const errorsSection = document.querySelector("#errors-section")
          const codesTable = document.querySelector("#codes-table")
          const codesTableBody = codesTable.querySelector("tbody")
          const copyAllButton = document.querySelector("#copy-all-button")
          const timer = document.querySelector("#timer")

          waitingSection.style.display = "none";
          timer.style.display = "block";

          if (data?.products?.length) {
            codesTableBody.innerHTML = "";
            data.products.forEach((product, i) => {
              const row = document.createElement("tr");
              const codeIndex = document.createElement("td");
              codeIndex.innerHTML = i + 1;
              row.appendChild(codeIndex);
              const codeValue = document.createElement("td");
              codeValue.innerHTML = product;
              row.appendChild(codeValue);
              const copyButtonTd = document.createElement("td");
              const copyButton = document.createElement("button");
              copyButton.className = "btn btn-sm btn-outline-primary";
              copyButton.onclick = function() {
                copyToClipboard(product);
              };
              copyButton.innerHTML = "<i class='bi bi-clipboard'></i>";
              copyButtonTd.appendChild(copyButton);
              row.appendChild(copyButtonTd);

              codesTableBody.appendChild(row);
            });
            resultSection.style.display = "block";
            copyAllButton.addEventListener("click", function() {
              copyAllCodes(data.products);
            });
          }

          if (data?.errors?.length) {
            errorsSection.style.display = "block";
            errorsTable = document.querySelector("#errors-table")
            const errorsTableBody = errorsTable.querySelector("tbody")

            errorsTableBody.innerHTML = "";
            data.errors.forEach((product, i) => {
              const row = document.createElement("tr");
              const codeIndex = document.createElement("td");
              codeIndex.innerHTML = i + 1;
              row.appendChild(codeIndex);
              const codeValue = document.createElement("td");
              codeValue.innerHTML = product;
              row.appendChild(codeValue);

              errorsTableBody.appendChild(row);
            });
          }
        }
      };
    } else {
      alert("Sorry! Your browser does not support server-sent events. which prevents from the checking the payment status");
    }
  }

  function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
  }

  function copyAllCodes(codes) {
    let codesText = "";
    codes.forEach(function(code) {
      codesText += code + "\n";
    });
    copyToClipboard(codesText);
  }

  function startTimer() {
    const duration = 15 * 60; // 15 minutes in seconds
    const endTime = Date.now() + duration * 1000; // Current time + duration in milliseconds

    function updateTimer() {
      const now = Date.now();
      const remainingTime = Math.max(0, endTime - now); // Remaining time in milliseconds

      const minutes = Math.floor(remainingTime / 1000 / 60);
      const seconds = Math.floor((remainingTime / 1000) % 60);

      // Format the time
      const formattedMinutes = String(minutes).padStart(2, '0');
      const formattedSeconds = String(seconds).padStart(2, '0');

      // Display the timer
      document.getElementById('timer').textContent = `${formattedMinutes}:${formattedSeconds}`;

      if (remainingTime > 0) {
        // Update the timer every second
        requestAnimationFrame(updateTimer);
      } else {
        // Timer finished
        document.getElementById('timer').textContent = "Time out! Please try to buy again.";
      }
    }

    // Start the timer update
    updateTimer();
  }

  window.onload = function() {
    var paymentId = '<?php echo $paymentId; ?>';
    var transactionId = '<?php echo $transactionId; ?>';
    var deepLink = '<?php echo $deepLink; ?>';
    var webLink = '<?php echo $webLink; ?>';
    redirectToApp(deepLink, webLink);
    checkPaymentStatus(paymentId)
    startTimer();
  };
</script>

<?php
require_once "./include/footer.php";
?>