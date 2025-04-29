<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Initialize variables
$error = '';
$success = $_GET['success'] ?? '';
$filter_product_id = $_GET['product_id'] ?? null;
$filter_warehouse_id = $_GET['warehouse_id'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_date_from = $_GET['date_from'] ?? null;
$filter_date_to = $_GET['date_to'] ?? null;

// Handle movement deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_movement'])) {
    $movement_id = $_POST['movement_id'] ?? null;
    
    try {
        $conn->beginTransaction();
        
        // Get movement details first
        $stmt = $conn->prepare("
            SELECT m.product_id, m.variant_id, m.warehouse_id, m.quantity, m.movement_type 
            FROM inventory_movements m 
            WHERE m.id = ?
        ");
        $stmt->execute([$movement_id]);
        $movement = $stmt->fetch();
        
        if ($movement) {
            // Reverse the movement by adjusting stock
            if ($movement['warehouse_id']) {
                if ($movement['variant_id']) {
                    $stmt = $conn->prepare("
                        UPDATE warehouse_stock 
                        SET quantity = quantity - ? 
                        WHERE warehouse_id = ? AND product_id = ? AND variant_id = ?
                    ");
                    $stmt->execute([
                        $movement['quantity'],
                        $movement['warehouse_id'],
                        $movement['product_id'],
                        $movement['variant_id']
                    ]);
                } else {
                    $stmt = $conn->prepare("
                        UPDATE warehouse_stock 
                        SET quantity = quantity - ? 
                        WHERE warehouse_id = ? AND product_id = ? AND variant_id IS NULL
                    ");
                    $stmt->execute([
                        $movement['quantity'],
                        $movement['warehouse_id'],
                        $movement['product_id']
                    ]);
                }
            }
            
            // Delete the movement record
            $stmt = $conn->prepare("DELETE FROM inventory_movements WHERE id = ?");
            $stmt->execute([$movement_id]);
            
            $conn->commit();
            $success = "Movement deleted and stock adjusted successfully";
        } else {
            $error = "Movement not found";
        }
        
        header("Location: movements.php?success=" . urlencode($success));
        exit();
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error deleting movement: " . $e->getMessage();
    }
}

// Fetch inventory movements
$movements = [];
$query_params = [];
$query = "
    SELECT m.*, 
           p.name as product_name,
           p.product_code,
           v.option_name as variant_option,
           v.option_value as variant_value,
           w.name as warehouse_name,
           u.full_name as user_name,
           IFNULL(oi.order_id, '') as related_order
    FROM inventory_movements m
    JOIN products p ON m.product_id = p.id
    LEFT JOIN product_variants v ON m.variant_id = v.id
    LEFT JOIN warehouses w ON m.warehouse_id = w.id
    LEFT JOIN employees u ON m.user_id = u.id
    LEFT JOIN order_items oi ON m.reference_id = oi.order_id AND m.movement_type = 'sale'
    WHERE 1=1
";

// Apply filters
if ($filter_product_id) {
    $query .= " AND m.product_id = ?";
    $query_params[] = $filter_product_id;
}

if ($filter_warehouse_id) {
    $query .= " AND m.warehouse_id = ?";
    $query_params[] = $filter_warehouse_id;
}

if ($filter_type) {
    $query .= " AND m.movement_type = ?";
    $query_params[] = $filter_type;
}

if ($filter_date_from) {
    $query .= " AND m.created_at >= ?";
    $query_params[] = $filter_date_from . ' 00:00:00';
}

if ($filter_date_to) {
    $query .= " AND m.created_at <= ?";
    $query_params[] = $filter_date_to . ' 23:59:59';
}

$query .= " ORDER BY m.created_at DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($query_params);
    $movements = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching movements: " . $e->getMessage();
}

// Fetch filter options
$products = [];
$warehouses = [];
$movement_types = ['purchase', 'sale', 'adjustment', 'transfer', 'return', 'damage', 'other'];

try {
    $stmt = $conn->query("SELECT id, name, product_code FROM products ORDER BY name");
    $products = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT id, name FROM warehouses WHERE status = 1 ORDER BY name");
    $warehouses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching filter options: " . $e->getMessage();
}
$page_title = 'Movements';
$current_page = 'Movements';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Inventory Movements</h1>
        <a href="adjustments.php" class="d-none d-sm-inline-block btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> New Adjustment
        </a>
    </div>

    <!-- Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select class="form-control" id="product_id" name="product_id">
                        <option value="">All Products</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" <?= $filter_product_id == $product['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['product_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="warehouse_id" class="form-label">Warehouse</label>
                    <select class="form-control" id="warehouse_id" name="warehouse_id">
                        <option value="">All Warehouses</option>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <option value="<?= $warehouse['id'] ?>" <?= $filter_warehouse_id == $warehouse['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($warehouse['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="type" class="form-label">Movement Type</label>
                    <select class="form-control" id="type" name="type">
                        <option value="">All Types</option>
                        <?php foreach ($movement_types as $type): ?>
                            <option value="<?= $type ?>" <?= $filter_type == $type ? 'selected' : '' ?>>
                                <?= ucfirst($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="<?= htmlspecialchars($filter_date_from) ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="<?= htmlspecialchars($filter_date_to) ?>">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Inventory Movements</h6>
            <div>
            <a href="export_movements.php<?= $filter_product_id ? '?product_id='.$filter_product_id : '' ?><?= $filter_warehouse_id ? '&warehouse_id='.$filter_warehouse_id : '' ?><?= $filter_type ? '&type='.$filter_type : '' ?><?= $filter_date_from ? '&date_from='.$filter_date_from : '' ?><?= $filter_date_to ? '&date_to='.$filter_date_to : '' ?>" 
   class="btn btn-sm btn-primary">
    <i class="fas fa-file-export"></i> Export
</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="movementsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Variant</th>
                            <th>Warehouse</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reference</th>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movements as $movement): ?>
                            <tr>
                                <td><?= date('M j, Y H:i', strtotime($movement['created_at'])) ?></td>
                                <td>
                                    <?= htmlspecialchars($movement['product_name']) ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($movement['product_code']) ?></small>
                                </td>
                                <td>
                                    <?php if ($movement['variant_option']): ?>
                                        <?= htmlspecialchars($movement['variant_option']) ?>: 
                                        <?= htmlspecialchars($movement['variant_value']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($movement['warehouse_name'] ?? 'N/A') ?></td>
                                <td><?= ucfirst($movement['movement_type']) ?></td>
                                <td class="<?= $movement['quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $movement['quantity'] > 0 ? '+' : '' ?><?= $movement['quantity'] ?>
                                </td>
                                <td>
                                    <?php if ($movement['related_order']): ?>
                                        <a href="order_details.php?id=<?= $movement['related_order'] ?>">
                                            Order #<?= $movement['related_order'] ?>
                                        </a>
                                    <?php elseif ($movement['reference_id']): ?>
                                        Ref: <?= $movement['reference_id'] ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($movement['user_name'] ?? 'System') ?></td>
                                <td>
                                    <?php if (in_array($movement['movement_type'], ['adjustment', 'transfer', 'return'])): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="movement_id" value="<?= $movement['id'] ?>">
                                            <button type="submit" name="delete_movement" class="btn btn-sm btn-primary"
                                                    onclick="return confirm('Delete this movement and adjust stock? This cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($movements)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No movements found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Bootstrap Bundle with Popper -->
 <!-- jQuery first, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#movementsTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 50,
        "dom": '<"top"f>rt<"bottom"lip><"clear">'
    });
    
    // Confirm before deleting movement
    $('form[method="post"]').submit(function(e) {
        if ($(this).find('button[name="delete_movement"]').length) {
            if (!confirm('Are you sure you want to delete this movement and adjust stock?')) {
                e.preventDefault();
            }
        }
    });
});
</script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the edit modal with data
            var editCategoryModal = document.getElementById('editCategoryModal');
            if (editCategoryModal) {
                editCategoryModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var id = button.getAttribute('data-id');
                    var name = button.getAttribute('data-name');
                    var active = button.getAttribute('data-active');
                    
                    document.getElementById('editCategoryId').value = id;
                    document.getElementById('editCategoryName').value = name;
                    document.getElementById('editCategoryActive').checked = active === '1';
                });
            }
            
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
</body>
</html>