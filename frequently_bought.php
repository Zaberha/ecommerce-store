<?php
require_once 'db.php';

function getFrequentlyBoughtTogether($product_id, $limit = 4) {
    global $conn;
    
    // Query to find products frequently bought with the current product
    $query = "
        SELECT 
            oi2.product_id,
            COUNT(*) as frequency,
            p.name,
            p.price,
            p.discount_percentage,
            p.main_image
        FROM 
            order_items oi1
        JOIN 
            order_items oi2 ON oi1.order_id = oi2.order_id
        JOIN 
            products p ON oi2.product_id = p.id
        WHERE 
            oi1.product_id = :product_id
            AND oi2.product_id != :product_id
            AND p.active = 1
        GROUP BY 
            oi2.product_id
        ORDER BY 
            frequency DESC
        LIMIT " . (int)$limit;
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting frequently bought together: " . $e->getMessage());
        return [];
    }
}

function getFallbackRecommendations($product_id, $limit = 4) {
    global $conn;
    
    // Fallback to products from the same category
    $query = "
        SELECT 
            p.id as product_id,
            p.name,
            p.price,
            p.discount_percentage,
            p.main_image
        FROM 
            products p
        JOIN 
            products p2 ON p.category_id = p2.category_id
        WHERE 
            p2.id = :product_id 
            AND p.id != :product_id
            AND p.active = 1
        ORDER BY 
            RAND()
        LIMIT " . (int)$limit;
    
    try {
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting fallback recommendations: " . $e->getMessage());
        return [];
    }
}