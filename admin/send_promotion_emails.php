<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Load required files
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
require 'db.php'; // Uses existing connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Start output buffering
ob_start();

try {
    // Validate promotion ID
    if (!isset($_POST['promotion_id']) || !is_numeric($_POST['promotion_id'])) {
        throw new Exception("Invalid promotion ID received");
    }
    
    $promotion_id = (int)$_POST['promotion_id'];
    
    // Get store info from admin table
    $stmt = $conn->query("SELECT * FROM admin LIMIT 1");
    $store_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get promotion details
    $stmt = $conn->prepare("SELECT * FROM promotions WHERE id = ?");
    $stmt->execute([$promotion_id]);
    $promotion = $stmt->fetch();
    
    if (!$promotion) {
        throw new Exception("No promotion found with ID: $promotion_id");
    }

    // Get recipient emails
    $emails = [];
    $sources = [
        "SELECT DISTINCT email FROM abandoned_carts WHERE email IS NOT NULL",
        "SELECT email FROM users"
    ];
    
    foreach ($sources as $query) {
        $stmt = $conn->query($query);
        while ($row = $stmt->fetch()) {
            $email = trim($row['email']);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }
    }
    
    $emails = array_unique($emails);
    $total_emails = count($emails);

    if ($total_emails === 0) {
        throw new Exception("No valid email addresses found");
    }

    // Configure PHPMailer
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.hostinger.com';
$mail->SMTPAuth = true;
$mail->Username = 'promotions@advancedpromedia.net';
$mail->Password = 'Myweb2025@';
$mail->SMTPSecure = 'ssl';
$mail->Port = 465;
$mail->CharSet = 'UTF-8';
$mail->isHTML(true);

$mail->setFrom('promotions@advancedpromedia.net', 'Advanced Promedia');
$mail->addReplyTo('info@advancedpromedia.com', 'Customer Support');
$mail->Sender = 'promotions@advancedpromedia.net'; // Envelope sender
$mail->addCustomHeader('X-Originating-IP: ' . $_SERVER['SERVER_ADDR']);
$mail->addCustomHeader('Precedence: bulk');
$mail->addCustomHeader('List-Unsubscribe: <mailto:unsubscribe@advancedpromedia.net>');

// Add debug output
$mail->SMTPDebug = 3; // Maximum debug output
$mail->Debugoutput = function($str, $level) {
    file_put_contents('smtp_debug.log', date('Y-m-d H:i:s')." - $level - $str\n", FILE_APPEND);
    echo "SMTP: $str\n";
};
    // Build HTML email
   $email_body = '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>'.htmlspecialchars($promotion['name']).'</title>
    <style type="text/css">
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f8f8; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 0.9em; color: #777; }
        a { color: #0066cc; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>'.htmlspecialchars($promotion['name']).'</h2>
        </div>
        <div class="content">
         <p>Dear Customer</p>
         <p>🎉 Big Savings Alert! Our Exclusive Offer is Here! 🎉</p>
            '.nl2br(htmlspecialchars($promotion['description'])).'
            <p>No special codes needed—just shop and save! This offer is <strong>Valid until:</strong> '.date('F j, Y', strtotime($promotion['expiry_date'])).'</p>
            <p>so don’t miss your chance to grab amazing deals!</p>
            <p><a href="https://' . $_SERVER['HTTP_HOST'] . '/promotions.php" class="button">View Our Promotions</a></p>
                
        </div>
        <div class="footer">
            <p><img src="https://'.$_SERVER['HTTP_HOST'].'/admin/'.htmlspecialchars($store_info['business_logo']).'" alt="'.htmlspecialchars($store_info['store_name']).'" style="max-width:150px;"></p>
            <p>'.htmlspecialchars($store_info['store_name']).'</p>
            <p>Phone: '.htmlspecialchars($store_info['store_phone']).'</p>
            <p>Email: <a href="mailto:'.htmlspecialchars($store_info['store_email']).'">'.htmlspecialchars($store_info['store_email']).'</a></p>
            <p>'.htmlspecialchars($store_info['store_address']).', '.htmlspecialchars($store_info['store_city']).', '.htmlspecialchars($store_info['store_country']).'</p>
        </div>
    </div>
</body>
</html>';
    // Send emails
    $success_count = 0;
    foreach ($emails as $email) {
        try {
            
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid email skipped: $email");
            continue;
        }
            $mail->clearAddresses();
            $mail->addAddress($email);
            $mail->Subject = $promotion['name'];
            $mail->Body = $email_body;
            $mail->AltBody = strip_tags(str_replace("<br>", "\n", $email_body));
            
            if ($mail->send()) {
                $success_count++;
            }
        } catch (Exception $e) {
    error_log("Email sending failed completely: " . $e->getMessage());
    $_SESSION['email_result'] = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => ob_get_clean()
    ];
    // Store failed emails for review
    file_put_contents('failed_emails_'.date('Y-m-d').'.log', implode("\n", $emails));
}
        // Small delay every 20 emails
        if ($success_count % 20 === 0) {
            sleep(1);
        }
    }

    // Update promotion status
    $conn->prepare("UPDATE promotions SET email_sent=1, email_sent_at=NOW() WHERE id=?")
         ->execute([$promotion_id]);

    $_SESSION['email_result'] = [
        'success' => $success_count > 0,
        'count' => $success_count,
        'message' => $success_count > 0 
            ? "Sent to $success_count recipients" 
            : "Failed to send emails"
    ];

} catch (Exception $e) {
    $_SESSION['email_result'] = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ];
}

header("Location: promotion.php?action=edit&id=$promotion_id");
exit();