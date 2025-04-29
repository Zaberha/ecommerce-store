<?php
// save_settings.php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['employee_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Verify POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Begin transaction
    $conn->beginTransaction();
    
    // Clear existing settings
    $conn->query("DELETE FROM social_media_settings");
    
    // Process Facebook settings
    if (isset($_POST['facebook'])) {
        $stmt = $conn->prepare("
            INSERT INTO social_media_settings 
            (platform, auto_post_products, auto_sync_products, auto_sync_orders) 
            VALUES ('facebook', ?, ?, ?)
        ");
        $stmt->execute([
            !empty($_POST['facebook']['auto_post_products']) ? 1 : 0,
            !empty($_POST['facebook']['auto_sync_products']) ? 1 : 0,
            !empty($_POST['facebook']['auto_sync_orders']) ? 1 : 0
        ]);
    }
    
    // Process Instagram settings
    if (isset($_POST['instagram'])) {
        $stmt = $conn->prepare("
            INSERT INTO social_media_settings 
            (platform, auto_post_products, auto_sync_products, auto_sync_orders) 
            VALUES ('instagram', ?, ?, ?)
        ");
        $stmt->execute([
            !empty($_POST['instagram']['auto_post_products']) ? 1 : 0,
            !empty($_POST['instagram']['auto_sync_products']) ? 1 : 0,
            !empty($_POST['instagram']['auto_sync_orders']) ? 1 : 0
        ]);
    }
    
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
    
} catch(PDOException $e) {
    $conn->rollBack();
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred'
    ]);
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

exit();