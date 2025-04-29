<?php
// connect_facebook.php
require_once 'config.php'; // Your configuration file with app credentials

$fb = new \Facebook\Facebook([
  'app_id' => FB_APP_ID,
  'app_secret' => FB_APP_SECRET,
  'default_graph_version' => 'v18.0',
]);

$helper = $fb->getRedirectLoginHelper();

$permissions = [
    'pages_show_list',
    'pages_read_engagement',
    'pages_manage_posts',
    'pages_manage_metadata',
    'instagram_basic',
    'instagram_content_publish',
    'catalog_management',
    'business_management'
];

$loginUrl = $helper->getLoginUrl('https://yourdomain.com/fb_callback.php', $permissions);

header('Location: ' . $loginUrl);