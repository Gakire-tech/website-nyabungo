<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$pdo = getPDOConnection();

// Filtre par nom de branche (optionnel)
$branchName = isset($_GET['branch']) ? trim($_GET['branch']) : null;

// Récupérer les branches
$sql = 'SELECT id, name FROM branches';
$params = [];
if ($branchName) {
    $sql .= ' WHERE name = ?';
    $params[] = $branchName;
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pour chaque branche, récupérer jusqu'à 2 images de galleries
foreach ($branches as &$branch) {
    $sqlImg = 'SELECT image, caption FROM galleries WHERE branch_id = ? AND is_active = 1 ORDER BY display_order ASC, id ASC LIMIT 2';
    $stmtImg = $pdo->prepare($sqlImg);
    $stmtImg->execute([$branch['id']]);
    $branch['images'] = $stmtImg->fetchAll(PDO::FETCH_ASSOC);
}

// Réponse JSON
echo json_encode(['branches' => $branches]); 