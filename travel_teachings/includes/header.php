<?php
// includes/header.php  — call with $pageTitle set
$pageTitle = $pageTitle ?? 'TravelTeachings';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="TravelTeachings – Tourism study notes and resources by Dr. Renu Malra">
<title><?= htmlspecialchars($pageTitle) ?> | TravelTeachings</title>
<link rel="icon" type="image/png" href="assets/images/logo.png">
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<!-- Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<!-- Styles -->
<link rel="stylesheet" href="assets/css/main.css">
</head>
<body>

<header class="site-header" id="site-header">
  <div class="header-inner">
    <a href="index.php" class="brand">
      <img src="assets/images/logo.png" alt="TravelTeachings Logo" class="brand-logo">
      <span class="brand-name">Travel<em>Teachings</em></span>
    </a>

    <nav class="main-nav" id="main-nav">
      <ul>
        <li><a href="index.php"  <?= basename($_SERVER['PHP_SELF']) === 'index.php'  ? 'class="active"' : '' ?>>Home</a></li>
        <li><a href="study.php"  <?= basename($_SERVER['PHP_SELF']) === 'study.php'  ? 'class="active"' : '' ?>>Study Material</a></li>
        <li><a href="about.php"  <?= basename($_SERVER['PHP_SELF']) === 'about.php'  ? 'class="active"' : '' ?>>About</a></li>
        <li><a href="contact.php"<?= basename($_SERVER['PHP_SELF']) === 'contact.php'? 'class="active"' : '' ?>>Ask Us</a></li>
      </ul>
    </nav>

    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>
