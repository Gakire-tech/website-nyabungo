<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');

// Gestion des actions CRUD pour les menus
$editMenuId = isset($_GET['edit_menu']) ? (int)$_GET['edit_menu'] : null;
$deleteMenuId = isset($_GET['delete_menu']) ? (int)$_GET['delete_menu'] : null;
$editItemId = isset($_GET['edit_item']) ? (int)$_GET['edit_item'] : null;
$deleteItemId = isset($_GET['delete_item']) ? (int)$_GET['delete_item'] : null;
$message = '';

// Suppression d'un menu
if ($deleteMenuId) {
    $pdo->prepare('DELETE FROM menu_items WHERE menu_id = ?')->execute([$deleteMenuId]);
    $pdo->prepare('DELETE FROM menus WHERE id = ?')->execute([$deleteMenuId]);
    $message = "Menu supprimé.";
}
// Suppression d'un item
if ($deleteItemId) {
    $stmt = $pdo->prepare('SELECT image FROM menu_items WHERE id = ?');
    $stmt->execute([$deleteItemId]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists(UPLOAD_DIR . $img)) {
        @unlink(UPLOAD_DIR . $img);
    }
    $pdo->prepare('DELETE FROM menu_items WHERE id = ?')->execute([$deleteItemId]);
    $message = "Item supprimé.";
}

