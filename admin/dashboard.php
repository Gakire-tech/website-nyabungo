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
  <link rel="stylesheet" href="dashboard.css">
  <!-- <style>
    .sidebar {
      width: 260px;
      min-height: 100vh;
    }
    @media (max-width: 991.98px) {
      .sidebar { width: 100%; min-height: auto; }
    }
  </style> -->
</head>
<body class="d-flex" style="background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb') no-repeat center center/cover; min-height: 100vh;">

  <!-- Sidebar -->
  <aside class="sidebar d-flex flex-column glass-sidebar text-white">
    <img src="../assets/NyabungoLogo3OG.JPG" alt="Logo" class="p-2 border-bottom img-fluid mb-3">
    <nav class="flex-grow-1 p-3">
      <a href="dashboard.php" class="d-block px-3 py-2 rounded fw-semibold text-decoration-none mb-1 bg-light text-dark">Tableau de bord</a>
      <a href="messages.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1 text-white">Message</a>
      <a href="menus.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1 text-white">Menus</a>
      <a href="branches.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1 text-white">Branches</a>
      <a href="event_images.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1 text-white">Images des événements</a>
      <a href="evenements.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1 text-white">Événements</a>
      <a href="galeries.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1 text-white">Galeries</a>
      <a href="utilisateurs.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1 text-white">Utilisateurs</a>
      <a href="contenu.php" class="d-block px-3 py-2 rounded text-decoration-none mb-1 text-white">Contenu du site</a>
    </nav>
    <form action="logout.php" method="post" class="p-3 border-top">
      <button type="submit" class="btn btn-outline-light w-100 fw-bold">Déconnexion</button>
    </form>
  </aside>

  <!-- Main content -->
  <main class="flex-grow-1 p-5 text-white">
    <div class="mb-5 d-flex justify-content-between align-items-center">
      <h1 class="h4 fw-bold mb-0">Bienvenue, <?php echo htmlspecialchars($username); ?> !</h1>
      <span class="text-white-50 small">Dashboard administrateur</span>
    </div>
    <div class="row g-4">
      <div class="col-12 col-md-4">
        <div class="glass-card p-4 text-center">
          <div class="fs-2 mb-2">🍽️</div>
          <div class="fw-bold">Gestion des Menus</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="glass-card p-4 text-center">
          <div class="fs-2 mb-2">📅</div>
          <div class="fw-bold">Réservations</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="glass-card p-4 text-center">
          <div class="fs-2 mb-2">🎉</div>
          <div class="fw-bold">Événements</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="glass-card p-4 text-center">
          <div class="fs-2 mb-2">🖼️</div>
          <div class="fw-bold">Galeries</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="glass-card p-4 text-center">
          <div class="fs-2 mb-2">👤</div>
          <div class="fw-bold">Utilisateurs</div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="glass-card p-4 text-center">
          <div class="fs-2 mb-2">📝</div>
          <div class="fw-bold">Contenu du site</div>
        </div>
      </div>
    </div>
  </main>

  <!-- CSS styles -->
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      color: #fff;
    }

    .glass-sidebar {
      width: 260px;
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.1);
    }

    .sidebar a:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    @media (max-width: 991.98px) {
      .glass-sidebar {
        width: 100%;
        min-height: auto;
        flex-direction: row;
        overflow-x: auto;
      }
    }
  </style>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>