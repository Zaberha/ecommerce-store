<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}


// Check permissions and verify request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

// Get input data
$alert_id = $_POST['alert_id'] ?? null;
$action = $_POST['action'] ?? '';

// Validate inputs
if (!$alert_id || !in_array($action, ['mark_sent', 'mark_acknowledged'])) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    $conn->beginTransaction();
    
    // Get current alert status
    $stmt = $conn->prepare("SELECT status FROM inventory_alerts WHERE id = ?");
    $stmt->execute([$alert_id]);
    $alert = $stmt->fetch();
    
    if (!$alert) {
        throw new Exception("Alert not found");
    }
    
    // Validate status transition
    $current_status = $alert['status'];
    $new_status = '';
    
    if ($action === 'mark_sent' && $current_status === 'pending') {
        $new_status = 'sent';
    } elseif ($action === 'mark_acknowledged' && $current_status === 'sent') {
        $new_status = 'acknowledged';
    } else {
        throw new Exception("Invalid status transition");
    }
    
    // Update alert status
    $stmt = $conn->prepare("UPDATE inventory_alerts SET status = ?, sent_at = NOW() WHERE id = ?");
    $stmt->execute([$new_status, $alert_id]);
    
    // Log this action
    $stmt = $conn->prepare("
        INSERT INTO alert_logs 
        (alert_id, action, performed_by, old_status, new_status) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $alert_id,
        $action,
        $_SESSION['user_id'] ?? 0,
        $current_status,
        $new_status
    ]);
    
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Alert status updated successfully',
        'new_status' => $new_status
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode([
        'success' => false,
        'message' => 'Error processing alert: ' . $e->getMessage()
    ]);
}