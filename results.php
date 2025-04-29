<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Search Results';
$current_page = 'results';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/db.php'; // Your PDO connection

// Get and sanitize search query
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
$is_voice_search = isset($_GET['voice']); // Flag for voice searches

// Initialize results
$results = [];
$suggestions = [];

if (!empty($query)) {
    try {
        // Clean the query for SQL
        $search_terms = preg_replace('/[^\w\s\-]/', '', $query);
        $keywords = explode(' ', $search_terms);
        
        // Build the SQL query dynamically
        $where_clauses = [];
        $params = [];
        
        foreach ($keywords as $i => $term) {
            if (strlen($term) >= 2) { // Ignore single characters
                $param = ":term{$i}";
                $where_clauses[] = "(name LIKE {$param} OR description LIKE {$param} OR product_code LIKE {$param})";
                $params[$param] = "%{$term}%";
            }
        }
        
        if (!empty($where_clauses)) {
            $sql = "SELECT *
                    FROM products 
                    WHERE " . implode(' AND ', $where_clauses) . "
                    ORDER BY 
                        CASE 
                            WHEN name LIKE :exact THEN 0 
                            WHEN name LIKE :start THEN 1 
                            ELSE 2 
                        END,
                        name ASC
                    LIMIT 50";
            
            // Add priority matching parameters
            $params[':exact'] = $search_terms;
            $params[':start'] = $search_terms . '%';
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get suggestions if no results
            if (empty($results) && count($keywords) > 0) {
                $stmt = $conn->prepare("
                    SELECT DISTINCT name 
                    FROM products 
                    WHERE name LIKE :suggestion
                    LIMIT 5
                ");
                $stmt->execute([':suggestion' => '%' . $keywords[0] . '%']);
                $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        $error = "Sorry, we encountered a search error. Please try again.";
    }
}
?>



<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Search Results</li>
        </ol>
    </nav>

    <?php if ($is_voice_search): ?>
        <div class="voice-search-notice alert alert-info">
            <i class="fas fa-microphone"></i> You searched by voice for: "<strong><?= $query ?></strong>"
        </div>
    <?php endif; ?>

    <h2 class="mb-4">Results for "<?= $query ?>"</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif (empty($results)): ?>
        <div class="text-muted">
            <p>No products found for "<strong><?= $query ?></strong>"</p>
            
            <?php if (!empty($suggestions)): ?>
                <hr>
                <p>Try these similar products:</p>
                <ul class="list-unstyled">
                    <?php foreach ($suggestions as $suggestion): ?>
                        <li>
                            <a href="results.php?query=<?= urlencode($suggestion) ?>">
                                <?= htmlspecialchars($suggestion) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($results as $product): ?>
                <div class="col-md-3 mb-3">
                 <div class="card h-100">
                     <!-- Product Image (Clickable) -->
                     <a href="product_details.php?id=<?php echo $product['id']; ?>">
                     <div style="position: relative;">
                         <img src="admin/images/<?php echo htmlspecialchars($product['main_image']); ?>" 
                              alt="<?php echo htmlspecialchars($product['name']); ?>" 
                              class="product-image">
                              <?php if ($product['is_offer'] == 1 && $product['discount_percentage'] > 0): ?>
                                 <div class="discount-badge">
                                 <?php echo htmlspecialchars($product['discount_percentage'], ENT_QUOTES, 'UTF-8'); ?>% OFF
                     </div>
                 <?php endif; ?>
             </div>
                     </a>
                         <div class="card-body">     
                     <!-- Product Name (Clickable) -->
                     <a href="product_details.php?id=<?php echo $product['id']; ?>" class="product-name">
                         <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                     </a>


<?php
// Fetch average rating directly from database
$averageRating = 0;
$totalReviews = 0;
try {
 $stmt = $conn->prepare("SELECT 
                        AVG(stars) as average_rating,
                        COUNT(*) as total_reviews
                        FROM reviews 
                        WHERE product_id = ?");
 $stmt->execute([$product['id']]);
 $ratingData = $stmt->fetch(PDO::FETCH_ASSOC);
 
 $averageRating = round($ratingData['average_rating'] ?? 0, 1);
 $totalReviews = $ratingData['total_reviews'] ?? 0;
} catch (PDOException $e) {
 error_log("Error calculating average rating: " . $e->getMessage());
}
?>
 <div class="stars" style="--rating: <?= $averageRating ?>;">
     <?php
     // Display 5 stars with partial filling
     for ($i = 1; $i <= 5; $i++) {
         if ($i <= floor($averageRating)) {
             echo '<i class="fas fa-star text-warning"></i>';
         } elseif ($i - 0.5 <= $averageRating) {
             echo '<i class="fas fa-star-half-alt text-warning"></i>';
         } else {
             echo '<i class="far fa-star text-warning"></i>';
         }
     }
     ?>
      <small class="text-muted">(<?=  $totalReviews ?? 0 ?>)</small>
 </div>
 



                     
                     <!-- Product Price -->
                     <p><?php if(!empty($product['discount_percentage'])) {?><span class="original-price"><?php echo htmlspecialchars( $default_currency); echo number_format($product['price'], 2);} ?></span>
                     <span class="discounted-price"><?php echo htmlspecialchars( $default_currency); echo number_format($product['price']*(1-($product['discount_percentage']/100)), 2); ?></span></p>
                    <!-- Add to wislist -->
   
                    <?php if ($product['stock_limit'] == 0): ?>
                 <p class="stock-status out-of-stock text-danger">Out of Stock</p>
             <?php else: ?>
                 <p class="stock-status in-stock text-success">In Stock (<?php echo $product['stock_limit']; ?> available)</p>
             <?php endif; ?>
               

             <?php if ($product['stock_limit'] > 0): ?>
                 <a class="ico" onclick="addToCart(<?= $product['id'] ?>)"><i class="fa fa-shopping-cart"></i></a>
                 <a class="ico" onclick="addToWishlist(<?= $product['id'] ?>)"><i class="fas fa-heart wishlist-icon"></i></a>
             <?php else: ?>
                 <span class="ico disabled"><i class="fa fa-shopping-cart"></i></span>
                 <span class="ico disabled"><i class="fas fa-heart wishlist-icon"></i></span>
             <?php endif; ?>

           
                         <a href="product_details.php?id=<?= $product['id'] ?>" class="ico"><i class="fa-solid fa-eye"></i></a>
                         </div>
                         </div>
                     </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>