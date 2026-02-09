<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$leadId = $input['id'] ?? null;
$newStatus = $input['status'] ?? null;

if (!$leadId || !$newStatus) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $db = getDbConnection();
    $stmt = $db->prepare("UPDATE leads SET status_kanban = ? WHERE id = ?");
    $stmt->execute([$newStatus, $leadId]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
