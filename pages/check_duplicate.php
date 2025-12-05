<?php
// check_duplicate.php 
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$type = $_POST['type'] ?? '';
$value = trim($_POST['value'] ?? '');

if (!in_array($type, ['email', 'username']) || !$value) {
    echo json_encode(['exists' => false]);
    exit();
}

$column = ($type === 'email') ? 'email' : 'username';
$stmt = $pdo->prepare("SELECT 1 FROM users WHERE $column = ? LIMIT 1");
$stmt->execute([$value]);
$exists = (bool) $stmt->fetch();

echo json_encode(['exists' => $exists]);
?>