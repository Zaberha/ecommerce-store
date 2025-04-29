<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// File upload directory
$upload_dir = 'images/';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file uploads
    $main_image = !empty($_FILES['main_image']['name']) ? $upload_dir . basename($_FILES['main_image']['name']) : $_POST['existing_main_image'];
    if (!empty($_FILES['main_image']['name'])) move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image);

    $image2 = !empty($_FILES['image2']['name']) ? $upload_dir . basename($_FILES['image2']['name']) : $_POST['existing_image2'];
    if (!empty($_FILES['image2']['name'])) move_uploaded_file($_FILES['image2']['tmp_name'], $image2);

    $image3 = !empty($_FILES['image3']['name']) ? $upload_dir . basename($_FILES['image3']['name']) : $_POST['existing_image3'];
    if (!empty($_FILES['image3']['name'])) move_uploaded_file($_FILES['image3']['tmp_name'], $image3);

    $image4 = !empty($_FILES['image4']['name']) ? $upload_dir . basename($_FILES['image4']['name']) : $_POST['existing_image4'];
    if (!empty($_FILES['image4']['name'])) move_uploaded_file($_FILES['image4']['tmp_name'], $image4);

    // Collect form data
    $id = $_POST['id'];
    $name = $_POST['name'];
    $overview = $_POST['overview'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $discount_percentage = $_POST['discount_percentage'];
    $product_code = $_POST['product_code'];
    $minimum_order = $_POST['minimum_order'];
    $max_order = $_POST['max_order'];
    $stock_limit = $_POST['stock_limit'];
    $min_stock = $_POST['min_stock'];
    $delivery_rate = $_POST['delivery_rate'];
    $delivery_duration = $_POST['delivery_duration'];
    $cost = $_POST['cost'];
    $sort_order = $_POST['sort_order'];
    $brand_id = $_POST['brand_id'];
    $supplier_id = $_POST['supplier_id'];
    $weight = $_POST['weight'];
    $volume = $_POST['volume'];
    $international_code = $_POST['international_code'];
    $category_id = $_POST['category_id'];
    $is_offer = isset($_POST['is_offer']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    $free_shipping = isset($_POST['free_shipping']) ? 1 : 0;
    $return_allowed = isset($_POST['return_allowed']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $affiliate_link = $_POST['affiliate_link'];
    $origin_country = $_POST['origin_country'];
    
    // Update product in database
    $stmt = $conn->prepare("
        UPDATE products SET
            name = :name, description = :description, overview = :overview, price = :price, discount_percentage = :discount_percentage,
            product_code = :product_code, minimum_order = :minimum_order, max_order = :max_order, stock_limit = :stock_limit, min_stock = :min_stock,
            cost = :cost, sort_order = :sort_order, brand_id = :brand_id, weight = :weight, international_code = :international_code, volume = :volume, 
            delivery_rate = :delivery_rate, delivery_duration = :delivery_duration, main_image = :main_image, 
            image2 = :image2, image3 = :image3, image4 = :image4, category_id = :category_id, is_offer = :is_offer, 
            active = :active, free_shipping = :free_shipping, return_allowed = :return_allowed, is_new = :is_new, 
            affiliate_link = :affiliate_link, supplier_id = :supplier_id, origin_country = :origin_country
        WHERE id = :id
    ");
    
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':overview' => $overview,
        ':description' => $description,
        ':price' => $price,
        ':discount_percentage' => $discount_percentage,
        ':product_code' => $product_code,
        ':minimum_order' => $minimum_order,
        ':max_order' => $max_order,
        ':stock_limit' => $stock_limit,
        ':min_stock' => $min_stock,
        ':cost' => $cost,
        ':sort_order' => $sort_order,
        ':brand_id' => $brand_id,
        ':weight' => $weight,
        ':international_code' => $international_code,
        ':volume' => $volume,
        ':delivery_rate' => $delivery_rate,
        ':delivery_duration' => $delivery_duration,
        ':main_image' => $main_image,
        ':image2' => $image2,
        ':image3' => $image3,
        ':image4' => $image4,
        ':category_id' => $category_id,
        ':is_offer' => $is_offer,
        ':active' => $active,
        ':free_shipping' => $free_shipping,
        ':return_allowed' => $return_allowed,
        ':is_new' => $is_new,
        ':affiliate_link' => $affiliate_link,
        ':supplier_id' => $supplier_id,
        ':origin_country' => $origin_country
    ]);

    $_SESSION['message'] = 'Product updated successfully';
    $_SESSION['message_type'] = 'success';
    header("Location: products.php");
    exit();
}

// Fetch product data for editing
$id = $_GET['id'];
$product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch(PDO::FETCH_ASSOC);

// Fetch all categories, brands, suppliers
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$brands = $conn->query("SELECT * FROM brands")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $conn->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Edit Product';
$current_page = 'Edit Products';

?>
<?php

// Fetch admin settings
$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
$stmt->execute();
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);
$default_currency = $admin_settings['default_currency'];
?>
<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $page_title; ?> - Admin Panel</title>
        
        <!-- Bootstrap 5 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Dynamic Style - Local Style -->
            <link rel="stylesheet" href="css/dynamic-styles.php">
            <link rel="stylesheet" href="css/styles.css">
        
        <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
       
        <!-- Datepicker CSS -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <!-- DataTables CSS -->
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
            <script>
        // JavaScript to toggle submenus
                document.addEventListener("DOMContentLoaded", function () {
                const submenuToggles = document.querySelectorAll(".submenu-toggle");

                    submenuToggles.forEach((toggle) => {
                        toggle.addEventListener("click", function () {
                            const submenu = this.nextElementSibling;
                            submenu.style.display = submenu.style.display === "block" ? "none" : "block";
                            this.classList.toggle("active");
                        });
                    });
                });
            </script>
        </head>
