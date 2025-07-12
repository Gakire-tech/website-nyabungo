<?php
// public/includes/header.php
// Cette partie sera incluse dans toutes les pages publiques

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/lang.php';

session_start();
// Gestion de la langue
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'fr';
$text = $langs[$lang];

$pdo = getPDOConnection();

// TODO: Vous pourriez vouloir récupérer les éléments du menu de navigation dynamiquement ici si nécessaire

?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>NYABUNGO RESTAURANT & BAR</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
  <!-- Header/Navbar -->
  <nav class="navbar navbar-custom px-0" id="mainNavbar">
    <div class="container-fluid px-lg-5 px-2 d-flex align-items-center" style="min-height:90px;">
      <!-- Menu gauche -->
      <div class="navbar-section">
        <ul class="navbar-nav flex-row menu-nav">
          <li class="nav-item"><a class="nav-link" href="index.php"><?= htmlspecialchars($text['home']) ?></a></li>
          <li class="nav-item"><a class="nav-link" href="evenements.php"><?= htmlspecialchars($text['events']) ?></a></li>
          <li class="nav-item"><a class="nav-link" href="galeries.php"><?= htmlspecialchars($text['gallery']) ?></a></li>
        </ul>
      </div>
      <!-- Logo centre -->
      <div class="navbar-section center">
        <a class="navbar-logo mx-auto" href="index.php"><img src="../assets/logo.jpg" alt="NYABUNGO Logo" class="navbar-logo-img"></a>
      </div>
      <!-- Menu droit -->
      <div class="navbar-section right">
        <ul class="navbar-nav flex-row align-items-center menu-nav">
          <li class="nav-item"><a class="nav-link" href="a-propos.php"><?= htmlspecialchars($text['about']) ?></a></li>
          <li class="nav-item"><a class="nav-link" href="contact.php"><?= htmlspecialchars($text['contact']) ?></a></li>
          <li class="nav-item dropdown lang-dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <?= htmlspecialchars($text['lang']) ?>
            </a>
            <ul class="dropdown-menu" aria-labelledby="langDropdown">
              <li><a class="dropdown-item<?= $lang === 'fr' ? ' active' : '' ?>" href="?lang=fr">FR</a></li>
              <li><a class="dropdown-item<?= $lang === 'en' ? ' active' : '' ?>" href="?lang=en">EN</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</body>
</html> 