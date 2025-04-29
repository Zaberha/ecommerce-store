<?php
session_start(); // Start the session

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection

$host = 'localhost';
$dbname = 'u547298449_ecommerce';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Return a JSON error if the database connection fails
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Check if this is an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($is_ajax) {
    // Get the raw POST data
    $raw_data = file_get_contents('php://input');
    $data = json_decode($raw_data, true);

    // Debug: Log the raw POST data
    error_log("Raw POST data: " . print_r($raw_data, true));

    // Debug: Log the decoded JSON data
    error_log("Decoded JSON data: " . print_r($data, true));

    // Extract cart data
    $cart_items = $data['cart'] ?? [];

    // Debug: Log the received cart data
    error_log("Received cart data: " . print_r($cart_items, true));

    // Fetch product details for cart items
    $cart_products = [];
    if (!empty($cart_items)) {
        $product_ids = array_keys($cart_items);

        // Debug: Log the product IDs
        error_log("Product IDs: " . print_r($product_ids, true));

        // Fetch products from the database
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $query = "SELECT * FROM products WHERE id IN ($placeholders)";
        error_log("Executing query: " . $query); // Log the query for debugging
        $stmt = $conn->prepare($query);
        $stmt->execute($product_ids);
        $fetched_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug: Log the fetched products
        error_log("Fetched products: " . print_r($fetched_products, true));

        // Ensure correct order and prevent duplication
        foreach ($product_ids as $id) {
            foreach ($fetched_products as $product) {
                if ($product['id'] == $id) {
                    $product['quantity'] = $cart_items[$id] ?? 0;
                    $cart_products[] = $product;
                    break;
                }
            }
        }
    }

    // Return the product details as JSON
    header('Content-Type: application/json');
    echo json_encode($cart_products);
    exit;
} else {
    // If not an AJAX request, return an empty array
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}
?>