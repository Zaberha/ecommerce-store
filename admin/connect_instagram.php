<?php
// connect_instagram.php
require_once __DIR__ . '/config.php';

// Since Instagram Business accounts are managed through Facebook API,
// we'll use the same Facebook SDK but request Instagram permissions

$fb = initFacebookSDK();

$helper = $fb->getRedirectLoginHelper();

// Special permissions needed for Instagram Business accounts
$permissions = [
    'instagram_basic',
    'instagram_content_publish',
    'pages_show_list',       // Needed to access connected Instagram accounts
    'pages_read_engagement'  // Needed for page access
];

// Add Instagram specific permissions to the redirect URL
$loginUrl = $helper->getLoginUrl(FB_REDIRECT_URI, $permissions);

// Store state in session for CSRF protection
$_SESSION['fb_state'] = $helper->getPersistentDataHandler()->get('state');

header('Location: ' . $loginUrl);