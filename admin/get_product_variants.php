<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}


header('Content-Type: application/json');

$product_id = $_GET['product_id'] ?? 0;
$warehouse_id = $_GET['warehouse_id'] ?? null;

try {
    $response = ['variants' => []];
    
    // Get variants with stock for this product
    $query = "
        SELECT v.id, v.option_name, v.option_value, 
               COALESCE(ws.quantity, 0) as quantity
        FROM product_variants v
        LEFT JOIN warehouse_stock ws ON v.id = ws.variant_id 
            AND ws.product_id = ? 
            " . ($warehouse_id ? "AND ws.warehouse_id = ?" : "") . "
        WHERE v.product_id = ?
        HAVING quantity > 0
        ORDER BY v.option_name, v.option_value
    ";
    
    $stmt = $conn->prepare($query);
    
    if ($warehouse_id) {
        $stmt->execute([$product_id, $warehouse_id, $product_id]);
    } else {
        $stmt->execute([$product_id, $product_id]);
    }
    
    $response['variants'] = $stmt->fetchAll();
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}