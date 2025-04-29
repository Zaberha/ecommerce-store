<?php
include 'db.php';

$product_id = $_GET['id'] ?? 0; // Get product ID from the request

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// Ensure price is a number
if ($product) {
    $product['price'] = (float)$product['price']; // Convert price to a float
}

// Return product details as JSON
header('Content-Type: application/json');
echo json_encode($product);
?>