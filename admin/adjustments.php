<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $product_id = (int)$_POST['product_id'];
        $variant_id = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
        $warehouse_id = !empty($_POST['warehouse_id']) ? (int)$_POST['warehouse_id'] : null;
        $quantity = (int)$_POST['quantity'];
        $notes = trim($_POST['notes']);
        $user_id = $_SESSION['employee_id'];

        // Validate quantity
        if ($quantity === 0) {
            throw new Exception("Adjustment quantity cannot be zero");
        }

        // Update warehouse stock
        if ($warehouse_id) {
            $stmt = $conn->prepare("
                INSERT INTO warehouse_stock 
                (warehouse_id, product_id, variant_id, quantity) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + ?
            ");
            $stmt->execute([$warehouse_id, $product_id, $variant_id, $quantity, $quantity]);
        }

        // Record inventory movement
        $stmt = $conn->prepare("
            INSERT INTO inventory_movements 
            (product_id, variant_id, warehouse_id, quantity, movement_type, notes, user_id)
            VALUES (?, ?, ?, ?, 'adjustment', ?, ?)
        ");
        $stmt->execute([$product_id, $variant_id, $warehouse_id, $quantity, $notes, $user_id]);

        $conn->commit();
        $success = "Stock adjustment recorded successfully";
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error processing adjustment: " . $e->getMessage();
    }
}

// Get products, variants, and warehouses for dropdowns
$products = [];
$warehouses = [];

try {
    $stmt = $conn->query("SELECT id, name FROM products WHERE active = 1 ORDER BY name");
    $products = $stmt->fetchAll();

    $stmt = $conn->query("SELECT id, name FROM warehouses WHERE status = 1 ORDER BY name");
    $warehouses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
}
$page_title = 'Adjustments';
$current_page = 'Adjustments';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Stock Adjustments</h1>
        <a href="movements.php" class="d-none d-sm-inline-block btn btn-sm btn-primary">
            <i class="fas fa-arrow-left"></i> View All Movements
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
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Make Adjustment</h6>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-group">
                            <label for="product_id">Product *</label>
                            <select class="form-control" id="product_id" name="product_id" required>
                                <option value="">-- Select Product --</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" id="variant_group" style="display: none;">
                            <label for="variant_id">Variant</label>
                            <select class="form-control" id="variant_id" name="variant_id">
                                <option value="">-- Base Product --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="warehouse_id">Warehouse</label>
                            <select class="form-control" id="warehouse_id" name="warehouse_id">
                                <option value="">-- All Warehouses --</option>
                                <?php foreach ($warehouses as $warehouse): ?>
                                    <option value="<?= $warehouse['id'] ?>"><?= htmlspecialchars($warehouse['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Leave blank to adjust total stock</small>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Adjustment Quantity *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-plus-minus"></i>
                                    </span>
                                </div>
                                <input type="number" class="form-control" id="quantity" name="quantity" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('quantity').value = Math.abs(document.getElementById('quantity').value)">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('quantity').value = -Math.abs(document.getElementById('quantity').value)">
                                        <i class="fas fa-minus"></i> Remove
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Positive to add stock, negative to remove</small>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            <small class="form-text text-muted">Reason for this adjustment</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Record Adjustment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Adjustments</h6>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $stmt = $conn->query("
                            SELECT m.*, p.name as product_name, 
                                   COALESCE(v.option_value, '') as variant_value,
                                   w.name as warehouse_name,
                                   u.username as user_name
                            FROM inventory_movements m
                            JOIN products p ON m.product_id = p.id
                            LEFT JOIN product_variants v ON m.variant_id = v.id
                            LEFT JOIN warehouses w ON m.warehouse_id = w.id
                            LEFT JOIN users u ON m.user_id = u.id
                            WHERE m.movement_type = 'adjustment'
                            ORDER BY m.created_at DESC
                            LIMIT 5
                        ");
                        $adjustments = $stmt->fetchAll();

                        if (!empty($adjustments)) {
                            foreach ($adjustments as $adj) {
                                echo '<div class="mb-3">';
                                echo '<div class="font-weight-bold">' . htmlspecialchars($adj['product_name']);
                                if (!empty($adj['variant_value'])) {
                                    echo ' (' . htmlspecialchars($adj['variant_value']) . ')';
                                }
                                echo '</div>';
                                echo '<div class="' . ($adj['quantity'] > 0 ? 'text-success' : 'text-danger') . '">';
                                echo ($adj['quantity'] > 0 ? '+' : '') . $adj['quantity'];
                                echo '</div>';
                                echo '<small class="text-muted">';
                                if ($adj['warehouse_name']) {
                                    echo htmlspecialchars($adj['warehouse_name']) . ' • ';
                                }
                                echo htmlspecialchars($adj['user_name']) . ' • ';
                                echo date('M j, H:i', strtotime($adj['created_at']));
                                echo '</small>';
                                if (!empty($adj['notes'])) {
                                    echo '<div class="text-muted small mt-1">' . htmlspecialchars($adj['notes']) . '</div>';
                                }
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="text-muted">No recent adjustments</div>';
                        }
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-danger">Error fetching adjustments</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load variants when product changes
    $('#product_id').change(function() {
        const productId = $(this).val();
        if (productId) {
            $.get('/api/get_product_variants.php', { product_id: productId }, function(data) {
                const $variantSelect = $('#variant_id');
                $variantSelect.empty().append('<option value="">-- Base Product --</option>');
                
                if (data.length > 0) {
                    $('#variant_group').show();
                    $.each(data, function(index, variant) {
                        $variantSelect.append(
                            `<option value="${variant.id}">${variant.option_name}: ${variant.option_value}</option>`
                        );
                    });
                } else {
                    $('#variant_group').hide();
                }
            }, 'json');
        } else {
            $('#variant_group').hide();
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<!-- Bootstrap Bundle with Popper -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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