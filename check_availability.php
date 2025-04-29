<?php
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';
    
    if (empty($field) || empty($value)) {
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }
    
    // Validate which field we're checking
    $allowed_fields = ['username', 'email', 'phone'];
    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['error' => 'Invalid field']);
        exit;
    }
    
    // Check if the value exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE $field = ?");
    $stmt->execute([$value]);
    $exists = $stmt->fetch();
    
    echo json_encode(['available' => !$exists]);
    exit;
}

echo json_encode(['error' => 'Invalid request method']);