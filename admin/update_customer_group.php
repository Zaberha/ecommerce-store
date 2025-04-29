<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

require_once __DIR__ . '/../db.php';

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$group_id = isset($_POST['group_id']) ? ($_POST['group_id'] === '' ? null : intval($_POST['group_id'])) : null;

try {
    $stmt = $conn->prepare("UPDATE users SET group_id = ? WHERE id = ?");
    $stmt->execute([$group_id, $user_id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}