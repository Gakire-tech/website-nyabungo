<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}
$username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - NYABUNGO</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .sidebar {
      width: 260px;
      min-height: 100vh;
    }
    @media (max-width: 991.98px) {
      .sidebar { width: 100%; min-height: auto; }
    }
  </style>
</head>
<body class="bg-light d-flex">
  <!-- Sidebar -->
  <aside class="sidebar bg-white shadow-sm d-flex flex-column">
    <div class="p-4 border-bottom fw-bold fs-4 text-center">Admin NYABUNGO</div>
    <nav class="flex-grow-1 p-3">
      <a href="dashboard.php" class="d-block px-3 py-2 rounded fw-semibold text-decoration-none mb-1 bg-light">Tableau de bord</a>
      <a href="messages.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1">Message</a>
      <a href="menus.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1">Menus</a>
      <a href="branches.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1">Branches</a>
      <a href="event_images.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1">images des evenements</a>
      <a href="evenements.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1">Événements</a>
      <a href="galeries.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1">Galeries</a>
      <a href="utilisateurs.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1">Utilisateurs</a>
      <a href="contenu.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1">Contenu du site</a>
    </nav>
    <form action="logout.php" method="post" class="p-3 border-top">
      <button type="submit" class="btn btn-dark w-100 fw-bold">Déconnexion</button>
    </form>
  </aside>
  <!-- Main content -->
  <main class="flex-grow-1 p-5">
    <div class="mb-5 d-flex justify-content-between align-items-center">
      <h1 class="h4 fw-bold mb-0">Bienvenue, <?php echo htmlspecialchars($username); ?> !</h1>
      <span class="text-muted small">Dashboard administrateur</span>
    </div>
    <div class="row g-4">
      <div class="col-12 col-md-4">
        <div class="bg-white rounded shadow-sm p-4 text-center">
          <div class="fs-2 mb-2">🍽️</div>
          <div class="fw-bold">Gestion des Menus</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="bg-white rounded shadow-sm p-4 text-center">
          <div class="fs-2 mb-2">📅</div>
          <div class="fw-bold">Réservations</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="bg-white rounded shadow-sm p-4 text-center">
          <div class="fs-2 mb-2">🎉</div>
          <div class="fw-bold">Événements</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="bg-white rounded shadow-sm p-4 text-center">
          <div class="fs-2 mb-2">🖼️</div>
          <div class="fw-bold">Galeries</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="bg-white rounded shadow-sm p-4 text-center">
          <div class="fs-2 mb-2">👤</div>
          <div class="fw-bold">Utilisateurs</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="bg-white rounded shadow-sm p-4 text-center">
          <div class="fs-2 mb-2">📝</div>
          <div class="fw-bold">Contenu du site</div>
        </div>
      </div>
    </div>
  </main>
  <!-- Bootstrap JS Bundle (inclut Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 