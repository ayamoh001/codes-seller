<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="initial-scale=1, width=device-width">
  <title><?php echo $title; ?></title>
  <link rel="shortcut icon" href="<?php echo $baseURL; ?>/favicon.ico" type="image/x-icon">
  <link rel="icon" href="<?php echo $baseURL; ?>/favicon.ico" type="image/x-icon">
  <meta name="theme-color" content="#C29725">
  <meta name="color-scheme" content="dark">
  <meta name="referrer" content="origin">
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <link rel="canonical" href="<?php echo $baseURL; ?>">
  <meta name="format-detection" content="telephone=yes, date=yes, address=yes, email=yes, url=yes">
  <meta name="robots" content="no-index, nofollow">


  <!-- <link rel='dns-prefetch' href='https://www.googletagmanager.com' />
  <script src='https://www.googletagmanager.com/gtag/js?id=' defer></script>
  <script defer>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', '---------');
  </script> -->

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

  <link href="<?php echo $baseURL; ?>/assets/css/main.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body class="inter-without-weight">
  <header class="d-block bg-dark bg-gradient py-3">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand ratio-16x9" href="<?php echo $baseURL; ?>" width="120">
          <img class="ratio-16x9" src="<?php echo $baseURL; ?>/assets/images/Crypto-Cards-Logo.svg" alt="Crypto Cards Logo" width="120">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $baseURL; ?>/profile.php">profile</a>
            </li>
            <li class="nav-item ms-2">
              <div class="rounded-pill overflow-hidden shadow border-1 border-white">
                <img src="<?php echo $baseURL . (isset($user["profile_picture"]) && $user["profile_picture"] != "" ? $user["profile_picture"] : "/assets/images/profile-picture.png") ?>" alt="profile picture" width="42">
              </div>
            </li>
            <li class="nav-item ms-3">
              <span class="nav-link p-1" href="<?php echo $baseURL; ?>/profile.php">
                <form action="<?php echo $baseURL ?>/backend/auth.php" method="POST">
                  <input type="hidden" name="do" value="logout">
                  <button class="btn btn-danger" type="submit">Logout</button>
                </form>
              </span>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </header>