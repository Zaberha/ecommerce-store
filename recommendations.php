<?php
require_once 'db.php';

function getCustomersAlsoViewed($product_id, $limit = 5) {
    global $conn;
    
    // First try to get co-viewed products
    $stmt = $conn->prepare("
        SELECT pv2.product_id, COUNT(*) as view_count
        FROM product_views pv1
        JOIN product_views pv2 ON pv1.session_id = pv2.session_id
        WHERE pv1.product_id = :product_id 
        AND pv2.product_id != :product_id
        GROUP BY pv2.product_id
        ORDER BY view_count DESC
        LIMIT " . (int)$limit
    );
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If not enough recommendations, fall back to same category
    if (count($recommendations) < $limit) {
        $fallback_limit = $limit - count($recommendations);
        $stmt = $conn->prepare("
            SELECT p.id as product_id
            FROM products p
            JOIN products p2 ON p.category_id = p2.category_id
            WHERE p2.id = :product_id 
            AND p.id != :product_id
            ORDER BY RAND()
            LIMIT " . (int)$fallback_limit
        );
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $fallback_recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $recommendations = array_merge($recommendations, $fallback_recommendations);
    }
    
    return $recommendations;
}

function getProductDetails($product_ids) {
    if (empty($product_ids)) return [];
    
    global $conn;
    
    // Create placeholders for IN clause
    $placeholders = rtrim(str_repeat('?,', count($product_ids)), ',');
    
    $stmt = $conn->prepare("
        SELECT id, name, price, main_image, discount_percentage, stock_limit, is_offer, is_new, 
               (price * (1 - IFNULL(discount_percentage, 0)/100)) as final_price
        FROM products
        WHERE id IN ($placeholders)
        AND active = 1
    ");
    $stmt->execute($product_ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}