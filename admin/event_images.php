<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : null;
$message = '';

// Suppression
if ($deleteId) {
    $stmt = $pdo->prepare('SELECT image_path FROM event_images WHERE id = ?');
    $stmt->execute([$deleteId]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists(UPLOAD_DIR . $img)) {
        @unlink(UPLOAD_DIR . $img);
    }
    $stmt = $pdo->prepare('DELETE FROM event_images WHERE id = ?');
    $stmt->execute([$deleteId]);
    $message = "Image supprimée.";
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id = $_POST['branch_id'] ?? null;
    $alt_text = $_POST['alt_text'] ?? '';
    $display_order = $_POST['display_order'] ?? 0;
    $image_path = null;
    if (isset($_POST['id']) && $_POST['id']) {
        $id = $_POST['id'];
        // Si nouvelle image uploadée
        if (isset($_FILES['image_path']) && $_FILES['image_path']['tmp_name']) {
            $stmt = $pdo->prepare('SELECT image_path FROM event_images WHERE id = ?');
            $stmt->execute([$id]);
            $oldImg = $stmt->fetchColumn();
            if ($oldImg && file_exists(UPLOAD_DIR . $oldImg)) {
                @unlink(UPLOAD_DIR . $oldImg);
            }
            $image_path = uniqid('event_', true) . '.' . pathinfo($_FILES['image_path']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['image_path']['tmp_name'], UPLOAD_DIR . $image_path);
            $stmt = $pdo->prepare('UPDATE event_images SET branch_id=?, image_path=?, alt_text=?, display_order=? WHERE id=?');
            $stmt->execute([$branch_id, $image_path, $alt_text, $display_order, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE event_images SET branch_id=?, alt_text=?, display_order=? WHERE id=?');
            $stmt->execute([$branch_id, $alt_text, $display_order, $id]);
        }
        $message = "Image modifiée.";
    } else {
        // Insert
        if (isset($_FILES['image_path']) && $_FILES['image_path']['tmp_name']) {
            $image_path = uniqid('event_', true) . '.' . pathinfo($_FILES['image_path']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['image_path']['tmp_name'], UPLOAD_DIR . $image_path);
            $stmt = $pdo->prepare('INSERT INTO event_images (branch_id, image_path, alt_text, display_order) VALUES (?, ?, ?, ?)');
            $stmt->execute([$branch_id, $image_path, $alt_text, $display_order]);
            $message = "Image ajoutée.";
        } else {
            $message = "Veuillez sélectionner une image.";
        }
    }
}

// Récupérer les branches pour le select
$branches = $pdo->query('SELECT id, name FROM branches')->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des images d'événements
$eventImages = $pdo->query('SELECT e.*, b.name as branch_name FROM event_images e LEFT JOIN branches b ON e.branch_id = b.id ORDER BY e.display_order ASC, e.id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Si modification, récupérer l'image à éditer
$editImage = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM event_images WHERE id = ?');
    $stmt->execute([$editId]);
    $editImage = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des images d'événements - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100">
        <?php include 'sidebar.php'; ?>
      </aside>
      <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h4 mb-4">Gestion des images d'événements</h1>
        <?php if ($message): ?>
          <div class="alert alert-success"> <?= htmlspecialchars($message) ?> </div>
        <?php endif; ?>
        <!-- Formulaire ajout/modif -->
        <div class="card mb-4">
          <div class="card-header fw-bold">
            <?= $editImage ? 'Modifier une image' : 'Ajouter une image' ?>
          </div>
          <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="row g-3">
              <?php if ($editImage): ?>
                <input type="hidden" name="id" value="<?= $editImage['id'] ?>">
              <?php endif; ?>
              <div class="col-md-3">
                <label class="form-label">Succursale</label>
                <select name="branch_id" class="form-select" required>
                  <option value="">Choisir...</option>
                  <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($editImage && $editImage['branch_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Texte alternatif</label>
                <input type="text" name="alt_text" class="form-control" value="<?= $editImage['alt_text'] ?? '' ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">Ordre</label>
                <input type="number" name="display_order" class="form-control" value="<?= $editImage['display_order'] ?? 0 ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Image <?= $editImage ? '(laisser vide pour ne pas changer)' : '' ?></label>
                <input type="file" name="image_path" class="form-control" <?= $editImage ? '' : 'required' ?> accept="image/*">
                <?php if ($editImage && $editImage['image_path']): ?>
                  <img src="../assets/uploads/<?= htmlspecialchars($editImage['image_path']) ?>" alt="" class="img-thumbnail mt-2" style="max-height:80px;">
                <?php endif; ?>
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary fw-bold">Enregistrer</button>
                <?php if ($editImage): ?>
                  <a href="event_images.php" class="btn btn-secondary ms-2">Annuler</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
        <!-- Liste des images -->
        <div class="card">
          <div class="card-header fw-bold">Liste des images d'événements</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Succursale</th>
                    <th>Image</th>
                    <th>Texte alternatif</th>
                    <th>Ordre</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($eventImages as $img): ?>
                    <tr>
                      <td><?= $img['id'] ?></td>
                      <td><?= htmlspecialchars($img['branch_name'] ?? '') ?></td>
                      <td><?php if ($img['image_path']): ?><img src="../assets/uploads/<?= htmlspecialchars($img['image_path']) ?>" alt="" style="max-height:60px;max-width:90px;" class="rounded shadow-sm"><?php endif; ?></td>
                      <td><?= htmlspecialchars($img['alt_text']) ?></td>
                      <td><?= htmlspecialchars($img['display_order']) ?></td>
                      <td>
                        <a href="event_images.php?edit=<?= $img['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="event_images.php?delete=<?= $img['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette image ?');">Supprimer</a>
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