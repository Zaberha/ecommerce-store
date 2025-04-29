<?php
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized - Please login');
    }

    // Validate required fields
    $required = ['review_text', 'stars', 'product_id'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Insert review
    $stmt = $conn->prepare("INSERT INTO reviews 
                          (review_text, user_id, stars, product_id, created_at) 
                          VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([
        $_POST['review_text'],
        $_SESSION['user_id'],
        (int)$_POST['stars'],
        (int)$_POST['product_id']
    ]);

    // Get the new review with username
    $reviewId = $conn->lastInsertId();
    $stmt = $conn->prepare("SELECT r.*, u.username 
                          FROM reviews r 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.id = ?");
    $stmt->execute([$reviewId]);
    $review = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$review) {
        throw new Exception('Failed to fetch review data');
    }

    $response = [
        'success' => true,
        'review' => [
            'username' => $review['username'],
            'review_text' => $review['review_text'],
            'stars' => (int)$review['stars'],
            'created_at' => $review['created_at']
        ]
    ];

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $response['error'] = 'Database error occurred';
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>