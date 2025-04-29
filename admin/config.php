<?php
session_start();
// config.php - Facebook & Instagram Integration Configuration

// Load database configuration
require_once 'db.php';

// Facebook App Configuration
define('FB_APP_ID', 'your_facebook_app_id');
define('FB_APP_SECRET', 'your_facebook_app_secret');
define('FB_REDIRECT_URI', 'https://yourdomain.com/fb_callback.php');
define('FB_WEBHOOK_VERIFY_TOKEN', 'your_webhook_verification_token');

// Instagram Configuration (handled through Facebook API)
define('INSTAGRAM_BUSINESS_ACCOUNT_ID', 'your_instagram_business_account_id');

// E-commerce Site Configuration
define('SITE_URL', 'https://yourdomain.com');
define('PRODUCT_IMAGE_BASE_URL', 'https://yourdomain.com/images/products/');
define('DEFAULT_CURRENCY', 'USD');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone - Set to your actual timezone
date_default_timezone_set('Asia/Riyadh');

// Manual Facebook SDK Load
$facebookAutoload ='Facebook/autoload.php';

// Verify Facebook SDK exists before loading
if (!file_exists($facebookAutoload)) {
    die('<h2>Facebook SDK Not Found</h2>
        <p>Please download the Facebook PHP SDK and place it in the <code>/admin/Facebook</code> directory.</p>
        <p>Required files:
        <ul>
            <li>autoload.php</li>
            <li>Facebook.php</li>
            <li>Exceptions/FacebookResponseException.php</li>
            <li>Exceptions/FacebookSDKException.php</li>
        </ul>
        <p>Download from: <a href="https://github.com/facebook/php-graph-sdk" target="_blank">GitHub Repository</a></p>');
}

require_once $facebookAutoload;

// Initialize Facebook SDK
function initFacebookSDK() {
    return new \Facebook\Facebook([
        'app_id' => FB_APP_ID,
        'app_secret' => FB_APP_SECRET,
        'default_graph_version' => 'v18.0',
        'persistent_data_handler' => 'session'
    ]);
}

// Helper function to get active social media accounts
function getSocialMediaAccounts($platform = null) {
    global $conn;
    
    $query = "SELECT * FROM social_media_accounts WHERE is_active = 1";
    $params = [];
    
    if ($platform) {
        $query .= " AND platform = ?";
        $params[] = $platform;
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}