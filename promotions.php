<?php
session_start();
$page_title = 'Promotions';
require_once __DIR__ . '/includes/header.php';

// Verify database connection
if (!isset($conn)) {
    die("Database connection not established");
}

// Set consistent timezone
$conn->exec("SET time_zone = '+00:00'"); // UTC
date_default_timezone_set('UTC');

// Get current time
$current_time = date('Y-m-d H:i:s');

// Main query using positional parameters
$sql = "SELECT p.* FROM promotions p
        WHERE p.is_active = 1 
        AND p.start_date <= ? 
        AND p.expiry_date >= ?
        ORDER BY p.start_date DESC";

$promotions = [];
try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $current_time, PDO::PARAM_STR);
    $stmt->bindParam(2, $current_time, PDO::PARAM_STR);
    $stmt->execute();
    $promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Get promotion items if promotions exist
if (!empty($promotions)) {
    foreach ($promotions as &$promotion) {
        $items = $conn->prepare("
            SELECT pr.*, pi.use_general_discount
            FROM promotion_items pi
            JOIN products pr ON pi.product_id = pr.id
            WHERE pi.promotion_id = ?
        ");
        $items->execute([$promotion['id']]);
        $promotion['items'] = $items->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($promotion['items'] as &$item) {
            if ($item['use_general_discount'] && !empty($promotion['all_discount_percentage'])) {
                $item['discount_percentage'] = $promotion['all_discount_percentage'];
                $item['discounted_price'] = $item['price'] * (1 - $item['discount_percentage'] / 100);
            }
        }
    }
    unset($promotion, $item);
}
?>
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
        <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
  </ol>
</nav>
            <h2 class="text-center mb-4 fw-bold">Current Promotions</h2>
            <p class="text-center text-muted">Check out our limited-time offers and save big on your favorite products!</p>
        </div>
    </div>

    <?php if (empty($promotions)): ?>
        <div class="no-promotions">
            <i class="fas fa-tags"></i>
            <h3>No Active Promotions Right Now</h3>
            <p class="text-muted">Check back later for exciting offers!</p>
            <a href="shop.php" class="btn btn-primary mt-3">Browse Products</a>
        </div>
    <?php else: ?>
        <?php foreach ($promotions as $promotion): 
            $start_date = new DateTime($promotion['start_date']);
            $expiry_date = new DateTime($promotion['expiry_date']);
            $now = new DateTime();
            $time_left = $now->diff($expiry_date);
            
            $days_left = $time_left->d;
            $hours_left = $time_left->h;
            $minutes_left = $time_left->i;
            
            $time_left_str = '';
            if ($days_left > 0) {
                $time_left_str .= $days_left . ' day' . ($days_left > 1 ? 's' : '') . ' ';
            }
            if ($hours_left > 0) {
                $time_left_str .= $hours_left . ' hour' . ($hours_left > 1 ? 's' : '') . ' ';
            }
            $time_left_str .= $minutes_left . ' minute' . ($minutes_left > 1 ? 's' : '');
        ?>
        <div class="promotion-card">
            <div class="promotion-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="promotion-title mb-3"><?php echo htmlspecialchars($promotion['name']); ?></h2>
                        <div class="promotion-time">
                        <span class="countdown mb-2"><i class="fas fa-calendar-alt me-2"></i>
                            <?php echo $start_date->format('M d, Y H:i'); ?> - <?php echo $expiry_date->format('M d, Y H:i'); ?></span>
                            <span class="countdown"><i class="fas fa-clock me-1"></i> Ends in <?php echo $time_left_str; ?></span>
                        </div>
                    </div>
                    <?php if (!empty($promotion['all_discount_percentage'])): ?>
                        <div class="text-end">
                            <div class="fs-1 fw-bold"><?php echo $promotion['all_discount_percentage']; ?>% OFF</div>
                            <div>on all items below</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="p-4">
                <?php if (!empty($promotion['description'])): ?>
                    <div class="mb-4"><?php echo nl2br(htmlspecialchars($promotion['description'])); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($promotion['items'])): ?>
                    <h4 class="mb-4">Products in this promotion:</h4>
                    
                    <div class="row">
                        <?php foreach ($promotion['items'] as $item): ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="product-card p-3">
                              
                                
                                <div class="text-center mb-3">
                                <a href="product_details.php?id=<?php echo $item['id']; ?>">
                                    <img src="admin/images/<?php echo htmlspecialchars($item['main_image'] ?? 'images/placeholder-product.jpg'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="img-fluid product-img"></a>
                                </div>
                                <a href="product_details.php?id=<?php echo $item['id']; ?>" class="product-name">
                                <h3 class="product-name"><?php echo htmlspecialchars($item['name']); ?></h3></a>
                                




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
    $stmt->execute([$item['id']]);
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
        } ?>
        <small class="text-muted">(<?=  $totalReviews ?? 0 ?>)</small>
       
    </div>



                                <div class="product-price mt-2">
                                    <?php if (!empty($item['discount_percentage'])): ?>
                                        <div class="original-price"><?php echo htmlspecialchars( $default_currency); echo number_format($item['price'], 2); ?></div>
                                        <div class="discounted-price"><?php echo htmlspecialchars( $default_currency); echo number_format($item['discounted_price'], 2); ?></div>
                                    <?php else: ?>
                                        <div class="fw-bold"><?php echo htmlspecialchars( $default_currency); echo number_format($item['price'], 2); ?></div>
                                    <?php endif; ?>
                                </div>
                                




                                <div class="mt-3">
                                  
                                
                                <?php if ($item['stock_limit'] == 0): ?>
                    <p class="stock-status out-of-stock text-danger">Out of Stock</p>
                <?php else: ?>
                    <p class="stock-status in-stock text-success">In Stock (<?php echo $item['stock_limit']; ?> available)</p>
                <?php endif; ?>
                <?php if ($item['stock_limit'] > 0): ?>
                    <a class="ico" onclick="addToCart(<?= $item['id'] ?>)"><i class="fa fa-shopping-cart"></i></a>
                    <a class="ico" onclick="addToWishlist(<?= $item['id'] ?>)"><i class="fas fa-heart wishlist-icon"></i></a>
                <?php else: ?>
                    <span class="ico disabled"><i class="fa fa-shopping-cart"></i></span>
                    <span class="ico disabled"><i class="fas fa-heart wishlist-icon"></i></span>
                <?php endif; ?>
               
                         <a href="product_details.php?id=<?= $item['id'] ?>" class="ico"><i class="fa-solid fa-eye"></i></a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">No products currently available in this promotion.</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/includes/footer.php';
