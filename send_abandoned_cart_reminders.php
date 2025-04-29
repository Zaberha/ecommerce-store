<?php
require_once __DIR__ . '/db.php';

// Log file path
$logFile = __DIR__ . '/abandoned_cart_reminders.log';

// Fetch abandoned carts older than 1 hour that haven't been reminded
$stmt = $conn->prepare("SELECT * FROM abandoned_carts WHERE created_at < NOW() - INTERVAL 1 HOUR AND reminded = 0");
$stmt->execute();
$abandoned_carts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($abandoned_carts as $cart) {
    $email = $cart['email'];
    $cart_data = json_decode($cart['cart_data'], true);
    $coupon_code = 'SAVE10-' . uniqid(); // Generate a unique coupon code

    // Prepare the email content
    $subject = 'Complete Your Purchase and Get 10% Off!';

    // HTML email template
    $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .coupon { font-size: 18px; font-weight: bold; color: #d9534f; }
                .unsubscribe { font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <h2>Hi there,</h2>
            <p>We noticed you left items in your cart. Use the code below to get <strong>10% off</strong> your order!</p>
            <p class='coupon'>Coupon Code: $coupon_code</p>
            <p><a href='[Your Website URL]'>Click here to continue shopping</a></p>
            <p class='unsubscribe'>
                <a href='[Your Website URL]/unsubscribe.php?email=$email'>Unsubscribe</a> from these reminders.
            </p>
        </body>
        </html>
    ";

    // Email headers for HTML content
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Your Store <noreply@yourstore.com>" . "\r\n";

    // Send the email
    if (mail($email, $subject, $message, $headers)) {
        // Mark the cart as reminded and save the coupon code
        $stmt = $conn->prepare("UPDATE abandoned_carts SET reminded = 1, coupon_code = :coupon_code WHERE id = :id");
        $stmt->execute([
            'id' => $cart['id'],
            'coupon_code' => $coupon_code,
        ]);

        // Log the successful email
        file_put_contents($logFile, "Reminder sent to $email at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        echo "Reminder email sent to $email with coupon code $coupon_code.\n";
    } else {
        // Log the email sending error
        file_put_contents($logFile, "Failed to send reminder to $email at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        echo "Failed to send reminder email to $email.\n";
    }
}
?>