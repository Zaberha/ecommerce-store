<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}


// Get inventory summary
$inventory_stats = [];
try {
    $stmt = $conn->query("
        SELECT 
            COUNT(DISTINCT p.id) as total_products,
            COUNT(DISTINCT v.id) as total_variants,
            SUM(COALESCE(ws.quantity, p.min_stock)) as total_items,
            SUM(CASE WHEN COALESCE(ws.quantity, p.min_stock) <= p.min_stock THEN 1 ELSE 0 END) as low_stock_items,
            COUNT(DISTINCT po.id) as pending_orders
        FROM products p
        LEFT JOIN product_variants v ON p.id = v.product_id
        LEFT JOIN warehouse_stock ws ON (p.id = ws.product_id AND (v.id = ws.variant_id OR ws.variant_id IS NULL))
        LEFT JOIN purchase_orders po ON po.status IN ('ordered', 'partial')
    ");
    $inventory_stats = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Error fetching inventory stats: " . $e->getMessage();
}

// Get recent stock movements
$recent_movements = [];
try {
    $stmt = $conn->query("
        SELECT m.*, p.name as product_name, 
               COALESCE(v.option_name, '') as variant_option,
               COALESCE(v.option_value, '') as variant_value,
               w.name as warehouse_name,
               u.username as user_name
        FROM inventory_movements m
        JOIN products p ON m.product_id = p.id
        LEFT JOIN product_variants v ON m.variant_id = v.id
        LEFT JOIN warehouses w ON m.warehouse_id = w.id
        LEFT JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC
        LIMIT 10
    ");
    $recent_movements = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching recent movements: " . $e->getMessage();
}
$page_title = 'Inventory Dashboard';
$current_page = 'Inventory Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Inventory Dashboard</h1>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <!-- Products Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $inventory_stats['total_products'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Variants Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Variants</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $inventory_stats['total_variants'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $inventory_stats['low_stock_items'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Orders Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Pending Purchase Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $inventory_stats['pending_orders'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Stock Movements</h6>
                    <a href="movements.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Variant</th>
                                    <th>Warehouse</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_movements as $movement): ?>
                                <tr>
                                    <td><?= formatDateTime($movement['created_at']) ?></td>
                                    <td><?= htmlspecialchars($movement['product_name']) ?></td>
                                    <td>
                                        <?php if (!empty($movement['variant_option'])): ?>
                                            <?= htmlspecialchars($movement['variant_option']) ?>: 
                                            <?= htmlspecialchars($movement['variant_value']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($movement['warehouse_name'] ?? 'N/A') ?></td>
                                    <td><?= ucfirst($movement['movement_type']) ?></td>
                                    <td class="<?= $movement['quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= $movement['quantity'] > 0 ? '+' : '' ?><?= $movement['quantity'] ?>
                                    </td>
                                    <td><?= htmlspecialchars($movement['user_name'] ?? 'System') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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