?>
    
    <!-- Bootstrap Bundle with Popper -->
      <!-- Bootstrap 5 JS and dependencies -->
 <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    // Countdown timer update
// Countdown timer update
function updateCountdowns() {
    $('.countdown').each(function() {
        // Check if element exists and has text
        if (!$(this).length || !$(this).text()) return;
        
        const text = $(this).text().trim();
        if (text.startsWith('Ends in')) {
            const timeStr = text.replace('Ends in ', '').trim();
            const parts = timeStr.split(' ');
            
            let totalMinutes = 0;
            for (let i = 0; i < parts.length; i += 2) {
                const value = parseInt(parts[i]);
                const unit = parts[i+1];
                
                if (!value || !unit) continue;
                
                if (unit.includes('day')) {
                    totalMinutes += value * 24 * 60;
                } else if (unit.includes('hour')) {
                    totalMinutes += value * 60;
                } else if (unit.includes('minute')) {
                    totalMinutes += value;
                }
            }
            
            // Decrease by 1 minute
            totalMinutes -= 1;
            
            if (totalMinutes <= 0) {
                $(this).text('Ended');
                return;
            }
            
            // Convert back to days/hours/minutes
            const days = Math.floor(totalMinutes / (24 * 60));
            const remainingHours = totalMinutes % (24 * 60);
            const hours = Math.floor(remainingHours / 60);
            const minutes = remainingHours % 60;
            
            let newTimeStr = 'Ends in ';
            if (days > 0) {
                newTimeStr += days + ' day' + (days > 1 ? 's ' : ' ');
            }
            if (hours > 0) {
                newTimeStr += hours + ' hour' + (hours > 1 ? 's ' : ' ');
            }
            newTimeStr += minutes + ' minute' + (minutes > 1 ? 's' : '');
            
            $(this).text(newTimeStr);
        }
    });
}

// Only set interval if countdown elements exist
if ($('.countdown').length) {
    updateCountdowns(); // Run immediately
    setInterval(updateCountdowns, 60000); // Then every minute
}
    </script>
    <script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>