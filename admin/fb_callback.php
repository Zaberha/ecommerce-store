<?php
require_once __DIR__ . '/config.php';

$fb = initFacebookSDK();
$helper = $fb->getRedirectLoginHelper();

try {
    // Get access token
    $accessToken = $helper->getAccessToken(FB_REDIRECT_URI);
    
    if (!isset($accessToken)) {
        throw new Exception('Failed to get access token');
    }

    // Get long-lived token (60 days)
    $oAuth2Client = $fb->getOAuth2Client();
    $longLivedToken = $oAuth2Client->getLongLivedAccessToken((string)$accessToken);
    $tokenMetadata = $oAuth2Client->debugToken($longLivedToken);
    $tokenMetadata->validateAppId(FB_APP_ID);
    $tokenMetadata->validateExpiration();

    // Get user pages (including connected Instagram accounts)
    $pagesResponse = $fb->get('/me/accounts?fields=instagram_business_account,access_token,name,id', $longLivedToken);
    $pages = $pagesResponse->getGraphEdge()->asArray();

    foreach ($pages as $page) {
        // Save Facebook page connection
        $stmt = $conn->prepare("
            INSERT INTO social_media_accounts 
            (platform, account_id, account_name, access_token, expires_at, is_active) 
            VALUES (?, ?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE 
            access_token = VALUES(access_token),
            expires_at = VALUES(expires_at),
            is_active = 1
        ");
        
        $expiresAt = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 60); // 60 days
        $stmt->execute([
            'facebook',
            $page['id'],
            $page['name'],
            $page['access_token'],
            $expiresAt
        ]);

        // Check for connected Instagram account
        if (isset($page['instagram_business_account'])) {
            try {
                $igResponse = $fb->get(
                    '/' . $page['instagram_business_account']['id'] . '?fields=username,name,profile_picture_url', 
                    $page['access_token']
                );
                $igAccount = $igResponse->getGraphNode()->asArray();
                
                // Save Instagram account connection
                $stmt = $conn->prepare("
                    INSERT INTO social_media_accounts 
                    (platform, account_id, account_name, access_token, expires_at, is_active) 
                    VALUES (?, ?, ?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE 
                    access_token = VALUES(access_token),
                    expires_at = VALUES(expires_at),
                    is_active = 1
                ");
                
                $stmt->execute([
                    'instagram',
                    $page['instagram_business_account']['id'],
                    $igAccount['username'],
                    $page['access_token'], // Use page token for Instagram API calls
                    $expiresAt
                ]);
                
            } catch(Exception $e) {
                error_log("Instagram connection error: " . $e->getMessage());
                continue;
            }
        }
    }

    // Redirect back with success message
    $_SESSION['fb_access_token'] = (string)$longLivedToken;
    header('Location: social_media.php?success=1');
    exit();

} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // Graph API error
    $error = 'Graph API Error: ' . $e->getMessage();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // SDK error
    $error = 'Facebook SDK Error: ' . $e->getMessage();
} catch(Exception $e) {
    // Other errors
    $error = 'Error: ' . $e->getMessage();
}

// If we got here, there was an error
header('Location: social_media.php?error=' . urlencode($error ?? 'Unknown error'));
exit();