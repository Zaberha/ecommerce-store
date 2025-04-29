<?php
require_once __DIR__ . '/db.php'; // Your existing connection file

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current product ID safely
$current_product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($current_product_id) {
    // Generate session ID if none exists
    if (empty($_SESSION['session_id'])) {
        $_SESSION['session_id'] = bin2hex(random_bytes(16));
    }
    
    include 'recommendations.php';


    try {
        // Record view in database
        $stmt = $conn->prepare("INSERT INTO product_views (product_id, session_id) VALUES (?, ?)");
        $stmt->execute([$current_product_id, $_SESSION['session_id']]);
        
        // Store in session for immediate use
        $_SESSION['recently_viewed'] = $_SESSION['recently_viewed'] ?? [];
        array_unshift($_SESSION['recently_viewed'], $current_product_id);
        $_SESSION['recently_viewed'] = array_slice(array_unique($_SESSION['recently_viewed']), 0, 10);
    } catch (PDOException $e) {
        // Log error but don't break the page
        error_log("Failed to track product view: " . $e->getMessage());
    }
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;



// Fetch reviews for the current product
$reviews = [];
try {
    $product_id =  $product_id ?? 0; // Make sure you have the product ID
    
    $stmt = $conn->prepare("SELECT r.*, u.username 
                          FROM reviews r 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.active=1 AND r.product_id = ? 
                          ORDER BY r.created_at DESC");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error but don't stop execution
    error_log("Error fetching reviews: " . $e->getMessage());
}



$averageRating = 0;
$totalReviews = 0;

try {
    $stmt = $conn->prepare("SELECT 
                           AVG(stars) as average_rating,
                           COUNT(*) as total_reviews
                           FROM reviews 
                           WHERE product_id = ?");
    $stmt->execute([ $product_id]);
    $ratingData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $averageRating = round($ratingData['average_rating'] ?? 0, 1);
    $totalReviews = $ratingData['total_reviews'] ?? 0;
} catch (PDOException $e) {
    error_log("Error calculating average rating: " . $e->getMessage());
}






// Fetch product details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, b.name as brand_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        LEFT JOIN brands b ON p.brand_id = b.id 
                        WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit();
}
$page_title = $product['name'];
$current_page = 'products';
require_once 'includes/header.php';
// Fetch related products (same brand and same category)
$related_brand_products = [];
$related_category_products = [];

if ($product['brand_id']) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE brand_id = ? AND id != ? AND active = 1 LIMIT 4");
    $stmt->execute([$product['brand_id'], $product_id]);
    $related_brand_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($product['category_id']) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND active = 1 LIMIT 4");
    $stmt->execute([$product['category_id'], $product_id]);
    $related_category_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


?>

<div class="container">
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
    <li class="breadcrumb-item"><a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
  </ol>
</nav>
    <!-- Product Details Row -->
    <div class="row">
        <!-- First Column: Product Images -->
        <div class="col-md-1">
        <div class="row">
                <?php
                $images = [
                    $product['main_image'],
                    $product['image2'],
                    $product['image3'],
                    $product['image4']
                ];

                foreach ($images as $image) {
                    if (!empty($image)) {
                        echo '<div class="col-12 mb-3">
                                <img src="admin/images/' . htmlspecialchars($image) . '" 
                                     alt="Product Image" 
                                     class="img-thumbnail img-fluid thumimage" 
                                     onclick="changeMainImage(this.src)">
                              </div>';
                    }
                }
                ?>
            </div>
</div>
        <div class="col-md-5">
            <!-- Main Image -->
            <div class="text-center mb-3">
                <img id="main-image" src="admin/images/<?php echo htmlspecialchars($product['main_image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid">


            </div>

            <!-- Thumbnail Images -->
        
        </div>

        <!-- Second Column: Product Details -->
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>






            <div class="product-rating mb-3" data-bs-toggle="tooltip" title="<?= $averageRating ?> out of 5 stars">
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
    </div>
    <span class="ms-2">
        <?= $averageRating ?> (<?= $totalReviews ?> reviews)
    </span>
</div>

<!-- Initialize Bootstrap tooltips -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>



            <p class="text-muted">Category: <a href="category.php?id=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></p>
            <p class="text-muted">Brand: <?php echo htmlspecialchars($product['brand_name'] ?? 'N/A'); ?></p>

            <!-- Price and Discount -->
            <p class="h4">




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



            </p>
<h3>Overview</h3>
            <p><?php echo nl2br(htmlspecialchars($product['overview'])); ?></p>
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
                

            <!-- Additional Info -->
            <div class="mt-4">
                <p><strong>Product Code:</strong> <?php echo htmlspecialchars($product['product_code']); ?></p>

            </div>
        </div>


   
    </div>

    <div class="row">
     <!-- Frequently bought together -->
     <div class="col-md-12">
        <!-- Frequently bought together -->


        <div class="frequently-bought-section text-center">
    <h3>Frequently Bought Together</h3>
    <div class="product-bundle">
        <?php
        if ($current_product_id) {
            require_once 'frequently_bought.php';
            
            // Get frequently bought together items
            $frequently_bought = getFrequentlyBoughtTogether($current_product_id);
            
            // If not enough results, use fallback
            if (count($frequently_bought) < 2) {
                $frequently_bought = array_merge(
                    $frequently_bought,
                    getFallbackRecommendations($current_product_id, 4 - count($frequently_bought))
                );
            }
            
            // Display the main product
            $main_product = getProductDetails([$current_product_id])[0] ?? null;
            
            if ($main_product && !empty($frequently_bought)) {
                echo '<div class="bundle-container">';
                
                // Main product
                echo '<div class="bundle-main-product">';
                echo '<img src="admin/images/'.htmlspecialchars($main_product['main_image']).'" alt="'.htmlspecialchars($main_product['name']).'" class="thumbo">';
                echo '<p>'.htmlspecialchars($main_product['name']).'</p>';
                echo '</div>';
                
                // Plus sign
                echo '<div class="bundle-plus align-self-sm-center">+</div>';
                
                // Frequently bought items
                echo '<div class="bundle-items d-flex">';
                foreach (array_slice($frequently_bought, 0, 3) as $freq) {
                    echo '<div class="bundle-item">';
                    echo '<img src="admin/images/'.htmlspecialchars($freq['main_image']).'" alt="'.htmlspecialchars($freq['name']).'" class="thumbo">';
                    echo '<p>'.htmlspecialchars($freq['name']).'</p>';
                    echo '</div>';
                    
                    // Plus sign between items
                    if ($freq !== end($frequently_bought)) {
                        echo '<div class="bundle-plus align-self-sm-center">+</div>';
                    }
                }
                echo '</div>';
                
                // Bundle price and add to cart
                $bundle_price = $main_product['price'] * (1 - ($main_product['discount_percentage'] / 100));
                foreach ($frequently_bought as $freq) {
                    $bundle_price += $freq['price'] * (1 - ($freq['discount_percentage'] / 100));
                }
                
                echo '<div class="bundle-actions">';
                echo '<div class="bundle-price">Bundle Price: $'.number_format($bundle_price, 2).'</div>';
                
                // Create array of all product IDs in the bundle
                $bundle_ids = array_merge(
                    [$current_product_id],
                    array_column($frequently_bought, 'product_id')
                );
                
                echo '<button id="add-bundle-btn" class="btn btn-primary" data-product-ids="'.htmlspecialchars(json_encode($bundle_ids)).'">';
                echo 'Add All to Cart';
                echo '</button>';
                echo '</div>';
                
                echo '</div>'; // Close bundle-container
            }
        }
        ?>
    </div>
</div>


        </div>

</div>


<hr>


<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">
            Description
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features" type="button" role="tab" aria-controls="features" aria-selected="false">
            Features
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">
            Reviews
        </button>
    </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content p-3 border-top-0 border" id="productTabsContent">
    <!-- Description Tab -->
    <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
        <h3>Product Description</h3>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    </div>
    
    <!-- Features Tab -->
    <div class="tab-pane fade" id="features" role="tabpanel" aria-labelledby="features-tab">
        <h3>Product Features</h3>
        <p><strong>Weight (Kg):</strong> <?php echo htmlspecialchars($product['weight']); ?></p>
        <p><strong>Size:</strong> <?php echo htmlspecialchars($product['volume']); ?></p>
        <p><strong>Country of Origin:</strong> <?php echo htmlspecialchars($product['origin_country']); ?></p>
    </div>
    
    <!-- Reviews Tab -->
    <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
        <h3>Customer Reviews</h3>
        
        <!-- Review Button -->
        <div class="mb-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn btn-primary" id="writeReviewBtn">
                    <i class="fas fa-edit me-2"></i>Write a Review
                </button>
            <?php else: ?>
                <button class="btn btn-primary" disabled>
                    <i class="fas fa-edit me-2"></i>Write a Review
                </button>
                <small class="text-muted d-block mt-2">Please <a href="login.php">login</a> to add a review</small>
            <?php endif; ?>
        </div>

        <!-- Review Form (initially hidden) -->
        <div class="card mb-4 d-none" id="reviewForm">
            <div class="card-body">
                <h5 class="card-title">Write Your Review</h5>
                <form id="reviewFormSubmit">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="star-rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?= $i ?>" name="stars" value="<?= $i ?>" required>
                                <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reviewText" class="form-label">Your Review</label>
                        <textarea class="form-control" id="reviewText" name="review_text" rows="3" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Submit Review
                    </button>
                </form>
            </div>
        </div>

        <!-- Reviews List -->
<!-- Reviews List -->
<div id="reviewsList">
    <?php if (empty($reviews)): ?>
        <div class="text-muted">No reviews yet. Be the first to review!</div>
    <?php else: ?>
        <?php foreach ($reviews as $review): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title"><?= htmlspecialchars($review['username']) ?></h5>
                        <div class="text-warning">
                            <?= str_repeat('<i class="fas fa-star"></i>', $review['stars']) ?>
                            <?= str_repeat('<i class="far fa-star"></i>', 5 - $review['stars']) ?>
                        </div>
                    </div>
                    <p class="card-text text-start"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                    <small class="text-muted">
                        <?= date('F j, Y', strtotime($review['created_at'])) ?>
                    </small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
    </div>
</div>



























<!-- Customers Also Viewed 


<div class="also-viewed-section">

    <div class="product-recommendations">-->
        <?php
        if ($current_product_id) {
            require_once 'recommendations.php';
            
            // Get recommendations (try session first, then database)
            $recommended_ids = [];
            
            // From session
            if (!empty($_SESSION['recently_viewed'])) {
                $recommended_ids = array_diff($_SESSION['recently_viewed'], [$current_product_id]);
                $recommended_ids = array_slice($recommended_ids, 0, 8);
            }
            
            // From database if needed
            if (count($recommended_ids) < 3) {
                try {
                    $db_recommendations = getCustomersAlsoViewed($current_product_id, 8);
                    $db_ids = array_column($db_recommendations, 'product_id');
                    $recommended_ids = array_unique(array_merge($recommended_ids, $db_ids));
                    $recommended_ids = array_slice($recommended_ids, 0, 8);
                } catch (Exception $e) {
                    error_log("Recommendation error: " . $e->getMessage());
                }
            }
            
            // Get product details
            $recommended_products = !empty($recommended_ids) ? getProductDetails($recommended_ids) : [];
            
            // Display products
            if (!empty($recommended_products)) {
                foreach ($recommended_products as $also) {
                    $final_price = $also['price'] * (1 - ($also['discount_percentage'] / 100));
                    $discount_badge = $product['discount_percentage'] > 0 
                        ? '<span class="discount-badge">-'.$also['discount_percentage'].'%</span>' 
                        : '';
                    
                   // echo '<div class="recommended-product">
                      //  <a href="product_details.php?id='.$also['id'].'">
                        //    <div class="product-image">
                       //         <img src="admin/images/'.htmlspecialchars($also['main_image']).'" alt="'.htmlspecialchars($also['name']).'" class="thumbo">
                        //        '.$discount_badge.'
                        //    </div>
                        //    <h3>'.htmlspecialchars($also['name']).'</h3>
                        //    <div class="price">';
                    
                    if ($also['discount_percentage'] > 0) {
                   //     echo '<span class="old-price">$'.number_format($also['price'], 2).'</span>';
                    }
                    
                   // echo '<span class="current-price">$'.number_format($final_price, 2).'</span>
                    //        </div>
                    //    </a>
                  //  </div>';
                }
            } else {
               // echo '<p>Check out these other great products!</p>';
                // Fallback to random products
                try {
                    $stmt = $conn->query("SELECT id, name, price, is_offer, stock_limit, main_image, discount_percentage, in_new
                                         FROM products WHERE active = 1 ORDER BY RAND() LIMIT 8");
                    $fallback_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($fallback_products as $also) {
                        $final_price = $also['price'] * (1 - ($also['discount_percentage'] / 100));
                     //   echo '<div class="recommended-product">
                        //    <a href="product_details.php?id='.$also['id'].'">
                           //     <div class="product-image">
                               //     <img src="admin/images/'.htmlspecialchars($also['main_image']).'" alt="'.htmlspecialchars($also['name']).'">
                             //   </div>
                            //    <h3>'.htmlspecialchars($also['name']).'</h3>
                           //     <div class="price">$'.number_format($final_price, 2).'</div>
                          //  </a>
                        //</div>';
                    }
                } catch (PDOException $e) {
                    error_log("Fallback products error: " . $e->getMessage());
                    echo '<p>No recommendations available right now.</p>';
                }
            }
        }
        ?>
   <!-- </div>
</div>-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.product-recommendations');
    if (container) {
        container.style.overflowX = 'auto';
        container.style.whiteSpace = 'nowrap';
        container.style.paddingBottom = '20px';
        
        const items = container.querySelectorAll('.recommended-product');
        items.forEach(item => {
            item.style.display = 'inline-block';
            item.style.width = '200px';
            item.style.marginRight = '15px';
            item.style.verticalAlign = 'top';
            item.style.whiteSpace = 'normal';
        });
    }
});

</script>







    <!-- Related Products Row -->
    <div class="row mt-5">


 <!-- Customers Also Viewed -->
 <?php if (!empty($recommended_products)): ?>
            <div class="col-12">
                <h3>Customers Also Viewed</h3>
                <div class="row">
                    <?php foreach ($recommended_products as $also): ?>
                        <div class="col-md-3 mb-4">
                            <div class="product-card">
                                <!-- Product Image (Clickable) -->
                                <a href="product_details.php?id=<?php echo $also['id']; ?>">
                                    <div style="position: relative;">
                                        <img src="admin/images/<?php echo htmlspecialchars($also['main_image']); ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($also['name']); ?>">
                                             <?php
// Check for active promotions for this product
$productId = $also['id'];
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
<?php if ($also['is_new'] == 1): ?>
                               <div class="discount-badge-left">Trending</div>
                            <?php endif; ?>
                                    </div>
                                </a>
                                <div class="card-body">
                                    <!-- Product Name (Clickable) -->
                                    <a href="product_details.php?id=<?php echo $also['id']; ?>" class="product-name">
                                        <h3><?php echo htmlspecialchars($also['name']); ?></h3>
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
    $stmt->execute([$also['id']]);
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
$productId = $also['id'];
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

                                           
                                            <?php if ($also['stock_limit'] == 0): ?>
                    <p class="stock-status out-of-stock text-danger">Out of Stock</p>
                <?php else: ?>
                    <p class="stock-status in-stock text-success">In Stock (<?php echo $also['stock_limit']; ?> available)</p>
                <?php endif; ?>
                  

                <?php if ($also['stock_limit'] > 0): ?>
                    <a class="ico" onclick="addToCart(<?= $also['id'] ?>)"><i class="fa fa-shopping-cart"></i></a>
                    <a class="ico" onclick="addToWishlist(<?= $also['id'] ?>)"><i class="fas fa-heart wishlist-icon"></i></a>   
                <?php else: ?>
                    <span class="ico disabled"><i class="fa fa-shopping-cart"></i></span>
                    <span class="ico disabled"><i class="fas fa-heart wishlist-icon"></i></span>
                <?php endif; ?>
               
                                           
                            <a href="product_details.php?id=<?= $also['id'] ?>" class="ico"><i class="fa-solid fa-eye"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>



<hr/>
        <!-- Related Products by Brand -->
        <?php if (!empty($related_brand_products)): ?>
            <div class="col-12">
                <h3>Related Products (from <?php echo htmlspecialchars($product['brand_name'] ?? 'N/A'); ?>)</h3>
                <div class="row">
                    <?php foreach ($related_brand_products as $related): ?>
                        <div class="col-md-3 mb-4">
                            <div class="product-card">
                                <!-- Product Image (Clickable) -->
                                <a href="product_details.php?id=<?php echo $related['id']; ?>">
                                    <div style="position: relative;">
                                        <img src="admin/images/<?php echo htmlspecialchars($related['main_image']); ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($related['name']); ?>">
                                             <?php
// Check for active promotions for this product
$productId = $related['id'];
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
<?php if ($related['is_new'] == 1): ?>
                               <div class="discount-badge-left">Trending</div>
                            <?php endif; ?>
                                    </div>
                                </a>
                                <div class="card-body">
                                    <!-- Product Name (Clickable) -->
                                    <a href="product_details.php?id=<?php echo $related['id']; ?>" class="product-name">
                                        <h3><?php echo htmlspecialchars($related['name']); ?></h3>
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
    $stmt->execute([$related['id']]);
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
$productId = $related['id'];
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

                                           
                                            <?php if ($related['stock_limit'] == 0): ?>
                    <p class="stock-status out-of-stock text-danger">Out of Stock</p>
                <?php else: ?>
                    <p class="stock-status in-stock text-success">In Stock (<?php echo $related['stock_limit']; ?> available)</p>
                <?php endif; ?>
                  

                <?php if ($related['stock_limit'] > 0): ?>
                    <a class="ico" onclick="addToCart(<?= $related['id'] ?>)"><i class="fa fa-shopping-cart"></i></a>
                    <a class="ico" onclick="addToWishlist(<?= $related['id'] ?>)"><i class="fas fa-heart wishlist-icon"></i></a>   
                <?php else: ?>
                    <span class="ico disabled"><i class="fa fa-shopping-cart"></i></span>
                    <span class="ico disabled"><i class="fas fa-heart wishlist-icon"></i></span>
                <?php endif; ?>
               
                                           
                            <a href="product_details.php?id=<?= $related['id'] ?>" class="ico"><i class="fa-solid fa-eye"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <hr/>
        <!-- Related Products by Category -->
        <?php if (!empty($related_category_products)): ?>
            <div class="col-12">
                <h3>Related Products (from <?php echo htmlspecialchars($product['category_name']); ?>)</h3>
                <div class="row">
                    <?php foreach ($related_category_products as $related): ?>
                        <div class="col-md-3 mb-4">
                            <div class="product-card">
                                <!-- Product Image (Clickable) -->
                                <a href="product_details.php?id=<?php echo $related['id']; ?>">
                                    <div style="position: relative;">
                                        <img src="admin/images/<?php echo htmlspecialchars($related['main_image']); ?>" 
                                             class="product-image" 
                                             alt="<?php echo htmlspecialchars($related['name']); ?>">
                                             <?php
// Check for active promotions for this product
$productId = $related['id'];
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
<?php if ($related['is_new'] == 1): ?>
                               <div class="discount-badge-left">Trending</div>
                            <?php endif; ?>
                                    </div>
                                </a>
                                <div class="card-body">
                                    <!-- Product Name (Clickable) -->
                                    <a href="product_details.php?id=<?php echo $related['id']; ?>" class="product-name">
                                        <h3><?php echo htmlspecialchars($related['name']); ?></h3>
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
    $stmt->execute([$related['id']]);
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
$productId = $related['id'];
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

                                            <?php if ($related['stock_limit'] == 0): ?>
                    <p class="stock-status out-of-stock text-danger">Out of Stock</p>
                <?php else: ?>
                    <p class="stock-status in-stock text-success">In Stock (<?php echo $related['stock_limit']; ?> available)</p>
                <?php endif; ?>
                  

                <?php if ($related['stock_limit'] > 0): ?>
                    <a class="ico" onclick="addToCart(<?= $related['id'] ?>)"><i class="fa fa-shopping-cart"></i></a>
                    <a class="ico" onclick="addToWishlist(<?= $related['id'] ?>)"><i class="fas fa-heart wishlist-icon"></i></a>
                <?php else: ?>
                    <span class="ico disabled"><i class="fa fa-shopping-cart"></i></span>
                    <span class="ico disabled"><i class="fas fa-heart wishlist-icon"></i></span>
                   
                <?php endif; ?>
               
                                    

                            <a href="product_details.php?id=<?= $related['id'] ?>" class="ico"><i class="fa-solid fa-eye"></i></a>


                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- Footer -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
     <!-- JavaScript for Image Swapping, Quantity Control, and Cart Popup -->
<script>
    // Function to change the main product image
    function changeMainImage(src) {
        document.getElementById('main-image').src = src;
    }

</script>
    <script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>
    



    <script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Toggle review form visibility
    const writeReviewBtn = document.getElementById('writeReviewBtn');
    const reviewForm = document.getElementById('reviewForm');
    
    if (writeReviewBtn && reviewForm) {
        writeReviewBtn.addEventListener('click', function() {
            // Toggle form visibility
            const isHidden = reviewForm.classList.toggle('d-none');
            
            // Update button text
            this.innerHTML = isHidden 
                ? '<i class="fas fa-edit me-2"></i>Write a Review' 
                : '<i class="fas fa-times me-2"></i>Cancel';
        });
    }

    // 2. Star rating interaction
    const starInputs = document.querySelectorAll('.star-rating input');
    starInputs.forEach(input => {
        input.addEventListener('change', function() {
            const rating = parseInt(this.value);
            const labels = this.parentElement.querySelectorAll('label');
            
            labels.forEach((label, index) => {
                const starIcon = (index >= 5 - rating) ? 'fas fa-star' : 'far fa-star';
                const starColor = (index >= 5 - rating) ? '#ffc107' : '#ccc';
                
                label.innerHTML = `<i class="${starIcon}"></i>`;
                label.style.color = starColor;
            });
        });
    });

    // 3. Form submission handling
    const reviewFormSubmit = document.getElementById('reviewFormSubmit');
    if (reviewFormSubmit) {
        reviewFormSubmit.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            if (!submitBtn) return;
            
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            try {
                const formData = new FormData(this);
                const response = await fetch('submit_review.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                if (!data.success) throw new Error(data.error || 'Failed to submit review');
                
                // Success actions
                if (reviewForm) reviewForm.classList.add('d-none');
                if (writeReviewBtn) writeReviewBtn.innerHTML = '<i class="fas fa-edit me-2"></i>Write a Review';
                
                addReviewToDOM(data.review);
                showAlert('success', 'Thank you for your review!');
                this.reset();
                resetStarRating();
                
            } catch (error) {
                console.error('Error:', error);
                showAlert('danger', error.message || 'An error occurred');
            } finally {
                if (submitBtn) {
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.disabled = false;
                }
            }
        });
    }
    
    // Helper functions
    function resetStarRating() {
        const stars = document.querySelectorAll('.star-rating label');
        stars.forEach(star => {
            star.innerHTML = '<i class="far fa-star"></i>';
            star.style.color = '#ccc';
        });
        const checkedInput = document.querySelector('.star-rating input[type="radio"]:checked');
        if (checkedInput) checkedInput.checked = false;
    }
    
    function addReviewToDOM(review) {
        const reviewsList = document.getElementById('reviewsList');
        if (!reviewsList) return;
        
        const noReviewsAlert = reviewsList.querySelector('.alert-info');
        if (noReviewsAlert) noReviewsAlert.remove();
        
        const reviewDate = review.created_at ? new Date(review.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : 'Just now';
        
        const reviewHTML = `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title">${review.username || 'Anonymous'}</h5>
                        <div class="text-warning">
                            ${'<i class="fas fa-star"></i>'.repeat(review.stars || 0)}
                            ${'<i class="far fa-star"></i>'.repeat(5 - (review.stars || 0))}
                        </div>
                    </div>
                    <p class="card-text">${(review.review_text || '').replace(/\n/g, '<br>')}</p>
                    <small class="text-muted">${reviewDate}</small>
                </div>
            </div>
        `;
        
        reviewsList.insertAdjacentHTML('afterbegin', reviewHTML);
    }
    
    function showAlert(type, message) {
        const reviewsTab = document.getElementById('reviews');
        if (!reviewsTab) return;
        
        // Remove existing alerts
        const existingAlerts = reviewsTab.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        reviewsTab.insertBefore(alert, reviewsTab.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
});
</script>



</body>
</html>
