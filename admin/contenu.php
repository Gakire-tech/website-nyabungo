<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

// Gestion des actions CRUD
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : null;
$message = '';

// Suppression
if ($deleteId) {
    $stmt = $pdo->prepare('DELETE FROM site_content WHERE id = ?');
    $stmt->execute([$deleteId]);
    $message = "Contenu supprimé.";
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page = trim($_POST['page'] ?? '');
    $block = trim($_POST['block'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'published';
    $display_order = $_POST['display_order'] ?? 0;
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $stmt = $pdo->prepare('UPDATE site_content SET page=?, block=?, title=?, content=?, status=?, display_order=? WHERE id=?');
        $stmt->execute([$page, $block, $title, $content, $status, $display_order, $_POST['id']]);
        $message = "Contenu modifié.";
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO site_content (page, block, title, content, status, display_order) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$page, $block, $title, $content, $status, $display_order]);
        $message = "Contenu ajouté.";
    }
}

// Récupérer la liste des contenus
$contents = $pdo->query('SELECT * FROM site_content ORDER BY page, display_order, id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Si modification, récupérer le contenu à éditer
$editContent = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM site_content WHERE id = ?');
    $stmt->execute([$editId]);
    $editContent = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion du contenu du site - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100">
        <?php include 'sidebar.php'; ?>
      </aside>
      <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h4 mb-4">Gestion du contenu du site</h1>
        <?php if ($message): ?>
          <div class="alert alert-success"> <?= htmlspecialchars((string)$message) ?> </div>
        <?php endif; ?>
        <!-- Formulaire ajout/modif -->
        <div class="card mb-4">
          <div class="card-header fw-bold">
            <?= $editContent ? 'Modifier un contenu' : 'Ajouter un contenu' ?>
          </div>
          <div class="card-body">
            <form method="post" class="row g-3">
              <?php if ($editContent): ?>
                <input type="hidden" name="id" value="<?= $editContent['id'] ?>">
              <?php endif; ?>
              <div class="col-md-2">
                <label class="form-label">Page</label>
                <input type="text" name="page" class="form-control" value="<?= htmlspecialchars((string)($editContent['page'] ?? '')) ?>" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Bloc</label>
                <input type="text" name="block" class="form-control" value="<?= htmlspecialchars((string)($editContent['block'] ?? '')) ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Titre</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars((string)($editContent['title'] ?? '')) ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                  <option value="published" <?= (isset($editContent['status']) && $editContent['status'] == 'published') ? 'selected' : '' ?>>Publié</option>
                  <option value="draft" <?= (isset($editContent['status']) && $editContent['status'] == 'draft') ? 'selected' : '' ?>>Brouillon</option>
                </select>
              </div>
              <div class="col-md-1">
                <label class="form-label">Ordre</label>
                <input type="number" name="display_order" class="form-control" value="<?= htmlspecialchars((string)($editContent['display_order'] ?? 0)) ?>">
              </div>
              <div class="col-md-12">
                <label class="form-label">Contenu (HTML autorisé)</label>
                <textarea name="content" class="form-control" rows="4" required><?= htmlspecialchars((string)($editContent['content'] ?? '')) ?></textarea>
              </div>
              <?php if ($editContent && isset($editContent['last_updated'])): ?>
                <div class="col-md-4">
                  <label class="form-label">Dernière modification</label>
                  <input type="text" class="form-control" value="<?= htmlspecialchars((string)($editContent['last_updated'] ?? '')) ?>" readonly>
                </div>
              <?php endif; ?>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary fw-bold">Enregistrer</button>
                <?php if ($editContent): ?>
                  <a href="contenu.php" class="btn btn-secondary ms-2">Annuler</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
        <!-- Liste des contenus -->
        <div class="card">
          <div class="card-header fw-bold">Liste du contenu</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Page</th>
                    <th>Bloc</th>
                    <th>Titre</th>
                    <th>Statut</th>
                    <th>Ordre</th>
                    <th>Dernière modif</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($contents as $c): ?>
                    <tr>
                      <td><?= $c['id'] ?></td>
                      <td><?= htmlspecialchars((string)($c['page'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($c['block'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($c['title'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($c['status'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($c['display_order'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($c['last_updated'] ?? '')) ?></td>
                      <td>
                        <a href="contenu.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="contenu.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce contenu ?');">Supprimer</a>
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