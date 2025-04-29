<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}


$warehouse_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Get warehouse details
$warehouse = [];
try {
    $stmt = $conn->prepare("SELECT * FROM warehouses WHERE id = ?");
    $stmt->execute([$warehouse_id]);
    $warehouse = $stmt->fetch();
    
    if (!$warehouse) {
        throw new Exception("Warehouse not found");
    }
} catch (PDOException $e) {
    $error = "Error fetching warehouse: " . $e->getMessage();
}

// Handle stock adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_stock'])) {
    try {
        $conn->beginTransaction();
        
        $product_id = (int)$_POST['product_id'];
        $variant_id = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
        $adjustment = (int)$_POST['adjustment'];
        $notes = trim($_POST['notes']);
        $user_id = $_SESSION['user_id'];

        if ($adjustment === 0) {
            throw new Exception("Adjustment quantity cannot be zero");
        }

        // Update warehouse stock
        $stmt = $conn->prepare("
            INSERT INTO warehouse_stock 
            (warehouse_id, product_id, variant_id, quantity) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + ?
        ");
        $stmt->execute([
            $warehouse_id,
            $product_id,
            $variant_id,
            $adjustment,
            $adjustment
        ]);

        // Record inventory movement
        $stmt = $conn->prepare("
            INSERT INTO inventory_movements 
            (product_id, variant_id, warehouse_id, quantity, movement_type, notes, user_id)
            VALUES (?, ?, ?, ?, 'adjustment', ?, ?)
        ");
        $stmt->execute([
            $product_id,
            $variant_id,
            $warehouse_id,
            $adjustment,
            $notes,
            $user_id
        ]);

        $conn->commit();
        $success = "Stock adjusted successfully";
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error adjusting stock: " . $e->getMessage();
    }
}

// Get current stock
$stock = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            ws.*,
            p.name AS product_name,
            v.option_name,
            v.option_value
        FROM warehouse_stock ws
        LEFT JOIN products p ON ws.product_id = p.id
        LEFT JOIN product_variants v ON ws.variant_id = v.id
        WHERE ws.warehouse_id = ?
        ORDER BY p.name, v.option_name
    ");
    $stmt->execute([$warehouse_id]);
    $stock = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching stock: " . $e->getMessage();
}

// Get recent movements
$movements = [];
try {
    $stmt = $conn->prepare("
        SELECT m.*, p.name AS product_name, 
               v.option_name, v.option_value,
               u.username
        FROM inventory_movements m
        JOIN products p ON m.product_id = p.id
        LEFT JOIN product_variants v ON m.variant_id = v.id
        LEFT JOIN users u ON m.user_id = u.id
        WHERE m.warehouse_id = ?
        ORDER BY m.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$warehouse_id]);
    $movements = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching movements: " . $e->getMessage();
}

$page_title = 'Warehouse Stock';
$current_page = 'Stock';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Stock Management: <?= htmlspecialchars($warehouse['name']) ?>
        </h1>
        <a href="warehouses.php" class="d-none d-sm-inline-block btn btn-sm btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Warehouses
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Current Inventory</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
                        <i class="fas fa-plus-minus"></i> Quick Adjust
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Variant</th>
                                    <th>Quantity</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td>
                                            <?php if ($item['option_name']): ?>
                                                <?= htmlspecialchars($item['option_name']) ?>: 
                                                <?= htmlspecialchars($item['option_value']) ?>
                                            <?php else: ?>
                                                <em>Base Product</em>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= date('M j, Y H:i', strtotime($item['last_updated'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Movements</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($movements as $movement): ?>
                        <div class="mb-3 border-bottom pb-2">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?= htmlspecialchars($movement['product_name']) ?></strong>
                                    <?php if ($movement['option_name']): ?>
                                        <br><small><?= htmlspecialchars($movement['option_name']) ?>: 
                                        <?= htmlspecialchars($movement['option_value']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="<?= $movement['quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $movement['quantity'] > 0 ? '+' : '' ?><?= $movement['quantity'] ?>
                                </div>
                            </div>
                            <div class="text-muted small">
                                <?= date('M j, H:i', strtotime($movement['created_at'])) ?> 
                                by <?= htmlspecialchars($movement['username']) ?>
                                <?php if ($movement['notes']): ?>
                                    <br>Note: <?= htmlspecialchars($movement['notes']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select class="form-select" name="product_id" required>
                            <option value="">Select Product</option>
                            <?php
                            $products = $conn->query("SELECT id, name FROM products ORDER BY name")->fetchAll();
                            foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Variant (optional)</label>
                        <select class="form-select" name="variant_id">
                            <option value="">Base Product</option>
                            <?php
                            $variants = $conn->query("
                                SELECT v.id, v.product_id, v.option_value, p.name 
                                FROM product_variants v
                                JOIN products p ON v.product_id = p.id
                                ORDER BY p.name, v.option_value
                            ")->fetchAll();
                            foreach ($variants as $variant): ?>
                                <option value="<?= $variant['id'] ?>" data-product="<?= $variant['product_id'] ?>">
                                    <?= htmlspecialchars($variant['name']) ?> - 
                                    <?= htmlspecialchars($variant['option_value']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adjustment Quantity</label>
                        <input type="number" name="adjustment" class="form-control" required 
                               placeholder="Positive to add, negative to subtract">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="adjust_stock" class="btn btn-primary">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter variants based on selected product
    const productSelect = document.querySelector('select[name="product_id"]');
    const variantSelect = document.querySelector('select[name="variant_id"]');
    
    productSelect.addEventListener('change', function() {
        const productId = this.value;
        Array.from(variantSelect.options).forEach(option => {
            if (option.value === '') return;
            const show = option.dataset.product === productId;
            option.style.display = show ? 'block' : 'none';
            option.hidden = !show;
        });
        variantSelect.value = '';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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