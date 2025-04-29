<?php
// sync_product.php
require_once 'db_connection.php';
require_once 'facebook_sdk_init.php'; // Initialize Facebook SDK

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? null;

if(!$productId) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

try {
    // Get product details
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Get settings
    $settings = $db->query("SELECT * FROM social_media_settings")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);
    
    // Get connected accounts
    $accounts = $db->query("SELECT * FROM social_media_accounts WHERE is_active = 1")->fetchAll();
    
    $results = [];
    
    foreach($accounts as $account) {
        $platform = $account['platform'];
        
        // Check if auto sync is enabled for this platform
        if(isset($settings[$platform]['auto_sync_products']) && $settings[$platform]['auto_sync_products']) {
            try {
                $fb = new \Facebook\Facebook([
                  'app_id' => FB_APP_ID,
                  'app_secret' => FB_APP_SECRET,
                  'default_graph_version' => 'v18.0',
                ]);
                
                // Prepare product data for Facebook/Instagram
                $productData = [
                    'retailer_id' => 'PROD_' . $product['id'],
                    'name' => $product['name'],
                    'description' => $product['description'] ?? $product['name'],
                    'price' => $product['price'] * 100, // in cents
                    'currency' => 'USD', // Change as needed
                    'image_url' => 'https://yourdomain.com/images/' . $product['main_image'],
                    'url' => 'https://yourdomain.com/product.php?id=' . $product['id'],
                    'visibility' => 'published',
                    'brand' => 'Your Brand', // Get from brands table
                    'category' => 'YOUR_CATEGORY', // Map your category to FB category
                    'inventory' => $product['stock_limit'] > 0 ? $product['stock_limit'] : 100
                ];
                
                // Check if product already exists on this platform
                $mappingStmt = $db->prepare("
                    SELECT platform_product_id FROM social_media_product_mapping 
                    WHERE product_id = ? AND platform = ?
                ");
                $mappingStmt->execute([$productId, $platform]);
                $mapping = $mappingStmt->fetch();
                
                if($mapping) {
                    // Update existing product
                    $response = $fb->post('/' . $mapping['platform_product_id'], $productData, $account['access_token']);
                } else {
                    // Create new product
                    $response = $fb->post('/' . $account['account_id'] . '/products', $productData, $account['access_token']);
                    $graphNode = $response->getGraphNode();
                    
                    // Save mapping
                    $insertStmt = $db->prepare("
                        INSERT INTO social_media_product_mapping 
                        (product_id, platform, platform_product_id, posted_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $insertStmt->execute([
                        $productId,
                        $platform,
                        $graphNode['id']
                    ]);
                }
                
                // If auto-post is enabled, create a post
                if(isset($settings[$platform]['auto_post_products']) && $settings[$platform]['auto_post_products']) {
                    $postData = [
                        'message' => "New product: " . $product['name'] . " - " . $product['description'],
                        'link' => 'https://yourdomain.com/product.php?id=' . $product['id'],
                        'published' => true
                    ];
                    
                    $fb->post('/' . $account['account_id'] . '/feed', $postData, $account['access_token']);
                }
                
                $results[$platform] = 'success';
            } catch(\Facebook\Exceptions\FacebookResponseException $e) {
                $results[$platform] = 'error: ' . $e->getMessage();
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                $results[$platform] = 'error: ' . $e->getMessage();
            }
        }
    }
    
    echo json_encode(['success' => true, 'results' => $results]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}