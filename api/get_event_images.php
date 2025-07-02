<?php
// api/get_event_images.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$pdo = getPDOConnection();

$branchId = $_GET['branch_id'] ?? null;

$sqlImages = "SELECT image_path, alt_text FROM event_images";
$params = [];

if ($branchId && is_numeric($branchId)) {
    $sqlImages .= " WHERE branch_id = :branch_id";
    $params[':branch_id'] = $branchId;
}

$sqlImages .= " ORDER BY display_order ASC LIMIT 4";

try {
    $stmt = $pdo->prepare($sqlImages);
    $stmt->execute($params);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($images);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    error_log("API error in get_event_images.php: " . $e->getMessage());
}
?> 