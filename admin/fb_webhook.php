<?php
// fb_webhook.php
require_once 'config.php';
require_once 'db_connection.php';

// Verify webhook
$challenge = $_GET['hub_challenge'] ?? '';
$verify_token = $_GET['hub_verify_token'] ?? '';

if ($verify_token === FB_WEBHOOK_VERIFY_TOKEN) {
    echo $challenge;
    exit;
}

// Process webhook
$input = json_decode(file_get_contents('php://input'), true);

if(isset($input['entry'])) {
    foreach($input['entry'] as $entry) {
        if(isset($entry['changes'])) {
            foreach($entry['changes'] as $change) {
                if($change['field'] === 'commerce_orders') {
                    $orderId = $change['value']['id'];
                    $accountId = $change['value']['account_id'];
                    
                    // Check if we've already processed this order
                    $stmt = $db->prepare("
                        SELECT id FROM social_media_orders 
                        WHERE platform_order_id = ? AND platform = 'facebook'
                    ");
                    $stmt->execute([$orderId]);
                    
                    if($stmt->rowCount() == 0) {
                        // Get order details from Facebook
                        $fb = new \Facebook\Facebook([
                          'app_id' => FB_APP_ID,
                          'app_secret' => FB_APP_SECRET,
                          'default_graph_version' => 'v18.0',
                        ]);
                        
                        try {
                            $response = $fb->get('/' . $orderId . '?fields=id,buyer_details,shipping_address,created_time,items{retailer_id,quantity,price_per_unit}', $account['access_token']);
                            $orderData = $response->getGraphNode()->asArray();
                            
                            // Create order in your system
                            $db->beginTransaction();
                            
                            // Insert into orders table
                            $stmt = $db->prepare("
                                INSERT INTO orders 
                                (user_id, total_amount, discount, payment_method, order_status, 
                                delivery_charges, tax_amount, grand_total, method, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            
                            // You might need to create a user or use a generic user for social media orders
                            $userId = 0; // Or get/create user based on buyer_details
                            
                            $totalAmount = 0;
                            foreach($orderData['items'] as $item) {
                                $totalAmount += ($item['price_per_unit'] / 100) * $item['quantity'];
                            }
                            
                            $stmt->execute([
                                $userId,
                                $totalAmount,
                                0,
                                'visa card', // Or detect from order
                                'pending',
                                0, // delivery charges
                                0, // tax amount
                                $totalAmount,
                                'Facebook Shop',
                                date('Y-m-d H:i:s', strtotime($orderData['created_time']))
                            ]);
                            
                            $orderId = $db->lastInsertId();
                            
                            // Insert order items
                            foreach($orderData['items'] as $item) {
                                // Get product ID from retailer_id (PROD_123)
                                $productId = str_replace('PROD_', '', $item['retailer_id']);
                                
                                $stmt = $db->prepare("
                                    INSERT INTO order_items 
                                    (order_id, product_id, quantity, price) 
                                    VALUES (?, ?, ?, ?)
                                ");
                                
                                $stmt->execute([
                                    $orderId,
                                    $productId,
                                    $item['quantity'],
                                    $item['price_per_unit'] / 100
                                ]);
                            }
                            
                            // Record the social media order
                            $stmt = $db->prepare("
                                INSERT INTO social_media_orders 
                                (order_id, platform, platform_order_id) 
                                VALUES (?, 'facebook', ?)
                            ");
                            
                            $stmt->execute([$orderId, $orderData['id']]);
                            
                            $db->commit();
                            
                        } catch(Exception $e) {
                            $db->rollBack();
                            error_log("Error processing Facebook order: " . $e->getMessage());
                        }
                    }
                }
            }
        }
    }
}