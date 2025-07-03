<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

// Gestion des actions CRUD
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : null;
$message = '';

// Suppression
if ($deleteId) {
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$deleteId]);
    $message = "Utilisateur supprimé.";
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'editor';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        if ($password) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, role=?, is_active=?, password_hash=? WHERE id=?');
            $stmt->execute([$username, $email, $role, $is_active, $password_hash, $_POST['id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, role=?, is_active=? WHERE id=?');
            $stmt->execute([$username, $email, $role, $is_active, $_POST['id']]);
        }
        $message = "Utilisateur modifié.";
    } else {
        // Insert
        if (!$password) {
            $message = "Le mot de passe est requis pour la création.";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, role, is_active, password_hash) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$username, $email, $role, $is_active, $password_hash]);
            $message = "Utilisateur ajouté.";
        }
    }
}

// Récupérer la liste des utilisateurs
$users = $pdo->query('SELECT * FROM users ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Si modification, récupérer l'utilisateur à éditer
$editUser = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des utilisateurs - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100">
        <?php include 'sidebar.php'; ?>
      </aside>
      <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h4 mb-4">Gestion des utilisateurs</h1>
        <?php if ($message): ?>
          <div class="alert alert-success"> <?= htmlspecialchars($message) ?> </div>
        <?php endif; ?>
        <!-- Formulaire ajout/modif -->
        <div class="card mb-4">
          <div class="card-header fw-bold">
            <?= $editUser ? 'Modifier un utilisateur' : 'Ajouter un utilisateur' ?>
          </div>
          <div class="card-body">
            <form method="post" class="row g-3">
              <?php if ($editUser): ?>
                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
              <?php endif; ?>
              <div class="col-md-3">
                <label class="form-label">Nom d'utilisateur</label>
                <input type="text" name="username" class="form-control" value="<?= $editUser['username'] ?? '' ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= $editUser['email'] ?? '' ?>" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Rôle</label>
                <select name="role" class="form-select">
                  <option value="admin" <?= (isset($editUser['role']) && $editUser['role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                  <option value="editor" <?= (!isset($editUser['role']) || $editUser['role'] == 'editor') ? 'selected' : '' ?>>Éditeur</option>
                </select>
              </div>
              <div class="col-md-2 d-flex align-items-center">
                <div class="form-check mt-4">
                  <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= (isset($editUser['is_active']) && $editUser['is_active']) ? 'checked' : '' ?> >
                  <label class="form-check-label" for="is_active">Actif</label>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Mot de passe <?= $editUser ? '(laisser vide pour ne pas changer)' : '' ?></label>
                <input type="password" name="password" class="form-control" <?= $editUser ? '' : 'required' ?> autocomplete="new-password">
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary fw-bold">Enregistrer</button>
                <?php if ($editUser): ?>
                  <a href="utilisateurs.php" class="btn btn-secondary ms-2">Annuler</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
        <!-- Liste des utilisateurs -->
        <div class="card">
          <div class="card-header fw-bold">Liste des utilisateurs</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nom d'utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actif</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($users as $u): ?>
                    <tr>
                      <td><?= $u['id'] ?></td>
                      <td><?= htmlspecialchars($u['username']) ?></td>
                      <td><?= htmlspecialchars($u['email']) ?></td>
                      <td><?= htmlspecialchars($u['role']) ?></td>
                      <td><?= $u['is_active'] ? '<span class="text-success">Oui</span>' : '<span class="text-danger">Non</span>' ?></td>
                      <td>
                        <a href="utilisateurs.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="utilisateurs.php?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>
</html> 