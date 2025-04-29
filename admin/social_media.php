<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Get current settings
$stmt = $conn->query("SELECT * FROM social_media_settings");
$settings = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

// Get connected accounts
$accounts = $conn->query("SELECT * FROM social_media_accounts WHERE is_active = 1")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Integration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Social Media Integration</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Connected Accounts</h5>
            </div>
            <div class="card-body">
                <?php if(empty($accounts)): ?>
                    <div class="alert alert-info">No accounts connected yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>Account Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($accounts as $account): ?>
                                <tr>
                                    <td><?= ucfirst($account['platform']) ?></td>
                                    <td><?= htmlspecialchars($account['account_name']) ?></td>
                                    <td>
                                        <span class="badge bg-success">Connected</span>
                                        <?php if($account['expires_at'] && strtotime($account['expires_at']) < time()): ?>
                                            <span class="badge bg-warning">Token Expired</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger disconnect-btn" data-id="<?= $account['id'] ?>">Disconnect</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex gap-2 mt-3">
                    <a href="connect_facebook.php" class="btn btn-primary">Connect Facebook</a>
                    <a href="connect_instagram.php" class="btn btn-primary">Connect Instagram</a>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Integration Settings</h5>
            </div>
            <div class="card-body">
                <form id="integrationSettingsForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6>Facebook Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="facebookAutoPost" name="facebook[auto_post_products]" 
                                            <?= isset($settings['facebook']['auto_post_products']) && $settings['facebook']['auto_post_products'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="facebookAutoPost">Auto-post new products to Facebook</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="facebookAutoSync" name="facebook[auto_sync_products]" 
                                            <?= isset($settings['facebook']['auto_sync_products']) && $settings['facebook']['auto_sync_products'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="facebookAutoSync">Sync products to Facebook Shop</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="facebookSyncOrders" name="facebook[auto_sync_orders]" 
                                            <?= isset($settings['facebook']['auto_sync_orders']) && $settings['facebook']['auto_sync_orders'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="facebookSyncOrders">Sync orders from Facebook Shop</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6>Instagram Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="instagramAutoPost" name="instagram[auto_post_products]" 
                                            <?= isset($settings['instagram']['auto_post_products']) && $settings['instagram']['auto_post_products'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="instagramAutoPost">Auto-post new products to Instagram</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="instagramAutoSync" name="instagram[auto_sync_products]" 
                                            <?= isset($settings['instagram']['auto_sync_products']) && $settings['instagram']['auto_sync_products'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="instagramAutoSync">Sync products to Instagram Shop</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="instagramSyncOrders" name="instagram[auto_sync_orders]" 
                                            <?= isset($settings['instagram']['auto_sync_orders']) && $settings['instagram']['auto_sync_orders'] ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="instagramSyncOrders">Sync orders from Instagram Shop</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Product Sync Status</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="productSyncTable">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Facebook Status</th>
                                <th>Instagram Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $products = $conn->query("
                                SELECT p.id, p.name, 
                                MAX(CASE WHEN m.platform = 'facebook' THEN m.platform_product_id ELSE NULL END) as facebook_id,
                                MAX(CASE WHEN m.platform = 'instagram' THEN m.platform_product_id ELSE NULL END) as instagram_id
                                FROM products p
                                LEFT JOIN social_media_product_mapping m ON p.id = m.product_id
                                GROUP BY p.id, p.name
                                LIMIT 50
                            ")->fetchAll();
                            
                            foreach($products as $product):
                            ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td>
                                    <?php if($product['facebook_id']): ?>
                                        <span class="badge bg-success">Synced</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Synced</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($product['instagram_id']): ?>
                                        <span class="badge bg-success">Synced</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Synced</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary sync-btn" data-product="<?= $product['id'] ?>">Sync Now</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('integrationSettingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('save_settings.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Settings saved successfully!');
                } else {
                    alert('Error saving settings: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving settings');
            });
        });
        
        document.querySelectorAll('.disconnect-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if(confirm('Are you sure you want to disconnect this account?')) {
                    const accountId = this.getAttribute('data-id');
                    
                    fetch('disconnect_account.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: accountId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            alert('Error disconnecting account: ' + data.message);
                        }
                    });
                }
            });
        });
        
        document.querySelectorAll('.sync-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product');
                
                fetch('sync_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert('Product synced successfully!');
                        location.reload();
                    } else {
                        alert('Error syncing product: ' + data.message);
                    }
                });
            });
        });
    </script>
</body>
</html>