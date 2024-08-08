<?php
require_once "./include/config.php";
require_once "./include/functions.php";

$user_id = "";
$user = null;
$returnPath = "policies.php";
if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != "") {
  try {
    $user_id = (int) $_SESSION["user_id"];
    $user = getUser($user_id, $returnPath);
    $wallet = getUserWallet($user_id, $returnPath);
  } catch (Throwable $e) {
    showSessionAlert("Error in the server!", "danger", true, $returnPath);
    logErrors($e);
    exit;
  }
}

$canonicalPath = "/policies.php";
$title = "Crypto Cards - Policies";
require_once "./include/header.php";
?>


<main class="bg-dark text-white py-5 min-vh-100">
  <div class="container py-5 my-auto text-start d-flex flex-column gap-4">
    <h1>Our Policies and Terms of Use</h1>

    <h2>Welcome to Crypto Cards!</h2>

    <p>Thank you for choosing Crypto Cards for your codes needs. By accessing or using our website, you agree to be bound by the following terms and conditions. Please read them carefully.</p>

    <h3>1. Acceptance of Terms</h3>

    <p>By using our website, you agree to comply with and be bound by these Terms of Use. If you do not agree to these terms, please do not use our website.</p>

    <h3>2. Eligibility</h3>

    <p>You must be at least 18 years old to use our services. By using our website, you represent and warrant that you are at least 18 years old.</p>

    <h3>3. Use of the Website</h3>

    <p>Account Creation: You may be required to create an account to access certain features of the website. You agree to provide accurate and complete information when creating an account.<br>
      <b>Account Security:</b> You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account. Notify us immediately of any unauthorized use of your account.<br>
      <b>Prohibited Conduct:</b> You agree not to use the website for any unlawful purpose or in any way that could harm Crypto Cards or its users. This includes, but is not limited to, uploading or transmitting viruses, spamming, or infringing on the intellectual property rights of others.
    </p>

    <h3>4. Purchasing Products (Codes)</h3>

    <p>
      <b>Payment Methods:</b> We accept payments via cryptocurrency through Binance. By making a purchase, you agree to pay the listed price for the code and any applicable fees.<br>
      <b>Order Processing:</b> Once payment is confirmed, your order will be processed. Please note that processing times may vary.<br>
      <b>Delivery:</b> codes will be delivered electronically on the processing page.<br>
      <b>Refunds and Exchanges:</b> All sales are final. We do not offer refunds or exchanges for purchased codes unless required by law.
    </p>

    <h3>5. Intellectual Property</h3>

    <p>
      <b>Ownership:</b> All content on the website, including text, graphics, logos, and images, is the property of Crypto Cards or its content suppliers and is protected by intellectual property laws.<br>
      <b>Use of Content:</b> You may use the content on our website for personal, non-commercial purposes only. You may not copy, reproduce, distribute, or create derivative works from the content without our express written permission.
    </p>

    <h3>6. Privacy Policy</h3>

    <p>
      <b>Data Collection:</b> We collect and use personal information in accordance with our Privacy Policy. By using the website, you consent to the collection and use of your information as described in the Privacy Policy.<br>
      <b>Security:</b> We take reasonable measures to protect your personal information, but we cannot guarantee absolute security.
    </p>

    <h3>7. Limitation of Liability</h3>

    <p>
      <b>No Warranty:</b> The website and its content are provided "as is" without any warranties of any kind, either express or implied.<br>
      <b>Limitation of Damages:</b> In no event shall Crypto Cards be liable for any indirect, incidental, special, or consequential damages arising out of or in connection with your use of the website or purchase of codes.<br>
    </p>

    <h3>8. Changes to Terms</h3>

    <p>We reserve the right to modify these Terms of Use at any time. Any changes will be effective immediately upon posting. Your continued use of the website constitutes your acceptance of the revised terms.</p>

    <h3>9. Governing Law</h3>

    <p>These Terms of Use are governed by and construed in accordance with the laws of the jurisdiction in which Crypto Cards operates, without regard to its conflict of law principles.</p>

    <h3>10. Contact Us</h3>

    <p>If you have any questions about these Terms of Use, please contact us at support@Crypto Cards.com.</p>

    <p class="fs-6 lead">By accessing and using the Crypto Cards website, you acknowledge that you have read, understood, and agree to be bound by these Terms of Use. Thank you for choosing Crypto Cards for your code purchases!</p>
  </div>
</main>

<?php
require_once "./include/footer.php";
?>