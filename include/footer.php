  <!-- Footer -->
  <footer class="bg-dark text-light py-5 py-md-4 border-top h-100">
    <div class="container text-center d-flex gap-4 flex-column-reverse flex-md-row align-items-center justify-content-between">
      <nav class="navbar-nav d-flex flex-row gap-3">
        <a class="p-0 m-0 nav-link" href="https://www.instagram.com/crypto_cards_store?igsh=MWE5cWcxYTVvcmNlMw==">
          <i class="bi bi-instagram"></i>
        </a>
        <a class="p-0 m-0 nav-link" href="https://t.me/SamsupplierX">
          <i class="bi bi-telegram"></i>
        </a>
      </nav>
      <div class="d-flex gap-2 gap-md-3 flex-column flex-md-row align-items-center justify-content-between">
        <a href="<?php echo $baseURL; ?>/policies.php" class="underline text-white ">Our Terms & Policies</a>
        <div class="d-none d-md-block vr" style="height: 20px;"></div>
        <p class="p-0 m-0">&copy;<?php echo date("Y"); ?> Crypto Cards. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <span id="whatsapp-button" class="position-fixed fixed-bottom d-flex flex-column align-items-center m-auto mb-3 me-3" style="width: fit-content">
    <a href="https://wa.me/+601167999817" target="_blank" rel="noopener noreferrer" class="d-flex align-items-center justify-content-center overflow-hidden rounded-circle p-2 shadow-sm" style="background-color: #43c354;">
      <img src="<?php echo $baseURL; ?>/assets/images/icons/whatsapp-logo.webp" width="42" alt="التواصل عبر الواتساب">
    </a>
  </span>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
  <script src="<?php echo $baseURL; ?>/assets/js/app.js"></script>
  </body>

  </html>
  <?php
  $connection->close();
  ?>