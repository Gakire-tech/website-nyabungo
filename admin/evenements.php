<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

// Gestion des actions CRUD
$action = $_GET['action'] ?? '';
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : null;
$message = '';

// Suppression
if ($deleteId) {
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
    $stmt->execute([$deleteId]);
    $message = "Événement supprimé.";
}

// Ajout ou modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_id = $_POST['branch_id'] ?? '';
    $event_type = $_POST['event_type'] ?? '';
    $requested_date = $_POST['requested_date'] ?? '';
    $guests = $_POST['guests'] ?? '';
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $message_evt = $_POST['message'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    if (isset($_POST['id']) && $_POST['id']) {
        // Update
        $stmt = $pdo->prepare('UPDATE events SET branch_id=?, event_type=?, requested_date=?, guests=?, name=?, phone=?, email=?, message=?, status=? WHERE id=?');
        $stmt->execute([$branch_id, $event_type, $requested_date, $guests, $name, $phone, $email, $message_evt, $status, $_POST['id']]);
        $message = "Événement modifié.";
    } else {
        // Insert
        $stmt = $pdo->prepare('INSERT INTO events (branch_id, event_type, requested_date, guests, name, phone, email, message, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$branch_id, $event_type, $requested_date, $guests, $name, $phone, $email, $message_evt, $status]);
        $message = "Événement ajouté.";
    }
}

// Récupérer les branches pour le select
$branches = $pdo->query('SELECT id, name FROM branches')->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des événements
$events = $pdo->query('SELECT e.*, b.name as branch_name FROM events e LEFT JOIN branches b ON e.branch_id = b.id ORDER BY e.requested_date DESC, e.id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Si modification, récupérer l'événement à éditer
$editEvent = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([$editId]);
    $editEvent = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des événements - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100">
        <?php include 'sidebar.php'; ?>
      </aside>
      <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h4 mb-4">Gestion des événements</h1>
        <?php if ($message): ?>
          <div class="alert alert-success"> <?= htmlspecialchars($message) ?> </div>
        <?php endif; ?>
        <!-- Formulaire ajout/modif -->
        <div class="card mb-4">
          <div class="card-header fw-bold">
            <?= $editEvent ? 'Modifier un événement' : 'Ajouter un événement' ?>
          </div>
          <div class="card-body">
            <form method="post" class="row g-3">
              <?php if ($editEvent): ?>
                <input type="hidden" name="id" value="<?= $editEvent['id'] ?>">
              <?php endif; ?>
              <div class="col-md-3">
                <label class="form-label">Succursale</label>
                <select name="branch_id" class="form-select" required>
                  <option value="">Choisir...</option>
                  <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($editEvent && $editEvent['branch_id'] == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Type d'événement</label>
                <input type="text" name="event_type" class="form-control" value="<?= $editEvent['event_type'] ?? '' ?>" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Date</label>
                <input type="date" name="requested_date" class="form-control" value="<?= $editEvent['requested_date'] ?? '' ?>" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Convives</label>
                <input type="number" name="guests" class="form-control" value="<?= $editEvent['guests'] ?? '' ?>" min="1" required>
              </div>
              <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                  <option value="pending" <?= (isset($editEvent['status']) && $editEvent['status'] == 'pending') ? 'selected' : '' ?>>En attente</option>
                  <option value="confirmed" <?= (isset($editEvent['status']) && $editEvent['status'] == 'confirmed') ? 'selected' : '' ?>>Confirmé</option>
                  <option value="cancelled" <?= (isset($editEvent['status']) && $editEvent['status'] == 'cancelled') ? 'selected' : '' ?>>Annulé</option>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label">Nom du client</label>
                <input type="text" name="name" class="form-control" value="<?= $editEvent['name'] ?? '' ?>" required>
              </div>
              <div class="col-md-3">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control" value="<?= $editEvent['phone'] ?? '' ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= $editEvent['email'] ?? '' ?>">
              </div>
              <div class="col-md-12">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="2"><?= $editEvent['message'] ?? '' ?></textarea>
              </div>
              <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary fw-bold">Enregistrer</button>
                <?php if ($editEvent): ?>
                  <a href="evenements.php" class="btn btn-secondary ms-2">Annuler</a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
        <!-- Liste des événements -->
        <div class="card">
          <div class="card-header fw-bold">Liste des événements</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Succursale</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Convives</th>
                    <th>Client</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Statut</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($events as $evt): ?>
                    <tr>
                      <td><?= $evt['id'] ?></td>
                      <td><?= htmlspecialchars($evt['branch_name'] ?? '') ?></td>
                      <td><?= htmlspecialchars($evt['event_type']) ?></td>
                      <td><?= htmlspecialchars($evt['requested_date']) ?></td>
                      <td><?= htmlspecialchars($evt['guests']) ?></td>
                      <td><?= htmlspecialchars($evt['name']) ?></td>
                      <td><?= htmlspecialchars($evt['phone']) ?></td>
                      <td><?= htmlspecialchars($evt['email']) ?></td>
                      <td><?= htmlspecialchars($evt['status']) ?></td>
                      <td>
                        <a href="evenements.php?edit=<?= $evt['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="evenements.php?delete=<?= $evt['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cet événement ?');">Supprimer</a>
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