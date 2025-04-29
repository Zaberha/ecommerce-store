<?php
// logout.php
require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If we have cart/wishlist data in POST (from sync)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'sync_user_data.php';
    exit;
}

// Normal logout flow
if (isset($_SESSION['user_id'])) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// Redirect to homepage
header('Location: index.php');
exit;