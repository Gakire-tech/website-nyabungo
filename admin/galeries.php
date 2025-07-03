<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

// Gestion des actions CRUD
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : null;
$message = '';

// Suppression
if ($deleteId) {
    $stmt = $pdo->prepare('SELECT image FROM galleries WHERE id = ?');
    $stmt->execute([$deleteId]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists(UPLOAD_DIR . $img)) {
        @unlink(UPLOAD_DIR . $img);
    }
    $stmt = $pdo->prepare('DELETE FROM galleries WHERE id = ?');
    $stmt->execute([$deleteId]);
    $message = "Image supprimée.";
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id = $_POST['branch_id'] ?? null;
    $caption = $_POST['caption'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $display_order = $_POST['display_order'] ?? 0;
    $image = null;
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $id = $_POST['id'];
        // Si nouvelle image uploadée
        if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
            $stmt = $pdo->prepare('SELECT image FROM galleries WHERE id = ?');
            $stmt->execute([$id]);
            $oldImg = $stmt->fetchColumn();
            if ($oldImg && file_exists(UPLOAD_DIR . $oldImg)) {
                @unlink(UPLOAD_DIR . $oldImg);
            }
            $image = uniqid('gal_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $image);
            $stmt = $pdo->prepare('UPDATE galleries SET branch_id=?, image=?, caption=?, is_active=?, display_order=? WHERE id=?');
            $stmt->execute([$branch_id, $image, $caption, $is_active, $display_order, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE galleries SET branch_id=?, caption=?, is_active=?, display_order=? WHERE id=?');
            $stmt->execute([$branch_id, $caption, $is_active, $display_order, $id]);
        }
        $message = "Image modifiée.";
    } else {
        // Insert
        if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
            $image = uniqid('gal_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $image);
            $stmt = $pdo->prepare('INSERT INTO galleries (branch_id, image, caption, is_active, display_order) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$branch_id, $image, $caption, $is_active, $display_order]);
            $message = "Image ajoutée.";
        } else {
            $message = "Veuillez sélectionner une image.";
        }
    }
}

// Récupérer les branches pour le select
$branches = $pdo->query('SELECT id, name FROM branches')->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des images de galerie
$galeries = $pdo->query('SELECT g.*, b.name as branch_name FROM galleries g LEFT JOIN branches b ON g.branch_id = b.id ORDER BY g.display_order ASC, g.id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Si modification, récupérer l'image à éditer
$editGalerie = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM galleries WHERE id = ?');
    $stmt->execute([$editId]);
    $editGalerie = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des galeries - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100">
        <?php include 'sidebar.php'; ?>
      </aside>
      <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h4 mb-4">Gestion des galeries</h1>
        <?php if ($message): ?>
          <div class="alert alert-success"> <?= htmlspecialchars($message) ?> </div>
        <?php endif; ?>
        <!-- Formulaire ajout/modif -->
        <div class="card mb-4">
          <div class="card-header fw-bold">
            <?= $editGalerie ? 'Modifier une image' : 'Ajouter une image' ?>
          </div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="row g-3">
              <?php if ($editGalerie): ?>
                <input type="hidden" name="id" value="<?= $editGalerie['id'] ?>">
              <?php endif; ?>
              <div class="col-md-3">
                <label class="form-label">Succursale</label>
                <select name="branch_id" class="form-select" required>
                  <option value="">Choisir...</option>
                  <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($editGalerie && $editGalerie['branch_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Légende</label>
                <input type="text" name="caption" class="form-control" value="<?= $editGalerie['caption'] ?? '' ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">Ordre</label>
                <input type="number" name="display_order" class="form-control" value="<?= $editGalerie['display_order'] ?? 0 ?>">
              </div>
              <div class="col-md-2 d-flex align-items-center">
                <div class="form-check mt-4">
                  <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= (isset($editGalerie['is_active']) && $editGalerie['is_active']) ? 'checked' : '' ?> >
                  <label class="form-check-label" for="is_active">Active</label>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Image <?= $editGalerie ? '(laisser vide pour ne pas changer)' : '' ?></label>
                <input type="file" name="image" class="form-control" <?= $editGalerie ? '' : 'required' ?> accept="image/*">
                <?php if ($editGalerie && $editGalerie['image']): ?>
                  <img src="../assets/uploads/<?= htmlspecialchars($editGalerie['image']) ?>" alt="" class="img-thumbnail mt-2" style="max-height:80px;">
                <?php endif; ?>
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary fw-bold">Enregistrer</button>
                <?php if ($editGalerie): ?>
                  <a href="galeries.php" class="btn btn-secondary ms-2">Annuler</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
        <!-- Liste des images -->
        <div class="card">
          <div class="card-header fw-bold">Liste des images</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Succursale</th>
                    <th>Image</th>
                    <th>Légende</th>
                    <th>Ordre</th>
                    <th>Active</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($galeries as $g): ?>
                    <tr>
                      <td><?= $g['id'] ?></td>
                      <td><?= htmlspecialchars($g['branch_name'] ?? '') ?></td>
                      <td><?php if ($g['image']): ?><img src="../assets/uploads/<?= htmlspecialchars($g['image']) ?>" alt="" style="max-height:60px;max-width:90px;" class="rounded shadow-sm"><?php endif; ?></td>
                      <td><?= htmlspecialchars($g['caption']) ?></td>
                      <td><?= htmlspecialchars($g['display_order']) ?></td>
                      <td><?= $g['is_active'] ? '<span class="text-success">Oui</span>' : '<span class="text-danger">Non</span>' ?></td>
                      <td>
                        <a href="galeries.php?edit=<?= $g['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="galeries.php?delete=<?= $g['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette image ?');">Supprimer</a>
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