<?php
// api/get_menus.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

$pdo = getPDOConnection();

$branchId = $_GET['branch_id'] ?? null;

$sqlMenus = "SELECT mi.image, mi.name, mi.description, mi.price FROM menu_items mi JOIN menus m ON mi.menu_id = m.id WHERE mi.is_available = TRUE";
$params = [];

if ($branchId && is_numeric($branchId)) {
    $sqlMenus .= " AND m.branch_id = :branch_id";
    $params[':branch_id'] = $branchId;
}

$sqlMenus .= " ORDER BY mi.id LIMIT 4";

try {
    $stmt = $pdo->prepare($sqlMenus);
    $stmt->execute($params);
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($menus);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    error_log("API error in get_menus.php: " . $e->getMessage());
}
?> 