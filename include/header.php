<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="initial-scale=1, width=device-width">
  <link rel="shortcut icon" href="<?php echo $baseURL; ?>/favicon.ico" type="image/x-icon">
  <link rel="icon" href="<?php echo $baseURL; ?>/favicon.ico" type="image/x-icon">
  <title><?php echo $title; ?></title>
  <meta name="description" content="Crypto gaming cards online store">
  <meta name="category" content="digital, cards, crypto">
  <meta name="classification" content="digital, cards, crypto">
  <meta name="keywords" content="cryptogamingards.com, Crypto Cards, Crypto Gaming Cards, CryptoCards, CryptoGamingCards">
  <meta name="publisher" content="Crypto Cards">
  <meta name="author" content="Crypto Cards">
  <meta name="creator" content="Crypto Cards">
  <meta name="theme-color" content="#F7931A">
  <meta name="color-scheme" content="dark">
  <meta name="referrer" content="origin">
  <link rel="canonical" href="<?php echo $baseURL . (isset($canonicalPath) ? $canonicalPath : ""); ?>">
  <link rel="alternate" type="application/rss+xml" href="rss.xml" title="rss">
  <meta name="format-detection" content="telephone=yes, date=yes, address=yes, email=yes, url=yes">
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
  <link rel="author" href="<?php echo $baseURL; ?>" />
  <meta property="og:locale" content="en-US">
  <meta property="og:type" content="website">
  <meta property="og:image" content="<?php echo $baseURL; ?>/assets/images/crypto-cards-og-thumbnile.png">
  <meta property="og:title" content="Crypto Cards">
  <meta property="og:description" content="Crypto gaming cards online store">
  <meta property="og:url" content="<?php echo $baseURL; ?>">
  <meta property="og:site_name" content="Crypto Cards">
  <meta property="article:modified_time" content="2024-05-4T00:00:000+03:00">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="<?php echo $baseURL; ?>/assets/images/crypto-cards-og-thumbnile.png">
  <meta name="twitter:label1" content="estimated read time">
  <meta name="twitter:data1" content="1 minute">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="Crypto Cards">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <script type="application/ld+json">
    {
      "@context": "http://schema.org",
      "@type": "Store",
      "url": "https://cryptogamingcards.com",
      "name": "Crypto Cards",
      "description": "Crypto gaming cards online store",
      "logo": "<?php echo $baseURL; ?>/assets/images/crypto-cards-logo.png",
      "paymentAccepted": "Cryptocurrencies",
      "telephone": "+601167999817",
      "email": "crypto.cards.dealer24.7@gmail.com",
      "priceRange": "$",
      "image": [
        "<?php echo $baseURL; ?>/assets/images/crypto-cards-og-thumbnail.png"
      ],
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer service",
        "telephone": "+601167999817",
        "email": "crypto.cards.dealer24.7@gmail.com",
        "areaServed": "Malaysia"
      },
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Main Street",
        "addressLocality": "Kuala Lumpur",
        "addressRegion": "Wilayah Persekutuan Kuala Lumpur",
        "postalCode": "50000",
        "addressCountry": "Malaysia"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 3.139,
        "longitude": 101.6869
      }
    }
  </script>

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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="inter-without-weight min-vh-100">
  <header class="d-block bg-dark bg-gradient py-3">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand ratio-16x9" href="<?php echo $baseURL; ?>" width="120">
          <img class="ratio-16x9" src="<?php echo $baseURL; ?>/assets/images/Crypto-Cards-Logo-Light.svg" alt="Crypto Cards Logo" width="120">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $baseURL; ?>">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $baseURL; ?>/policies.php">Policies</a>
            </li>
            <?php if (isset($user_id) && $user_id != "") : ?>
              <div class="d-flex gap-3 gap-lg-0 align-items-center">
                <div class="d-flex flex-row-reverse flex-lg-row gap-3 gap-lg-0 justify-content-end">
                  <li class="nav-item">
                    <a href="<?php echo $baseURL; ?>/profile/wallet.php" class="nav-link ms-lg-2 fw-bold text-warning"><?php echo $wallet["balance"] ?> USD</a>
                  </li>
                  <li class="nav-item">
                    <a href="<?php echo $baseURL; ?>/profile/index.php" class="d-block rounded-circle overflow-hidden shadow border-1 border-white ms-lg-2" style="width: 42px; height: 42px;">
                      <img src="<?php echo $baseURL . (isset($user["profile_picture"]) && $user["profile_picture"] != "" ? $user["profile_picture"] : "/assets/images/profile-picture.png") ?>" class="object-cover h-100 w-100" alt="profile picture" width="42">
                    </a>
                  </li>
                </div>
                <li class="nav-item ms-lg-3">
                  <span class="nav-link">
                    <form action="<?php echo $baseURL ?>/backend/auth.php" method="POST">
                      <input type="hidden" name="do" value="logout">
                      <button class="btn btn-sm btn-outline-danger d-flex gap-2 align-items-center px-3" type="submit">
                        <span>Logout</span>
                        <i class="bi bi-door-open-fill"></i>
                      </button>
                    </form>
                  </span>
                </li>
              </div>
            <?php else : ?>
              <li class="nav-item">
                <a class="btn btn-sm btn-primary ms-md-2 px-4 d-flex align-items-center justify-content-center gap-2" href="<?php echo $baseURL; ?>/login.php">
                  <span>Login</span>
                  <i class="bi bi-door-closed-fill"></i>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </nav>
    </div>
  </header>