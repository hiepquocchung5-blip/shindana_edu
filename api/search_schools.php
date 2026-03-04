<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// 1. Get Search Parameters
$region = isset($_GET['region']) ? $_GET['region'] : 'all';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

// 2. Build Query
$sql = "SELECT * FROM japan_schools WHERE 1=1";
$params = [];

if ($region !== 'all') {
    $sql .= " AND region = ?";
    $params[] = $region;
}

if ($type !== 'all') {
    $sql .= " AND type = ?";
    $params[] = $type;
}

$sql .= " ORDER BY created_at DESC";

// 3. Execute & Return JSON
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $schools = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $schools]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>