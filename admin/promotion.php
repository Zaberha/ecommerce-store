<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_promotion'])) {
        // Create new promotion
        $name = $_POST['name'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $expiry_date = $_POST['expiry_date'];
        $all_discount_percentage = !empty($_POST['all_discount_percentage']) ? $_POST['all_discount_percentage'] : null;
        
        $stmt = $conn->prepare("INSERT INTO promotions (name, description, start_date, expiry_date, all_discount_percentage) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $start_date, $expiry_date, $all_discount_percentage]);
        
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Promotion created successfully!'];
        header("Location: promotion.php");
        exit();
    } elseif (isset($_POST['update_promotion'])) {
        // Update existing promotion
        $id = $_POST['promotion_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $start_date = $_POST['start_date'];
        $expiry_date = $_POST['expiry_date'];
        $all_discount_percentage = !empty($_POST['all_discount_percentage']) ? $_POST['all_discount_percentage'] : 1;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Start transaction
        $conn->beginTransaction();
        
        try {
            // Update promotion
            $stmt = $conn->prepare("UPDATE promotions SET name = ?, description = ?, start_date = ?, expiry_date = ?, all_discount_percentage = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $description, $start_date, $expiry_date, $all_discount_percentage, $is_active, $id]);
            
            // Get all product IDs associated with this promotion
            $stmt = $conn->prepare("SELECT product_id FROM promotion_items WHERE promotion_id = ?");
            $stmt->execute([$id]);
            $product_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($product_ids)) {
                if ($is_active == 1) {
                    // If promotion is active, set is_offer=1 and discount_percentage
                    $stmt = $conn->prepare("UPDATE products SET is_offer = 1, discount_percentage = ? WHERE id IN (".implode(',', array_fill(0, count($product_ids), '?')).")");
                    $stmt->execute(array_merge([$all_discount_percentage], $product_ids));
                } else {
                    // If promotion is inactive, set is_offer=0 and discount_percentage=NULL
                    $stmt = $conn->prepare("UPDATE products SET is_offer = 0, discount_percentage = NULL WHERE id IN (".implode(',', array_fill(0, count($product_ids), '?')).")");
                    $stmt->execute($product_ids);
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Promotion and associated products updated successfully!'];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error updating promotion: '.$e->getMessage()];
        }
        
        header("Location: promotion.php");
        exit();
    } elseif (isset($_POST['add_products'])) {
        // Add products to promotion
        $promotion_id = $_POST['promotion_id'];
        $product_ids = $_POST['product_ids'];
        $use_general_discount = isset($_POST['use_general_discount']) ? 1 : 0;
        
        // Get promotion details to check if it has general discount
        $promo_stmt = $conn->prepare("SELECT all_discount_percentage FROM promotions WHERE id = ?");
        $promo_stmt->execute([$promotion_id]);
        $promotion = $promo_stmt->fetch(PDO::FETCH_ASSOC);
        $general_discount = $promotion['all_discount_percentage'];
        
        foreach ($product_ids as $product_id) {
            // Check if product is already in promotion
            $check_stmt = $conn->prepare("SELECT id FROM promotion_items WHERE promotion_id = ? AND product_id = ?");
            $check_stmt->execute([$promotion_id, $product_id]);
            
            if (!$check_stmt->fetch()) {
                // Add to promotion
                $stmt = $conn->prepare("INSERT INTO promotion_items (promotion_id, product_id, use_general_discount) VALUES (?, ?, ?)");
                $stmt->execute([$promotion_id, $product_id, $use_general_discount]);
                
                // Update product's offer status and discount
                $discount_value = ($use_general_discount && $general_discount) ? $general_discount : null;
                $update_product = $conn->prepare("UPDATE products SET is_offer = 1, discount_percentage = ? WHERE id = ?");
                $update_product->execute([$discount_value, $product_id]);
            }
        }
        
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Products added to promotion successfully!'];
        header("Location: promotion.php?action=edit&id=$promotion_id");
        exit();
    } elseif (isset($_POST['remove_product'])) {
        // Remove product from promotion
        $promotion_id = $_POST['promotion_id'];
        $product_id = $_POST['product_id'];
        
        $stmt = $conn->prepare("DELETE FROM promotion_items WHERE promotion_id = ? AND product_id = ?");
        $stmt->execute([$promotion_id, $product_id]);
        
        // Update product's offer status and discount
        $update_product = $conn->prepare("UPDATE products SET is_offer = 0, discount_percentage = NULL WHERE id = ?");
        $update_product->execute([$product_id]);
        
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Product removed from promotion successfully!'];
        header("Location: promotion.php?action=edit&id=$promotion_id");
        exit();
    } elseif (isset($_POST['update_product_discount'])) {
        // Update whether product uses general discount
        $promotion_id = $_POST['promotion_id'];
        $product_id = $_POST['product_id'];
        $use_general_discount = isset($_POST['use_general_discount']) ? 1 : 0;
        
        // Get promotion details
        $promo_stmt = $conn->prepare("SELECT all_discount_percentage FROM promotions WHERE id = ?");
        $promo_stmt->execute([$promotion_id]);
        $promotion = $promo_stmt->fetch(PDO::FETCH_ASSOC);
        $general_discount = $promotion['all_discount_percentage'];
        
        // Update promotion item
        $stmt = $conn->prepare("UPDATE promotion_items SET use_general_discount = ? WHERE promotion_id = ? AND product_id = ?");
        $stmt->execute([$use_general_discount, $promotion_id, $product_id]);
        
        // Update product's discount
        $discount_value = ($use_general_discount && $general_discount) ? $general_discount : null;
        $update_product = $conn->prepare("UPDATE products SET discount_percentage = ? WHERE id = ?");
        $update_product->execute([$discount_value, $product_id]);
        
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Product discount setting updated successfully!'];
        header("Location: promotion.php?action=edit&id=$promotion_id");
        exit();
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $promotion_id = $_GET['id'];
    
    // First get all products in this promotion to reset their flags
    $products_stmt = $conn->prepare("SELECT product_id FROM promotion_items WHERE promotion_id = ?");
    $products_stmt->execute([$promotion_id]);
    $products = $products_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Reset product flags
    if (!empty($products)) {
        $placeholders = implode(',', array_fill(0, count($products), '?'));
        $reset_stmt = $conn->prepare("UPDATE products SET is_offer = 0, discount_percentage = NULL WHERE id IN ($placeholders)");
        $reset_stmt->execute($products);
    }
    
    // Delete promotion (cascade will delete promotion_items)
    $delete_stmt = $conn->prepare("DELETE FROM promotions WHERE id = ?");
    $delete_stmt->execute([$promotion_id]);
    
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Promotion deleted successfully!'];
    header("Location: promotion.php");
    exit();
}

// Get promotion statistics
$stats_stmt = $conn->query("
    SELECT 
        SUM(is_active = 1 AND start_date <= NOW() AND expiry_date >= NOW()) as active_promotions,
        SUM(expiry_date < NOW()) as expired_promotions,
        COUNT(*) as total_promotions
    FROM promotions
");
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get all promotions for listing
$promotions_stmt = $conn->query("
    SELECT p.*, COUNT(pi.id) as item_count 
    FROM promotions p
    LEFT JOIN promotion_items pi ON p.id = pi.promotion_id
    GROUP BY p.id
    ORDER BY p.start_date DESC
");
$promotions = $promotions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get promotion details for edit
$promotion = null;
$promotion_items = [];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $promotion_id = $_GET['id'];
    
    $promotion_stmt = $conn->prepare("SELECT * FROM promotions WHERE id = ?");
    $promotion_stmt->execute([$promotion_id]);
    $promotion = $promotion_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($promotion) {
        $items_stmt = $conn->prepare("
            SELECT pi.*, pr.name as product_name, pr.price, pr.discount_percentage as product_discount
            FROM promotion_items pi
            JOIN products pr ON pi.product_id = pr.id
            WHERE pi.promotion_id = ?
        ");
        $items_stmt->execute([$promotion_id]);
        $promotion_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get all products not in current promotion for add products modal
$available_products = [];
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $promotion_id = $_GET['id'];
    
    $products_stmt = $conn->prepare("
        SELECT p.id, p.name, p.price
        FROM products p
        WHERE p.id NOT IN (
            SELECT product_id FROM promotion_items WHERE promotion_id = ?
        )
    ");
    $products_stmt->execute([$promotion_id]);
    $available_products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);
}
$page_title = 'Promotion Management';
$current_page = 'Promotions';
require_once __DIR__ . '/includes/header.php';
?>

    <style>
        .stats-card {
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .promotion-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-active {
            background-color: var(--second-color);
            color: white;
        }
        .status-upcoming {
            background-color: #e0f2fe;
            color: #075985;
        }
        .status-expired {
            background-color:var(--main-color);
         color: white;
        }
        .product-discount-toggle .form-check-input {
            width: 3em;
            height: 1.5em;
        }
    </style>


        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Promotions Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPromotionModal">
                    <i class="fas fa-plus me-2"></i>Create Promotion
                </button>
                
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']['text']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stats-card colored-second text-second mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase text-second">Active Promotions</h6>
                                    <h2 class="mb-0"><?php echo $stats['active_promotions']; ?></h2>
                                </div>
                                <div class="icon-circle text-second">
                                    <i class="fas fa-bolt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card colored text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase text-third">Expired Promotions</h6>
                                    <h2 class="mb-0"><?php echo $stats['expired_promotions']; ?></h2>
                                </div>
                                <div class="icon-circle text-third">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stats-card colored-second text-second mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title text-uppercase text-second">Total Promotions</h6>
                                    <h2 class="mb-0"><?php echo $stats['total_promotions']; ?></h2>
                                </div>
                                <div class="icon-circle second">
                                    <i class="fas fa-tag"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotions Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">All Promotions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="promotionsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>Expiry Date</th>
                                    <th>Discount</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($promotions as $promo): 
                                    $current_time = time();
                                    $start_time = strtotime($promo['start_date']);
                                    $expiry_time = strtotime($promo['expiry_date']);
                                    
                                    if ($current_time < $start_time) {
                                        $status = 'upcoming';
                                        $status_text = 'Upcoming';
                                    } elseif ($current_time > $expiry_time) {
                                        $status = 'expired';
                                        $status_text = 'Expired';
                                    } else {
                                        $status = $promo['is_active'] ? 'active' : 'paused';
                                        $status_text = $promo['is_active'] ? 'Active' : 'Paused';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($promo['name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($promo['start_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($promo['expiry_date'])); ?></td>
                                    <td>
                                        <?php if ($promo['all_discount_percentage']): ?>
                                            <span class="badge bg-success"><?php echo $promo['all_discount_percentage']; ?>%</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Individual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $promo['item_count']; ?></td>
                                    <td>
                                        <span class="promotion-status status-<?php echo $status; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="promotion.php?action=edit&id=<?php echo $promo['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="promotion.php?action=delete&id=<?php echo $promo['id']; ?>" class="btn btn-sm btn-primary" title="Delete" onclick="return confirm('Are you sure you want to delete this promotion?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && $promotion): ?>
            <!-- Edit Promotion Section -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Promotion: <?php echo htmlspecialchars($promotion['name']); ?></h6>
                    <form method="POST" action="send_promotion_emails.php">
            <input type="hidden" name="promotion_id" value="<?= $promotion['id'] ?>">
            <button type="submit" name="send_emails" class="btn btn-primary" 
                    onclick="return confirm('Send this promotion to all customers?')">
                <i class="fas fa-paper-plane me-2"></i>Send Promotion Emails
            </button>
        </form>
                </div>
                <div class="card-body">
                    <form method="POST" action="promotion.php">
                        <input type="hidden" name="promotion_id" value="<?php echo $promotion['id']; ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Promotion Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($promotion['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="all_discount_percentage" class="form-label">Discount Percentage</label>
                                <input type="number" class="form-control" id="all_discount_percentage" name="all_discount_percentage" 
                                    min="1" max="100" required value="<?php echo htmlspecialchars($promotion['all_discount_percentage']); ?>" 
                                    placeholder="Default is the minimum 1%">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" 
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($promotion['start_date'])); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="datetime-local" class="form-control" id="expiry_date" name="expiry_date" 
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($promotion['expiry_date'])); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($promotion['description']); ?></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" <?php echo $promotion['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Active Promotion</label>
                        </div>
                        <button type="submit" name="update_promotion" class="btn btn-primary">Update Promotion</button>
                    </form>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Products in Promotion</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductsModal">
                            <i class="fas fa-plus me-2"></i>Add Products
                        </button>
                    </div>

                    <?php if (empty($promotion_items)): ?>
                        <div class="alert alert-info">No products added to this promotion yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($promotion_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <?php if ($item['use_general_discount'] && $promotion['all_discount_percentage']): ?>
                                                <span class="badge bg-success"><?php echo $promotion['all_discount_percentage']; ?>%</span>
                                            <?php elseif ($item['product_discount']): ?>
                                                <span class="badge bg-info"><?php echo $item['product_discount']; ?>%</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No discount</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="promotion.php" class="d-inline">
                                                <input type="hidden" name="promotion_id" value="<?php echo $promotion['id']; ?>">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <button type="submit" name="remove_product" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to remove this product from the promotion?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Promotion Modal -->
    <div class="modal fade" id="createPromotionModal" tabindex="-1" aria-labelledby="createPromotionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPromotionModalLabel">Create New Promotion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="promotion.php">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modal_name" class="form-label">Promotion Name</label>
                                <input type="text" class="form-control" id="modal_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_all_discount_percentage" class="form-label">Discount Percentage</label>
                                <input type="number" class="form-control" id="modal_all_discount_percentage" 
                                    name="all_discount_percentage" min="1" max="100" required
                                    placeholder="Deafult is minimum 1%">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modal_start_date" class="form-label">Start Date</label>
                                <input type="datetime-local" class="form-control" id="modal_start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="modal_expiry_date" class="form-label">Expiry Date</label>
                                <input type="datetime-local" class="form-control" id="modal_expiry_date" name="expiry_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="modal_description" class="form-label">Description</label>
                            <textarea class="form-control" id="modal_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_promotion" class="btn btn-primary">Create Promotion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && $promotion): ?>
    <!-- Add Products Modal -->
    <div class="modal fade" id="addProductsModal" tabindex="-1" aria-labelledby="addProductsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductsModalLabel">Add Products to Promotion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="promotion.php">
                    <input type="hidden" name="promotion_id" value="<?php echo $promotion['id']; ?>">
                    <div class="modal-body">
                        <?php if (empty($available_products)): ?>
                            <div class="alert alert-info">All products are already in this promotion.</div>
                        <?php else: ?>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="use_general_discount" name="use_general_discount" checked>
                                <label class="form-check-label" for="use_general_discount">Use general promotion discount for selected products</label>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th width="50px">Select</th>
                                            <th>Product</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($available_products as $product): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input" name="product_ids[]" value="<?php echo $product['id']; ?>">
                                            </td>
                                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <?php if (!empty($available_products)): ?>
                            <button type="submit" name="add_products" class="btn btn-primary">Add Selected Products</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize sidebar functionality
        initSidebar();
        
        // Initialize DataTable
        $('#promotionsTable').DataTable({
            responsive: true,
            columnDefs: [
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: -1 },
                { responsivePriority: 3, targets: 2 },
                { responsivePriority: 4, targets: 3 }
            ]
        });
        
        // Set default datetime for create promotion modal
        const now = new Date();
        const startDate = new Date(now.getTime() + 60 * 60 * 1000); // 1 hour from now
        const expiryDate = new Date(now.getTime() + 24 * 60 * 60 * 1000); // 24 hours from now
        
        // Format for datetime-local input
        function formatDateForInput(date) {
            return date.toISOString().slice(0, 16);
        }
        
        document.getElementById('modal_start_date').value = formatDateForInput(startDate);
        document.getElementById('modal_expiry_date').value = formatDateForInput(expiryDate);
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
    <?php if (isset($_SESSION['email_result'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const result = <?= json_encode($_SESSION['email_result']) ?>;
    const message = result.success 
        ? `Emails sent successfully! (${result.count} recipients)`
        : 'No emails were sent';
    
    alert(message);
});
</script>
<?php 
    unset($_SESSION['email_result']); 
endif; 
?>
</body>
</html>