// Ajout/modification d'un menu
if (isset($_POST['menu_submit'])) {
    $branch_id = $_POST['branch_id'] ?? '';
    $type = $_POST['type'] ?? 'restaurant';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    if ($_POST['menu_id']) {
        $stmt = $pdo->prepare('UPDATE menus SET branch_id=?, type=?, name=?, description=?, is_active=? WHERE id=?');
        $stmt->execute([$branch_id, $type, $name, $description, $is_active, $_POST['menu_id']]);
        $message = "Menu modifié.";
    } else {
        $stmt = $pdo->prepare('INSERT INTO menus (branch_id, type, name, description, is_active) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$branch_id, $type, $name, $description, $is_active]);
        $message = "Menu ajouté.";
    }
}
// Ajout/modification d'un item
if (isset($_POST['item_submit'])) {
    $menu_id = $_POST['menu_id'] ?? '';
    $name = $_POST['item_name'] ?? '';
    $description = $_POST['item_description'] ?? '';
    $price = $_POST['item_price'] ?? 0;
    $allergens = $_POST['item_allergens'] ?? '';
    $is_available = isset($_POST['item_is_available']) ? 1 : 0;
    $image = null;
    if ($_POST['item_id']) {
        // Update
        if (isset($_FILES['item_image']) && $_FILES['item_image']['tmp_name']) {
            $stmt = $pdo->prepare('SELECT image FROM menu_items WHERE id = ?');
            $stmt->execute([$_POST['item_id']]);
            $oldImg = $stmt->fetchColumn();
            if ($oldImg && file_exists(UPLOAD_DIR . $oldImg)) {
                @unlink(UPLOAD_DIR . $oldImg);
            }
            $image = uniqid('menu_', true) . '.' . pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['item_image']['tmp_name'], UPLOAD_DIR . $image);
            $stmt = $pdo->prepare('UPDATE menu_items SET name=?, description=?, price=?, allergens=?, is_available=?, image=? WHERE id=?');
            $stmt->execute([$name, $description, $price, $allergens, $is_available, $image, $_POST['item_id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE menu_items SET name=?, description=?, price=?, allergens=?, is_available=? WHERE id=?');
            $stmt->execute([$name, $description, $price, $allergens, $is_available, $_POST['item_id']]);
        }
        $message = "Item modifié.";
    } else {
        // Insert
        if (isset($_FILES['item_image']) && $_FILES['item_image']['tmp_name']) {
            $image = uniqid('menu_', true) . '.' . pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['item_image']['tmp_name'], UPLOAD_DIR . $image);
        }
        $stmt = $pdo->prepare('INSERT INTO menu_items (menu_id, name, description, price, allergens, is_available, image) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$menu_id, $name, $description, $price, $allergens, $is_available, $image]);
        $message = "Item ajouté.";
    }
}

// Récupérer les branches pour le select
$branches = $pdo->query('SELECT id, name FROM branches')->fetchAll(PDO::FETCH_ASSOC);
// Récupérer la liste des menus
$menus = $pdo->query('SELECT m.*, b.name as branch_name FROM menus m LEFT JOIN branches b ON m.branch_id = b.id ORDER BY m.id DESC')->fetchAll(PDO::FETCH_ASSOC);
// Si modification, récupérer le menu à éditer
$editMenu = null;
if ($editMenuId) {
    $stmt = $pdo->prepare('SELECT * FROM menus WHERE id = ?');
    $stmt->execute([$editMenuId]);
    $editMenu = $stmt->fetch(PDO::FETCH_ASSOC);
}
// Récupérer les items du menu sélectionné
$menuItems = [];
if ($editMenuId) {
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE menu_id = ? ORDER BY id DESC');
    $stmt->execute([$editMenuId]);
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Si modification, récupérer l'item à éditer
$editItem = null;
if ($editItemId) {
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ?');
    $stmt->execute([$editItemId]);
    $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des menus - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100">
        <?php include 'sidebar.php'; ?>
      </aside>
      <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h4 mb-4">Gestion des menus</h1>
        <?php if ($message): ?>
          <div class="alert alert-success"> <?= htmlspecialchars((string)$message) ?> </div>
        <?php endif; ?>
        <!-- Formulaire ajout/modif menu -->
        <div class="card mb-4">
          <div class="card-header fw-bold">
            <?= $editMenu ? 'Modifier un menu' : 'Ajouter un menu' ?>
          </div>
          <div class="card-body">
            <form method="post" class="row g-3">
              <input type="hidden" name="menu_id" value="<?= $editMenu['id'] ?? '' ?>">
              <div class="col-md-3">
                <label class="form-label">Succursale</label>
                <select name="branch_id" class="form-select" required>
                  <option value="">Choisir...</option>
                  <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($editMenu && $editMenu['branch_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                  <option value="restaurant" <?= (isset($editMenu['type']) && $editMenu['type'] == 'restaurant') ? 'selected' : '' ?>>Restaurant</option>
                  <option value="bar" <?= (isset($editMenu['type']) && $editMenu['type'] == 'bar') ? 'selected' : '' ?>>Bar</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Nom du menu</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars((string)($editMenu['name'] ?? '')) ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars((string)($editMenu['description'] ?? '')) ?>">
              </div>
              <div class="col-md-2 d-flex align-items-center">
                <div class="form-check mt-4">
                  <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" <?= (isset($editMenu['is_active']) && $editMenu['is_active']) ? 'checked' : '' ?> >
                  <label class="form-check-label" for="is_active">Actif</label>
                </div>
              </div>
              <div class="col-12 text-end">
                <button type="submit" name="menu_submit" class="btn btn-primary fw-bold">Enregistrer</button>
                <?php if ($editMenu): ?>
                  <a href="menus.php" class="btn btn-secondary ms-2">Annuler</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
        <!-- Liste des menus -->
        <div class="card mb-4">
          <div class="card-header fw-bold">Liste des menus</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Succursale</th>
                    <th>Type</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Actif</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($menus as $m): ?>
                    <tr>
                      <td><?= $m['id'] ?></td>
                      <td><?= htmlspecialchars((string)($m['branch_name'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($m['type'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($m['name'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($m['description'] ?? '')) ?></td>
                      <td><?= $m['is_active'] ? '<span class="text-success">Oui</span>' : '<span class="text-danger">Non</span>' ?></td>
                      <td>
                        <a href="menus.php?edit_menu=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="menus.php?delete_menu=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce menu et tous ses items ?');">Supprimer</a>
                        <a href="menus.php?edit_menu=<?= $m['id'] ?>#items" class="btn btn-sm btn-outline-secondary">Items</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <!-- Gestion des items du menu sélectionné -->
        <?php if ($editMenuId): ?>
        <div class="card mb-4" id="items">
          <div class="card-header fw-bold">Items du menu "<?= htmlspecialchars((string)($editMenu['name'] ?? '')) ?>"</div>
          <div class="card-body">
            <!-- Formulaire ajout/modif item -->
            <form method="post" enctype="multipart/form-data" class="row g-3 mb-4">
              <input type="hidden" name="menu_id" value="<?= $editMenuId ?>">
              <input type="hidden" name="item_id" value="<?= $editItem['id'] ?? '' ?>">
              <div class="col-md-3">
                <label class="form-label">Nom de l'item</label>
                <input type="text" name="item_name" class="form-control" value="<?= htmlspecialchars((string)($editItem['name'] ?? '')) ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Description</label>
                <input type="text" name="item_description" class="form-control" value="<?= htmlspecialchars((string)($editItem['description'] ?? '')) ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">Prix</label>
                <input type="number" name="item_price" class="form-control" value="<?= htmlspecialchars((string)($editItem['price'] ?? '')) ?>" min="0" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Allergènes</label>
                <input type="text" name="item_allergens" class="form-control" value="<?= htmlspecialchars((string)($editItem['allergens'] ?? '')) ?>">
              </div>
              <div class="col-md-1 d-flex align-items-center">
                <div class="form-check mt-4">
                  <input class="form-check-input" type="checkbox" name="item_is_available" value="1" id="item_is_available" <?= (isset($editItem['is_available']) && $editItem['is_available']) ? 'checked' : '' ?> >
                  <label class="form-check-label" for="item_is_available">Dispo</label>
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Image <?= $editItem ? '(laisser vide pour ne pas changer)' : '' ?></label>
                <input type="file" name="item_image" class="form-control" accept="image/*">
                <?php if ($editItem && $editItem['image']): ?>
                  <img src="../assets/uploads/<?= htmlspecialchars((string)$editItem['image']) ?>" alt="" class="img-thumbnail mt-2" style="max-height:80px;">
                <?php endif; ?>
              </div>
              <div class="col-12 text-end">
                <button type="submit" name="item_submit" class="btn btn-primary fw-bold">Enregistrer l'item</button>
                <?php if ($editItem): ?>
                  <a href="menus.php?edit_menu=<?= $editMenuId ?>#items" class="btn btn-secondary ms-2">Annuler</a>
                <?php endif; ?>
              </div>
            </form>
            <!-- Liste des items -->
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Prix</th>
                    <th>Allergènes</th>
                    <th>Dispo</th>
                    <th>Image</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($menuItems as $item): ?>
                    <tr>
                      <td><?= $item['id'] ?></td>
                      <td><?= htmlspecialchars((string)($item['name'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($item['description'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($item['price'] ?? '')) ?></td>
                      <td><?= htmlspecialchars((string)($item['allergens'] ?? '')) ?></td>
                      <td><?= $item['is_available'] ? '<span class="text-success">Oui</span>' : '<span class="text-danger">Non</span>' ?></td>
                      <td><?php if ($item['image']): ?><img src="../assets/uploads/<?= htmlspecialchars((string)$item['image']) ?>" alt="" style="max-height:60px;max-width:90px;" class="rounded shadow-sm"><?php endif; ?></td>
                      <td>
                        <a href="menus.php?edit_menu=<?= $editMenuId ?>&edit_item=<?= $item['id'] ?>#items" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="menus.php?edit_menu=<?= $editMenuId ?>&delete_item=<?= $item['id'] ?>#items" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cet item ?');">Supprimer</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </main>
    </div>
  </div>
</body>
</html> 