<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to prevent any accidental output
ob_start();

session_start();
require_once __DIR__ . '/db.php';

// Set proper content type header
header('Content-Type: application/json');

// Log file for debugging
$logFile = __DIR__ . '/save_abandoned_cart.log';

try {
    $stmt = $conn->query("SELECT * FROM admin LIMIT 1");
    $store_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get the raw POST data
    $raw_data = file_get_contents('php://input');
    $data = json_decode($raw_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data: " . json_last_error_msg());
    }

    $email = $data['email'] ?? '';
    $cart_data = $data['cart_data'] ?? '';
    $checkExisting = $data['check_existing'] ?? false;

    if (empty($email)) {
        throw new Exception("Email is required.");
    }

    if (empty($cart_data)) {
        $cart_data = 0;
    }

    // Check if email already exists (if requested)
    if ($checkExisting) {
        $checkStmt = $conn->prepare("SELECT email FROM abandoned_carts WHERE email = :email");
        $checkStmt->execute(['email' => $email]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            echo json_encode(['exists' => true]);
            exit;
        }
    }

    // Insert the abandoned cart data into the database
    $stmt = $conn->prepare("INSERT INTO abandoned_carts (email, cart_data) VALUES (:email, :cart_data)");
    $stmt->execute([
        'email' => $email,
        'cart_data' => $cart_data,
    ]);
    if($store_info['loyalty_program_enabled']){
        $loyal='<p>Did you know we have a loyalty program? Earn points with every order and get special discount coupons!</p>
                            <p><a href="https://' . $_SERVER['HTTP_HOST'] . '/loyaltyprogram.php" class="button">Learn About Loyalty Program</a></p>';
        }
        else {$loyal='';}
    // Send welcome email
    if ($store_info) {
        sendWelcomeEmail($email, $store_info,$loyal, $email);
    }

    // Log success
    file_put_contents($logFile, "Email $email was subscribed successfully at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

    // Return a clean success response
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you, Subscribed successfully. Check your email for discount codes, hot offers and much more.'
    ]);
    exit;

} catch (Exception $e) {
    // Log errors
    file_put_contents($logFile, "Error: " . $e->getMessage() . " at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

function sendWelcomeEmail($to, $store_info, $loyal, $email) {
    $subject = "Welcome to " . htmlspecialchars($store_info['store_name']);
    
    $headers = "From: " . htmlspecialchars($store_info['store_name']) . " <" . $store_info['store_email'] . ">\r\n";
    $headers .= "Reply-To: " . $store_info['store_email'] . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $message = '<!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
            .button { 
                display: inline-block; 
                padding: 10px 20px; 
                background-color: #007bff; 
                color: white !important; 
                text-decoration: none; 
                border-radius: 5px; 
                margin: 10px 0;
            }
            .credentials { 
                background-color: #f0f0f0; 
                padding: 15px; 
                border-radius: 5px; 
                margin: 15px 0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Welcome to ' . htmlspecialchars($store_info['store_name']) . '</h1>
            </div>
            
            <div class="content">
                <p>Hi</p>
                
                <p>A warm welcome to ' . htmlspecialchars($store_info['store_name']) . '! Thank you for subscribing to our newsletter.</p>
                
                <p>We shall send you from time to time emails about our latest news, best deals and offers </p>
                 <p>We gladly invite you to register and start your shopping experience with us below:</p>
                <p><a href="https://' . $_SERVER['HTTP_HOST'] . '/register.php" class="button">register here</a></p>
                
                <p>Check out our current offers and promotions:</p>
                <p><a href="https://' . $_SERVER['HTTP_HOST'] . '/promotions.php" class="button">View Our Offers</a></p>
                
                '.$loyal.'
                
                 <p>you may unsubscribe from our newsletter by clicking here !</p>
                <p><a href="https://' . $_SERVER['HTTP_HOST'] . '/unsubscribe.php?email=' . urlencode($email) . '" class="button">Unsubscribe</a></p>
                
            </div>
            
            <div class="footer">
                <p><img src="https://' . $_SERVER['HTTP_HOST'] . '/admin/' . htmlspecialchars($store_info['business_logo']) . '" alt="' . htmlspecialchars($store_info['store_name']) . '" style="max-width: 150px;"></p>
                <p>' . htmlspecialchars($store_info['store_name']) . '</p>
                <p>Phone: ' . htmlspecialchars($store_info['store_phone']) . '</p>
                <p>Email: ' . htmlspecialchars($store_info['store_email']) . '</p>
                <p>' . htmlspecialchars($store_info['store_address']) . ', ' . htmlspecialchars($store_info['store_city']) . ', ' . htmlspecialchars($store_info['store_country']) . '</p>
            </div>
        </div>
    </body>
    </html>';
    
    return mail($to, $subject, $message, $headers);
}
?>