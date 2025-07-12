<?php
session_start();
if (isset($_SESSION['admin_id'])) {
  header('Location: dashboard.php');
  exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once __DIR__ . '/../config/database.php';
  $username = trim(strtolower($_POST['username'] ?? ''));
  $password = $_POST['password'] ?? '';
  $pdo = getPDOConnection();
  // Recherche insensible à la casse et aux espaces
  $stmt = $pdo->prepare('SELECT * FROM users WHERE LOWER(TRIM(username)) = ? LIMIT 1');
  $stmt->execute([$username]);
  $user = $stmt->fetch();
  if ($user) {
    if (!$user['is_active']) {
      $error = 'Compte inactif. Contactez l\'administrateur.';
      error_log('Tentative de connexion sur compte inactif : ' . $username);
    } elseif (password_verify($password, $user['password_hash'])) {
      $_SESSION['admin_id'] = $user['id'];
      $_SESSION['admin_username'] = $user['username'];
      $_SESSION['admin_role'] = $user['role'];
      header('Location: dashboard.php');
      exit;
    } else {
      $error = 'Mot de passe incorrect.';
      error_log('Mot de passe incorrect pour : ' . $username);
    }
  } else {
    $error = 'Utilisateur non trouvé.';
    error_log('Utilisateur non trouvé : ' . $username);
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion Admin - NYABUNGO</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column justify-content-center align-items-center min-vh-100" style="background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb') no-repeat center center/cover;">
  <div class="glass-effect w-100 p-4" style="max-width: 400px; margin-top: 4rem;">
    <img src="../assets/NYABUUNGO.PNG" alt="Logo" class="img-fluid mb-2 rounded-circle w-25 d-block mx-auto">
    <?php if ($error): ?>
      <div class="mb-4 text-danger fw-bold text-center"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" class="vstack gap-3">
      <div>
        <label class="form-label fw-bold text-center">Votre Nom d'utilisateur</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div>
        <label class="form-label fw-bold text-center">Votre Mot de passe</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-dark w-100 fw-bold text-center">Se connecter</button>
    </form>
  </div>

  <style>
    .glass-effect {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 15px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      color: #fff;
    }

    .glass-effect input {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: #000;
    }

    .glass-effect input::placeholder {
      color: #666;
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>