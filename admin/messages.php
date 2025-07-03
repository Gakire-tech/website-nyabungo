<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getPDOConnection();

$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : null;
$message = '';

// Suppression
if ($deleteId) {
    $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = ?');
    $stmt->execute([$deleteId]);
    $message = "Message supprimé.";
}

// Récupérer la liste des messages
$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY submission_date DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des messages de contact - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container-fluid">
    <div class="row">
      <aside class="col-md-3 col-lg-2 bg-white border-end min-vh-100">
        <?php include 'sidebar.php'; ?>
      </aside>
      <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h4 mb-4">Messages de contact</h1>
        <?php if ($message): ?>
          <div class="alert alert-success"> <?= htmlspecialchars((string)$message) ?> </div>
        <?php endif; ?>
        <!-- Liste des messages -->
        <div class="card">
          <div class="card-header fw-bold">Liste des messages reçus</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($messages as $m): ?>
                    <tr>
                      <td><?= $m['id'] ?></td>
                      <td><?= htmlspecialchars((string)($m['name'] ?? '')) ?></td>
                      <td><a href="mailto:<?= htmlspecialchars((string)($m['email'] ?? '')) ?>"><?= htmlspecialchars((string)($m['email'] ?? '')) ?></a></td>
                      <td><?= nl2br(htmlspecialchars((string)($m['message'] ?? ''))) ?></td>
                      <td><?= htmlspecialchars((string)($m['submission_date'] ?? '')) ?></td>
                      <td>
                        <a href="messages.php?delete=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce message ?');">Supprimer</a>
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