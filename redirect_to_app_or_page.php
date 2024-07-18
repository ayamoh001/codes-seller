<?php
// Example deep link and web link
$deepLink = $_GET['deepLink'] ?? "";
$webLink = $_GET['webLink'] ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redirecting...</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script type="text/javascript">
    function redirectToApp(deepLink, webLink) {
      var start = new Date().getTime();
      var timeout = setTimeout(function() {
        var end = new Date().getTime();
        if (end - start < 2000) {
          window.location.href = webLink;
        }
      }, 1500);

      window.location.href = deepLink;

      window.onblur = function() {
        clearTimeout(timeout);
      };
    }

    window.onload = function() {
      var deepLink = '<?php echo $deepLink; ?>';
      var webLink = '<?php echo $webLink; ?>';
      redirectToApp(deepLink, webLink);
    };
  </script>
</head>

<body class="bg-dark text-white d-flex align-items-center justify-content-center vh-100">
  <div class="text-center">
    <div class="spinner-border text-warning mb-3" role="status">
      <span class="visually-hidden">Processing...</span>
    </div>
    <h1 class="h4 mb-3">Redirecting to the App</h1>
    <p class="lead">Please wait while we check if the app is installed on your device.</p>
    <p>If the app does not open automatically, you will be redirected to our website shortly.</p>
  </div>
</body>

</html>