<body>
    <!-- Sidebar Toggle -->
    <button class="btn btn-link d-md-none rounded-circle me-3 position-fixed top-0 start-0 mt-2 mb-2 ms-2 z-3" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay"></div>

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand navbar-light bg-white top-navbar shadow mb-4">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit <?php echo $product['name']; ?></li>
                        </ol>
                    </nav>
                </div>
                
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-fw"></i> <?php echo htmlspecialchars($_SESSION['employee_full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-fw"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="privileges.php"><i class="fas fa-cog fa-fw"></i> Privileges</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</a></li>
                        </ul>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
                <a href="products.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Products
                </a>
            </div>

            <!-- Message Alert -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Edit Product Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-second">Product Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="edit_product.php" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product Code</label>
                                    <input type="text" class="form-control" name="product_code" value="<?php echo htmlspecialchars($product['product_code']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">International Code</label>
                                    <input type="text" class="form-control" name="international_code" value="<?php echo htmlspecialchars($product['international_code']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Product Name*</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Overview</label>
                                    <textarea class="form-control" name="overview" rows="3"><?php echo htmlspecialchars($product['overview']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category*</label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Brand</label>
                                    <select class="form-select" name="brand_id">
                                        <option value="">Select Brand</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?php echo $brand['id']; ?>" <?php echo $brand['id'] == $product['brand_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($brand['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Supplier</label>
                                    <select class="form-select" name="supplier_id">
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?php echo $supplier['id']; ?>" <?php echo $supplier['id'] == $product['supplier_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($supplier['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Origin Country*</label>
                                    <input type="text" class="form-control" name="origin_country" value="<?php echo htmlspecialchars($product['origin_country']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" name="sort_order" value="<?php echo $product['sort_order']; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Price*</label>
                                    <input type="number" class="form-control" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="is_offer" value="1" <?php echo $product['is_offer'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Is Offer</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Discount Percentage</label>
                                    <input type="number" class="form-control" name="discount_percentage" value="<?php echo $product['discount_percentage']; ?>" step="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cost</label>
                                    <input type="number" class="form-control" name="cost" value="<?php echo $product['cost']; ?>" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Weight (kg)</label>
                                    <input type="number" class="form-control" name="weight" value="<?php echo $product['weight']; ?>" step="0.1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dimensions (LxWxH cm)</label>
                                    <input type="text" class="form-control" name="volume" value="<?php echo htmlspecialchars($product['volume']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Freight Rate</label>
                                    <input type="text" class="form-control" name="delivery_rate" value="<?php echo htmlspecialchars($product['delivery_rate']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Delivery Duration (days)</label>
                                    <input type="text" class="form-control" name="delivery_duration" value="<?php echo htmlspecialchars($product['delivery_duration']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Minimum Order</label>
                                    <input type="number" class="form-control" name="minimum_order" value="<?php echo $product['minimum_order']; ?>" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Max Order</label>
                                    <input type="number" class="form-control" name="max_order" value="<?php echo $product['max_order']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Minimum Stock</label>
                                    <input type="number" class="form-control" name="min_stock" value="<?php echo $product['min_stock']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Available Quantity</label>
                                    <input type="number" class="form-control" name="stock_limit" value="<?php echo $product['stock_limit']; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="active" value="1" <?php echo $product['active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Visible</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="free_shipping" value="1" <?php echo $product['free_shipping'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Free Shipping</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="return_allowed" value="1" <?php echo $product['return_allowed'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Return Allowed</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="is_new" value="1" <?php echo $product['is_new'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">New</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Affiliate Link</label>
                                    <input type="text" class="form-control" name="affiliate_link" value="<?php echo htmlspecialchars($product['affiliate_link']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Main Image</label>
                                    <?php if ($product['main_image']): ?>
                                        <div class="mb-2">
                                            <img src="images/<?php echo $product['main_image']; ?>" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="main_image" accept="image/*">
                                    <input type="hidden" name="existing_main_image" value="<?php echo $product['main_image']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Additional Image 1</label>
                                    <?php if ($product['image2']): ?>
                                        <div class="mb-2">
                                            <img src="images/<?php echo $product['image2']; ?>" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="image2" accept="image/*">
                                    <input type="hidden" name="existing_image2" value="<?php echo $product['image2']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Additional Image 2</label>
                                    <?php if ($product['image3']): ?>
                                        <div class="mb-2">
                                            <img src="images/<?php echo $product['image3']; ?>" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="image3" accept="image/*">
                                    <input type="hidden" name="existing_image3" value="<?php echo $product['image3']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Additional Image 3</label>
                                    <?php if ($product['image4']): ?>
                                        <div class="mb-2">
                                            <img src="images/<?php echo $product['image4']; ?>" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="image4" accept="image/*">
                                    <input type="hidden" name="existing_image4" value="<?php echo $product['image4']; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <a href="products.php" class="btn btn-primary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dropzone for file uploads -->
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize sidebar functionality
        initSidebar();
    });

    function initSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        if (!sidebarToggle || !sidebar || !mainContent) return;

        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }

        function toggleSidebar() {
            const isOpen = !sidebar.classList.contains('active');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
            sidebarToggle.setAttribute('aria-expanded', isOpen);
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    }
    </script>
     <script>
        // JavaScript to toggle discount_percentage input
        document.addEventListener("DOMContentLoaded", function () {
            const isOfferCheckbox = document.querySelector("input[name='is_offer']");
            const discountPercentageInput = document.querySelector("input[name='discount_percentage']");

            isOfferCheckbox.addEventListener("change", function () {
                discountPercentageInput.disabled = !this.checked;
            });

            // Initialize the state on page load
            discountPercentageInput.disabled = !isOfferCheckbox.checked;
        });
    </script>
</body>
</html>