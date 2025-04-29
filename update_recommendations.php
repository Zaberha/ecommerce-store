<?php
require 'db.php';

// Get all active products
$products = $conn->query("SELECT id FROM products WHERE active = 1")->fetchAll(PDO::FETCH_COLUMN);

// Create or update recommendations table
$conn->exec("
    CREATE TABLE IF NOT EXISTS product_recommendations (
        product_id INT PRIMARY KEY,
        recommended_ids TEXT,
        last_updated TIMESTAMP
    )
");

require 'recommendations.php';

foreach ($products as $product_id) {
    $recommendations = getCustomersAlsoViewed($product_id, 10);
    $recommended_ids = implode(',', array_column($recommendations, 'product_id'));
    
    $stmt = $conn->prepare("
        INSERT INTO product_recommendations (product_id, recommended_ids, last_updated)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE recommended_ids = VALUES(recommended_ids), last_updated = VALUES(last_updated)
    ");
    $stmt->execute([$product_id, $recommended_ids]);
}

echo "Recommendations updated successfully!\n";

//corn job setup in bash : 0 3 * * 0 /usr/bin/php /path/to/your/site/update_recommendations.php
?>
