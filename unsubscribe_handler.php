<?php
require_once __DIR__ . '/db.php';

// Set JSON content type
header('Content-Type: application/json');

// Get the email from the query string
$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'No email address provided'
    ]);
    exit;
}

try {
    // Remove the email from the abandoned_carts table
    $stmt = $conn->prepare("DELETE FROM abandoned_carts WHERE email = :email");
    $stmt->execute(['email' => $email]);
    
    // Optional: Add to unsubscribe list to prevent future emails
    $stmt = $conn->prepare("INSERT INTO unsubscribed_emails (email, unsubscribed_at) 
                           VALUES (:email, NOW()) 
                           ON DUPLICATE KEY UPDATE unsubscribed_at = NOW()");
    $stmt->execute(['email' => $email]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Successfully unsubscribed'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}