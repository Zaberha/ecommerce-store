<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require 'db.php';

try {
    $query = "SELECT 
                p.id, 
                p.name, 
                p.price, 
                p.main_image,  
                p.discount_percentage,
                COUNT(o.id) as order_count 
              FROM products p 
              LEFT JOIN order_items o ON p.id = o.product_id
              WHERE p.active = 1
              GROUP BY p.id 
              ORDER BY order_count DESC 
              LIMIT 8";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($products)) {
        http_response_code(404);
        echo json_encode(['message' => 'No products found']);
        exit;
    }
    
    // Format the data with proper checks
    $formattedProducts = array_map(function($product) {
        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => number_format((float)$product['price'], 2),
            'image' => !empty($product['main_image']) ? $product['main_image'] : 'images/default-product.jpg',
            'discount_percentage' => $product['discount_percentage'] ?? null
        ];
    }, $products);
    
    echo json_encode($formattedProducts);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}