<?php
session_start();
require_once __DIR__ . '/db.php';

// Get the raw POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);
$couponCode = $data['couponCode'] ?? '';

if (empty($couponCode)) {
    echo json_encode(['success' => false, 'message' => 'Coupon code is required.']);
    exit;
}

try {
    // 1. First fetch the coupon details to get its ID
    $stmt = $conn->prepare("
        SELECT * FROM discount_codes 
        WHERE code = :code 
        AND active_flag = 1 
        AND expiry_date >= CURDATE()
        LIMIT 1
    ");
    $stmt->execute(['code' => $couponCode]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        echo json_encode([
            'success' => false, 
            'message' => 'The coupon code is invalid or expired.'
        ]);
        exit;
    }

    // 2. Check if this user has already used THIS SPECIFIC coupon
    $stmt = $conn->prepare("
        SELECT COUNT(*) as usage_count 
        FROM orders 
        WHERE user_id = :user_id 
        AND discount_code_id = :coupon_id
    ");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'coupon_id' => $coupon['id']
    ]);
    $usage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usage['usage_count'] > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'You have already used this coupon code.'
        ]);
        exit;
    }

    // 3. Check global usage limits
    if ($coupon['usage_limit'] > 0) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total_usage 
            FROM orders 
            WHERE discount_code_id = :coupon_id
        ");
        $stmt->execute(['coupon_id' => $coupon['id']]);
        $totalUsage = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($totalUsage['total_usage'] >= $coupon['usage_limit']) {
            echo json_encode([
                'success' => false, 
                'message' => 'This coupon has reached its usage limit.'
            ]);
            exit;
        }
    }

    // Coupon is valid
    echo json_encode([
        'success' => true,
        'discount_percentage' => (float) $coupon['discount_percentage'],
        'coupon_code' => $coupon['code'],
        'coupon_id' => $coupon['id']
    ]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while validating the coupon.'
    ]);
}