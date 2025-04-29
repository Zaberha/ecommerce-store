<?php
$page_title = 'Home';
$current_page = 'home';
require_once __DIR__ . '/includes/header.php';

// check expiry date of offers

try {
    $conn->beginTransaction();

    // 1. Find promotions where CURDATE() is NOT between start_date and expiry_date (expired or not yet started)
    $sql = "
        SELECT id, is_active 
        FROM promotions 
        WHERE CURDATE() > expiry_date
        AND is_active = 1  
    ";
    $stmt = $conn->query($sql);
    $promotions_to_deactivate = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($promotions_to_deactivate as $promotion) {
        $promotion_id = $promotion['id'];

        // 2. Set promotion to inactive
        $update_promotion = $conn->prepare("
            UPDATE promotions 
            SET is_active = 0, updated_at = NOW() 
            WHERE id = ?
        ");
        $update_promotion->execute([$promotion_id]);

        // 3. Get all product_ids from promotion_items (before deleting)
        $get_products = $conn->prepare("
            SELECT product_id 
            FROM promotion_items 
            WHERE promotion_id = ?
        ");
        $get_products->execute([$promotion_id]);
        $product_ids = $get_products->fetchAll(PDO::FETCH_COLUMN);

        // 4. Delete promotion_items (cleanup)
        $delete_items = $conn->prepare("
            DELETE FROM promotion_items 
            WHERE promotion_id = ?
        ");
        $delete_items->execute([$promotion_id]);

        // 5. Reset products (is_offer=0, discount_percentage=NULL)
        if (!empty($product_ids)) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $update_products = $conn->prepare("
                UPDATE products 
                SET is_offer = 0, discount_percentage = NULL 
                WHERE id IN ($placeholders)
            ");
            $update_products->execute($product_ids);
        }
    }

    $conn->commit();
   
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}

// Fetch new products (e.g., products added in the last 30 days)
$new_products = $conn->query("SELECT * FROM products WHERE is_new = 1 AND active = 1 LIMIT 8");
// Fetch products
$stmt = $conn->query("SELECT * FROM products LIMIT 8");
$products = $stmt->fetchAll();



// Fetch special offers
$offers = $conn->query("SELECT p.* 
FROM products p
JOIN promotion_items pi ON p.id = pi.product_id
JOIN promotions pr ON pi.promotion_id = pr.id
WHERE pr.is_active = 1 AND CURDATE() BETWEEN start_date AND expiry_date LIMIT 8");

// Fetch best-selling products (most ordered)
$best_selling = $conn->query("SELECT p.*, COUNT(o.id) as order_count 
    FROM products p 
    LEFT JOIN order_items o ON p.id = o.product_id 
    WHERE p.active = 1
    GROUP BY p.id 
    ORDER BY order_count DESC 
    LIMIT 8");
?>


<header id="title">
    <div class="bgimg-1">    
        <h1 class="outline">Advanced Promedia</h1>       
    </div>
  </header>
  
  <main class="container my-5">
 <h2 class="fw-bold">Offers</h2>
 <div class="row"> 
<div class="container-fluid py-4 bg-light">
  <div class="row align-items-center">
    <div class="col-md-8 text-center text-md-start">
      <p class="mb-3 mb-md-0 fs-5 fw-light">
        Discover amazing deals and limited-time offers.!
      </p>
    </div>
    <div class="col-md-4 text-center text-md-end">
      <a href="promotions.php" class="btn btn-promo-link px-4 py-2">
        <span class="promo-text">All Promotions</span>
        <span class="promo-arrow ms-2">→</span>
      </a>
    </div>
  </div>
</div>

</div>
 <div class="row">
         <?php if ($offers->rowCount() > 0): ?>
             <?php while ($product = $offers->fetch()): ?>
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
                 <?php if ($product['is_new'] == 1): ?>
                               <div class="discount-badge-left">Trending</div>
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
                   
                 
             <?php endwhile; ?>
         <?php else: ?>
            <p>No special offers available.</p>
         <?php endif; ?> 
        
</div>  
 </main>





 
  <!-- Parallax background -->
  <div class="bgimg-2">
  </div>



  <main class="container my-5">
 
 <h2 class="fw-bold"><?= $translations['Trending']; ?></h2>
 <div class="row">
         <?php if ($new_products->rowCount() > 0): ?>
             <?php while ($product = $new_products->fetch()): ?>
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
                 <?php if ($product['is_new'] == 1): ?>
                               <div class="discount-badge-left">Trending</div>
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
                     

                     <?php
// Check for active promotions for this product
$productId = $product['id'];
$promotionCheck = $conn->prepare("
    SELECT pr.all_discount_percentage 
    FROM promotion_items pi
    JOIN promotions pr ON pi.promotion_id = pr.id
    WHERE pi.product_id = :product_id 
    AND pr.is_active = 1 
    AND CURDATE() BETWEEN pr.start_date AND pr.expiry_date
    LIMIT 1
");
$promotionCheck->bindValue(':product_id', $productId, PDO::PARAM_INT);
$promotionCheck->execute();
$activePromotion = $promotionCheck->fetch(PDO::FETCH_ASSOC);

// Display prices based on active promotion
if ($activePromotion && $activePromotion['all_discount_percentage'] > 0): ?>
    <p>
        <span class="original-price">
            <?php echo htmlspecialchars($default_currency); 
            echo number_format($product['price'], 2); ?>
        </span>
        <span class="discounted-price">
            <?php echo htmlspecialchars($default_currency); 
            echo number_format($product['price'] * (1 - ($activePromotion['all_discount_percentage']/100)), 2); ?>
        </span>
    </p>
<?php else: ?>
    <p>
        <span class="regular-price">
            <?php echo htmlspecialchars($default_currency); 
            echo number_format($product['price'], 2); ?>
        </span>
    </p>
<?php endif; ?>



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
                     
                 
             <?php endwhile; ?>
         <?php else: ?>
             <p>No new products available.</p>
         <?php endif; ?>
</div>

 </main>



    
   <!-- Parallax background -->
   <div class="bgimg-3">
   </div>

   <main class="container my-5">
 
 <h2 class="fw-bold">Best Selling</h2>
 <div class="row">
         <?php if ($best_selling->rowCount() > 0): ?>
             <?php while ($product = $best_selling->fetch()): ?>
                 <div class="col-md-3 mb-3">
                 <div class="card h-100">
                     <!-- Product Image (Clickable) -->
                     <a href="product_details.php?id=<?php echo $product['id']; ?>">
                     <div style="position: relative;">
                         <img src="admin/images/<?php echo htmlspecialchars($product['main_image']); ?>" 
                              alt="<?php echo htmlspecialchars($product['name']); ?>" 
                              class="product-image">
                              <?php
// Check for active promotions for this product
$productId = $product['id'];
$promotionCheck = $conn->prepare("
    SELECT pr.all_discount_percentage 
    FROM promotion_items pi
    JOIN promotions pr ON pi.promotion_id = pr.id
    WHERE pi.product_id = :product_id 
    AND pr.is_active = 1 
    AND CURDATE() BETWEEN pr.start_date AND pr.expiry_date
    LIMIT 1
");
$promotionCheck->bindValue(':product_id', $productId, PDO::PARAM_INT);
$promotionCheck->execute();
$activePromotion = $promotionCheck->fetch(PDO::FETCH_ASSOC);

if ($activePromotion && $activePromotion['all_discount_percentage'] > 0): ?>
    <div class="discount-badge">
        <?php echo htmlspecialchars($activePromotion['all_discount_percentage'], ENT_QUOTES, 'UTF-8'); ?>% OFF
    </div>
<?php endif; ?>


<?php if ($product['is_new'] == 1): ?>
                               <div class="discount-badge-left">Trending</div>
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
                     




                     <?php
// Check for active promotions for this product
$productId = $product['id'];
$promotionCheck = $conn->prepare("
    SELECT pr.all_discount_percentage 
    FROM promotion_items pi
    JOIN promotions pr ON pi.promotion_id = pr.id
    WHERE pi.product_id = :product_id 
    AND pr.is_active = 1 
    AND CURDATE() BETWEEN pr.start_date AND pr.expiry_date
    LIMIT 1
");
$promotionCheck->bindValue(':product_id', $productId, PDO::PARAM_INT);
$promotionCheck->execute();
$activePromotion = $promotionCheck->fetch(PDO::FETCH_ASSOC);

// Display prices based on active promotion
if ($activePromotion && $activePromotion['all_discount_percentage'] > 0): ?>
    <p>
        <span class="original-price">
            <?php echo htmlspecialchars($default_currency); 
            echo number_format($product['price'], 2); ?>
        </span>
        <span class="discounted-price">
            <?php echo htmlspecialchars($default_currency); 
            echo number_format($product['price'] * (1 - ($activePromotion['all_discount_percentage']/100)), 2); ?>
        </span>
    </p>
<?php else: ?>
    <p>
        <span class="regular-price">
            <?php echo htmlspecialchars($default_currency); 
            echo number_format($product['price'], 2); ?>
        </span>
    </p>
<?php endif; ?>

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
                     
                 
             <?php endwhile; ?>
         <?php else: ?>
             <p>No best-selling products available.</p>
         <?php endif; ?>
</div>


 </main>

 

<!-- Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>
  
</body>
</html>