<?php
// sync_user_data.php
require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only proceed if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Get the input data
$input = file_get_contents('php://input');
if (empty($input)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'No data received']));
}

$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid JSON data']));
}

$user_id = $_SESSION['user_id'];
$response = ['success' => true];

try {
    $conn->beginTransaction();
    
    // Process cart data
    if (!empty($data['cart_data'])) {
        $cart_data = json_decode($data['cart_data'], true);
        
        if ($cart_data !== null) {
            // Delete existing cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Insert new items
            $stmt = $conn->prepare("INSERT INTO cart (product_id, quantity, user_id, created_at) 
                                  VALUES (?, ?, ?, NOW())");
            
            foreach ($cart_data as $product_id => $item) {
                if (is_array($item) && isset($item['quantity'])) {
                    $quantity = (int)$item['quantity'];
                    $stmt->execute([$product_id, $quantity, $user_id]);
                }
            }
        }
    }
    
    // Process wishlist data
    if (!empty($data['wishlist_data'])) {
        $wishlist_data = json_decode($data['wishlist_data'], true);
        
        if ($wishlist_data !== null) {
            // Delete existing wishlist
            $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Insert new items
            $stmt = $conn->prepare("INSERT INTO wishlist (product_id, user_id, quantity, created_at) 
                                  VALUES (?, ?, ?, NOW())");
            
            foreach ($wishlist_data as $product_id => $quantity) {
                $quantity = (int)$quantity;
                $stmt->execute([$product_id, $user_id, $quantity]);
            }
        }
    }
    
    $conn->commit();
    
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Sync error: " . $e->getMessage());
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Database error']));
}

// For sendBeacon requests, we don't need to output anything
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('Content-Type: application/json');
    echo json_encode($response);
}