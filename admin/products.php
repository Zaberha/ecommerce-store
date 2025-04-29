<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Security: Validate and sanitize inputs
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $delete_id = sanitizeInput($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $delete_id);
    $stmt->execute();
    
    $_SESSION['message'] = 'Product deleted successfully';
    $_SESSION['message_type'] = 'success';
    header("Location: products.php");
    exit();
}

// Pagination
$itemsPerPage = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Fetch total number of products
$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Fetch products with pagination
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, b.name as brand_name, s.name as supplier_name 
                       FROM products p
                       LEFT JOIN categories c ON p.category_id = c.id
                       LEFT JOIN brands b ON p.brand_id = b.id
                       LEFT JOIN suppliers s ON p.supplier_id = s.id
                       LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all categories, brands, suppliers for dropdowns
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$brands = $conn->query("SELECT * FROM brands")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $conn->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Manage Products';
$current_page = 'products';
require_once __DIR__ . '/includes/header.php';
?>

        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Products</h1>
                <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus fa-sm text-white-50"></i> Add New Product
                </button>
            </div>

            <!-- Message Alert -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Products Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-second">Product List</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addProductModal">Add New</a></li>
                            <li><a class="dropdown-item" href="#">Export</a></li>
                            <li>
                                <form method="GET" action="products.php" class="px-3 py-2">
                                    <label for="items_per_page" class="form-label small">Items per page:</label>
                                    <select name="items_per_page" id="items_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="10" <?php echo $itemsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                        <option value="20" <?php echo $itemsPerPage == 20 ? 'selected' : ''; ?>>20</option>
                                        <option value="50" <?php echo $itemsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                    </select>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="productsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['product_code']); ?></td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars( $default_currency); echo number_format($product['price'], 2); ?></td>
                                        <td>
                                            <?php if ($product['discount_percentage'] > 0): ?>
                                                <span class="badge bg-success"><?php echo $product['discount_percentage']; ?>%</span>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo $product['active'] ? 'bg-success' : 'bg-primary'; ?>">
                                                <?php echo $product['active'] ? 'Visible' : 'Hidden'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="products.php?delete_id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to delete this product?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="products.php?page=<?php echo $page - 1; ?>&items_per_page=<?php echo $itemsPerPage; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= ceil($totalProducts / $itemsPerPage); $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="products.php?page=<?php echo $i; ?>&items_per_page=<?php echo $itemsPerPage; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < ceil($totalProducts / $itemsPerPage)): ?>
                                <li class="page-item">
                                    <a class="page-link" href="products.php?page=<?php echo $page + 1; ?>&items_per_page=<?php echo $itemsPerPage; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product Code</label>
                                    <input type="text" class="form-control" name="product_code">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">International Code</label>
                                    <input type="text" class="form-control" name="international_code">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Product Name*</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category*</label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Brand</label>
                                    <select class="form-select" name="brand_id">
                                        <option value="">Select Brand</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Supplier</label>
                                    <select class="form-select" name="supplier_id">
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Origin Country*</label>
                                    <input type="text" class="form-control" name="origin_country" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Overview</label>
                                    <textarea class="form-control" name="overview" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" name="sort_order">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Affiliate Link</label>
                                    <input type="text" class="form-control" name="affiliate_link">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Price*</label>
                                    <input type="number" class="form-control" name="price" step="1" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="is_offer" value="1">
                                    <label class="form-check-label">Is Offer</label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Discount Percentage</label>
                                    <input type="number" class="form-control" name="discount_percentage" step="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cost</label>
                                    <input type="number" class="form-control" name="cost" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Weight (kg)</label>
                                    <input type="number" class="form-control" name="weight" step="0.1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Dimensions (LxWxH cm)</label>
                                    <input type="text" class="form-control" name="volume">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Freight Rate</label>
                                    <input type="text" class="form-control" name="delivery_rate">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Delivery Duration</label>
                                    <input type="text" class="form-control" name="delivery_duration">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Minimum Order</label>
                                    <input type="number" class="form-control" name="minimum_order" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Maximum Order</label>
                                    <input type="number" class="form-control" name="max_order" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Available Quantities</label>
                                    <input type="number" class="form-control" name="stock_limit">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Minimum Stock</label>
                                    <input type="number" class="form-control" name="min_stock">
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="active" value="1" checked>
                                    <label class="form-check-label">Visible</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="free_shipping" value="1">
                                    <label class="form-check-label">Free Shipping</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="return_allowed" value="1">
                                    <label class="form-check-label">Return Allowed</label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="is_new" value="1">
                                    <label class="form-check-label">New</label>
                                </div>
                                
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Main Image*</label>
                                    <input type="file" class="form-control" name="main_image" accept="image/*" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Additional Images</label>
                                    <input type="file" class="form-control" name="image2" accept="image/*">
                                    <input type="file" class="form-control mt-2" name="image3" accept="image/*">
                                    <input type="file" class="form-control mt-2" name="image4" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
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
        
        // Initialize Dropzone for file uploads
        if (typeof Dropzone !== 'undefined') {
            Dropzone.autoDiscover = false;
            // Initialize Dropzone if needed
        }
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