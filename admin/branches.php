<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

// Gestion des actions CRUD
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : null;
$message = '';

// Suppression
if ($deleteId) {
    $stmt = $pdo->prepare('DELETE FROM branches WHERE id = ?');
    $stmt->execute([$deleteId]);
    $message = "Succursale supprimée.";
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $google_maps_link = trim($_POST['google_maps_link'] ?? '');
    $opening_hours = trim($_POST['opening_hours'] ?? '');
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $stmt = $pdo->prepare('UPDATE branches SET name=?, address=?, phone=?, email=?, google_maps_link=?, opening_hours=? WHERE id=?');
        $stmt->execute([$name, $address, $phone, $email, $google_maps_link, $opening_hours, $_POST['id']]);
        $message = "Succursale modifiée.";
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO branches (name, address, phone, email, google_maps_link, opening_hours) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $address, $phone, $email, $google_maps_link, $opening_hours]);
        $message = "Succursale ajoutée.";
    }
}

// Récupérer la liste des branches
$branches = $pdo->query('SELECT * FROM branches ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Si modification, récupérer la branche à éditer
$editBranch = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM branches WHERE id = ?');
    $stmt->execute([$editId]);
    $editBranch = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des succursales - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100">
        <?php include 'sidebar.php'; ?>
      </aside>
      <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h4 mb-4">Gestion des succursales</h1>
        <?php if ($message): ?>
          <div class="alert alert-success"> <?= htmlspecialchars($message) ?> </div>
        <?php endif; ?>
        <!-- Formulaire ajout/modif -->
        <div class="card mb-4">
          <div class="card-header fw-bold">
            <?= $editBranch ? 'Modifier une succursale' : 'Ajouter une succursale' ?>
          </div>
          <div class="card-body">
            <form method="post" class="row g-3">
              <?php if ($editBranch): ?>
                <input type="hidden" name="id" value="<?= $editBranch['id'] ?>">
              <?php endif; ?>
              <div class="col-md-4">
                <label class="form-label">Nom</label>
                <input type="text" name="name" class="form-control" value="<?= $editBranch['name'] ?? '' ?>" required>
              </div>
              <div class="col-md-8">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" class="form-control" value="<?= $editBranch['address'] ?? '' ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control" value="<?= $editBranch['phone'] ?? '' ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= $editBranch['email'] ?? '' ?>">
              </div>
              <div class="col-md-8">
                <label class="form-label">Lien Google Maps (embed)</label>
                <input type="text" name="google_maps_link" class="form-control" value="<?= $editBranch['google_maps_link'] ?? '' ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Horaires d'ouverture</label>
                <input type="text" name="opening_hours" class="form-control" value="<?= $editBranch['opening_hours'] ?? '' ?>">
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary fw-bold">Enregistrer</button>
                <?php if ($editBranch): ?>
                  <a href="branches.php" class="btn btn-secondary ms-2">Annuler</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
        <!-- Liste des succursales -->
        <div class="card">
          <div class="card-header fw-bold">Liste des succursales</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Google Maps</th>
                    <th>Horaires</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($branches as $b): ?>
                    <tr>
                      <td><?= $b['id'] ?></td>
                      <td><?= htmlspecialchars($b['name']) ?></td>
                      <td><?= htmlspecialchars($b['address']) ?></td>
                      <td><?= htmlspecialchars($b['phone']) ?></td>
                      <td><?= htmlspecialchars($b['email']) ?></td>
                      <td><a href="<?= htmlspecialchars($b['google_maps_link']) ?>" target="_blank">Carte</a></td>
                      <td><?= htmlspecialchars($b['opening_hours']) ?></td>
                      <td>
                        <a href="branches.php?edit=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="branches.php?delete=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette succursale ?');">Supprimer</a>
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