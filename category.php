<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Get category ID from URL
$category_id = $_GET['id'] ?? null;

if (!$category_id) {
    $_SESSION['error'] = 'Category not found';
    header('Location: /');
    exit;
}

// Get category details
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ? AND active = 1");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    $_SESSION['error'] = 'Category not found';
    header('Location: /');
    exit;
}

// Set page title and include header
$page_title = $category['name'];
$current_page = 'category-' . $category_id;
require_once __DIR__ . '/includes/header.php';

// Get sorting and filtering parameters
$sort = $_GET['sort'] ?? 'default';
$filter = $_GET['filter'] ?? 'all';

// Base SQL query
$sql = "SELECT p.*, 
       (SELECT AVG(stars) FROM reviews WHERE product_id = p.id) as average_rating,
       (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as total_reviews,
       (SELECT SUM(quantity) FROM order_items WHERE product_id = p.id) as total_sold
        FROM products p 
        WHERE p.category_id = ? AND p.active = 1";

// Apply filters
switch ($filter) {
    case 'new':
        $sql .= " AND p.is_new = 1";
        break;
    case 'offer':
        $sql .= " AND p.is_offer = 1";
        break;
    case 'bestselling':
        $sql .= " HAVING total_sold > 0";
        break;
    case 'highrated':
        $sql .= " HAVING average_rating >= 3";
        break;
    case 'instock':
        $sql .= " AND p.stock_limit > 0";
        break;
    // 'all' shows all products
}

// Apply sorting
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY p.created_at DESC";
        break;
    case 'bestselling':
        $sql .= " ORDER BY total_sold DESC";
        break;
    case 'toprated':
        $sql .= " ORDER BY average_rating DESC";
        break;
    default:
        $sql .= " ORDER BY p.sort_order ASC, p.name ASC";
        break;
}

$stmt = $conn->prepare($sql);
$stmt->execute([$category_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total products count without filters for display
$stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND active = 1");
$stmt->execute([$category_id]);
$total_products = $stmt->fetchColumn();
?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><?php echo htmlspecialchars($page_title); ?></h2>
        <div class="text-muted"><?php echo count($products); ?> of <?php echo $total_products; ?> products</div>
    </div>

    <!-- Sorting and Filtering Controls -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Sort by: <?php 
                        echo match($sort) {
                            'price_asc' => 'Price: Low to High',
                            'price_desc' => 'Price: High to Low',
                            'name_asc' => 'Name: A to Z',
                            'name_desc' => 'Name: Z to A',
                            'newest' => 'Newest First',
                            'bestselling' => 'Best Selling',
                            'toprated' => 'Top Rated',
                            default => 'Default'
                        };
                    ?>
                </button>
                <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                    <li><a class="dropdown-item <?= $sort === 'default' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&sort=default">Default</a></li>
                    <li><a class="dropdown-item <?= $sort === 'price_asc' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&sort=price_asc">Price: Low to High</a></li>
                    <li><a class="dropdown-item <?= $sort === 'price_desc' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&sort=price_desc">Price: High to Low</a></li>
                    <li><a class="dropdown-item <?= $sort === 'name_asc' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&sort=name_asc">Name: A to Z</a></li>
                    <li><a class="dropdown-item <?= $sort === 'name_desc' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&sort=name_desc">Name: Z to A</a></li>
                    <li><a class="dropdown-item <?= $sort === 'newest' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&sort=newest">Newest First</a></li>
                    <li><a class="dropdown-item <?= $sort === 'bestselling' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&sort=bestselling">Best Selling</a></li>
                    <li><a class="dropdown-item <?= $sort === 'toprated' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&sort=toprated">Top Rated</a></li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Filter: <?php 
                        echo match($filter) {
                            'new' => 'New Arrivals',
                            'offer' => 'Special Offers',
                            'bestselling' => 'Best Selling',
                            'highrated' => 'Highly Rated',
                            'instock' => 'In Stock',
                            default => 'All Products'
                        };
                    ?>
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item <?= $filter === 'all' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&filter=all">All Products</a></li>
                    <li><a class="dropdown-item <?= $filter === 'new' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&filter=new">New Arrivals</a></li>
                    <li><a class="dropdown-item <?= $filter === 'offer' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&filter=offer">Special Offers</a></li>
                    <li><a class="dropdown-item <?= $filter === 'bestselling' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&filter=bestselling">Best Selling</a></li>
                    <li><a class="dropdown-item <?= $filter === 'highrated' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&filter=highrated">Highly Rated</a></li>
                    <li><a class="dropdown-item <?= $filter === 'instock' ? 'active' : '' ?>" href="?id=<?= $category_id ?>&filter=instock">In Stock</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="products">
        <?php if (empty($products)): ?>
            <div class="text-muted">
                No products found matching your criteria. Try adjusting your filters.
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
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

                    <!-- Product Name (Clickable) -->
                    <a href="product_details.php?id=<?php echo $product['id']; ?>" class="product-name">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    </a>
                    
                    <div class="stars" style="--rating: <?= round($product['average_rating'] ?? 0, 1) ?>;">
                        <?php
                        $averageRating = round($product['average_rating'] ?? 0, 1);
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
                        <small class="text-muted">(<?= $product['total_reviews'] ?? 0 ?>)</small>
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

                    <!-- Stock Status -->
                    <?php if ($product['stock_limit'] == 0): ?>
                        <p class="stock-status out-of-stock text-danger">Out of Stock</p>
                    <?php else: ?>
                        <p class="stock-status in-stock text-success">In Stock (<?php echo $product['stock_limit']; ?> available)</p>
                    <?php endif; ?>

                    <!-- Sales Info -->
                    <?php if ($filter === 'bestselling' || $sort === 'bestselling'): ?>
                        <p class="sales-info"><i class="fas fa-chart-line"></i> <?= $product['total_sold'] ?? 0 ?> sold</p>
                    <?php endif; ?>

                    <!-- Add to Cart, Add to Wishlist, and View Links -->
                    <?php if ($product['stock_limit'] > 0): ?>
                        <a class="ico" onclick="addToCart(<?= $product['id'] ?>)"><i class="fa fa-shopping-cart"></i></a>
                        <a class="ico" onclick="addToWishlist(<?= $product['id'] ?>)"><i class="fas fa-heart wishlist-icon"></i></a>
                    <?php else: ?>
                        <span class="ico disabled"><i class="fa fa-shopping-cart"></i></span>
                        <span class="ico disabled"><i class="fas fa-heart wishlist-icon"></i></span>
                    <?php endif; ?>
                    
                    <a href="product_details.php?id=<?= $product['id'] ?>" class="ico"><i class="fa-solid fa-eye"></i></a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
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