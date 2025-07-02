<?php
// public/galeries.php
$pageTitle = "Notre Galerie";

require_once __DIR__ . '/includes/header.php';

$selectedBranchId = $_GET['branch_id'] ?? null;

try {
    // Récupérer les branches pour le filtre
    $stmtBranches = $pdo->prepare("SELECT id, name FROM branches ORDER BY name ASC");
    $stmtBranches->execute();
    $branches = $stmtBranches->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les images de la galerie depuis la base de données avec filtre par branche
    $sqlImages = "SELECT image_path, alt_text FROM galleries";
    if ($selectedBranchId && is_numeric($selectedBranchId)) {
        $sqlImages .= " WHERE branch_id = :branch_id";
    }
    $sqlImages .= " ORDER BY display_order ASC";

    $stmtImages = $pdo->prepare($sqlImages);
    if ($selectedBranchId && is_numeric($selectedBranchId)) {
        $stmtImages->bindValue(':branch_id', $selectedBranchId, PDO::PARAM_INT);
    }
    $stmtImages->execute();
    $galleryImages = $stmtImages->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error for galleries page: " . $e->getMessage());
    $branches = [];
    $galleryImages = [];
}

?>

<main class="flex-fill p-4 container" style="max-width: 1100px; margin-top: 120px;">
  <h1 class="section-title text-center mb-4">Notre Galerie Photo</h1>

  <!-- Sélecteur de branche -->
  <div class="mb-4 d-flex justify-content-center">
    <form action="galeries.php" method="GET" class="d-flex align-items-center">
      <label for="branch_select" class="form-label me-2 mb-0 fw-bold">Filtrer par branche :</label>
      <select name="branch_id" id="branch_select" class="form-select w-auto" onchange="this.form.submit()">
        <option value="">Toutes les branches</option>
        <?php foreach ($branches as $branch): ?>
          <option value="<?php echo htmlspecialchars($branch['id']); ?>" <?php echo ($selectedBranchId == $branch['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($branch['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <!-- Galerie d'images -->
  <div class="row g-4 mb-5">
    <?php if (!empty($galleryImages)): ?>
        <?php foreach ($galleryImages as $image): ?>
            <div class="col-6 col-md-4">
                <img src="../assets/uploads/galleries/<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['alt_text']); ?>" class="rounded object-fit-cover w-100 gallery-img">
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center col-12">Aucune image disponible pour cette sélection.</p>
    <?php endif; ?>
  </div>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?> 