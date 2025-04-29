<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Get filter parameters
$status = $_GET['status'] ?? 'pending';
$alert_type = $_GET['alert_type'] ?? '';
$product_id = $_GET['product_id'] ?? '';
$warehouse_id = $_GET['warehouse_id'] ?? '';

// Build query
$query = "SELECT a.*, p.name as product_name, 
          v.option_name as variant_option, v.option_value as variant_value,
          w.name as warehouse_name
          FROM inventory_alerts a
          JOIN products p ON a.product_id = p.id
          LEFT JOIN product_variants v ON a.variant_id = v.id
          LEFT JOIN warehouses w ON a.warehouse_id = w.id";

$where = [];
$params = [];

if ($status !== 'all') {
    $where[] = "a.status = ?";
    $params[] = $status;
}

if (!empty($alert_type)) {
    $where[] = "a.alert_type = ?";
    $params[] = $alert_type;
}

if (!empty($product_id)) {
    $where[] = "a.product_id = ?";
    $params[] = $product_id;
}

if (!empty($warehouse_id)) {
    $where[] = "a.warehouse_id = ?";
    $params[] = $warehouse_id;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY a.created_at DESC";

// Get alerts
$alerts = [];
try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $alerts = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching alerts: " . $e->getMessage();
}

// Get products for filter dropdown
$products = [];
$warehouses = [];
try {
    $stmt = $conn->query("SELECT id, name FROM products ORDER BY name");
    $products = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT id, name FROM warehouses WHERE status = 1 ORDER BY name");
    $warehouses = $stmt->fetchAll();
} catch (PDOException $e) {
    // Log error but don't break the page
    error_log("Error fetching filter options: " . $e->getMessage());
}
$page_title = 'Inventory Alerts';
$current_page = 'Alerts';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Inventory Alerts</h1>
        <div>
            <a href="alerts_settings.php" class="btn btn-sm btn-primary">
                <i class="fas fa-cog"></i> Alert Settings
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="get" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label for="status" class="mr-2">Status:</label>
                    <select class="form-control" id="status" name="status">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="sent" <?= $status === 'sent' ? 'selected' : '' ?>>Sent</option>
                        <option value="acknowledged" <?= $status === 'acknowledged' ? 'selected' : '' ?>>Acknowledged</option>
                    </select>
                </div>
                
                <div class="form-group mr-3 mb-2">
                    <label for="alert_type" class="mr-2">Type:</label>
                    <select class="form-control" id="alert_type" name="alert_type">
                        <option value="">All Types</option>
                        <option value="low_stock" <?= $alert_type === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                        <option value="out_of_stock" <?= $alert_type === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                        <option value="over_stock" <?= $alert_type === 'over_stock' ? 'selected' : '' ?>>Over Stock</option>
                    </select>
                </div>
                
                <div class="form-group mr-3 mb-2">
                    <label for="product_id" class="mr-2">Product:</label>
                    <select class="form-control" id="product_id" name="product_id">
                        <option value="">All Products</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" <?= $product_id == $product['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($product['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group mr-3 mb-2">
                    <label for="warehouse_id" class="mr-2">Warehouse:</label>
                    <select class="form-control" id="warehouse_id" name="warehouse_id">
                        <option value="">All Warehouses</option>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <option value="<?= $warehouse['id'] ?>" <?= $warehouse_id == $warehouse['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($warehouse['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary mb-2">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <a href="inventory_alerts.php" class="btn btn-primary mb-2 ml-2">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>
    </div>

    <!-- Alerts Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Alerts</h6>
            <span class="badge bg-primary"><?= count($alerts) ?> Alert(s)</span>
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
                            <th>Current Qty</th>
                            <th>Threshold</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert): ?>
                            <tr>
                                <td><?= formatDateTime($alert['created_at']) ?></td>
                                <td><?= htmlspecialchars($alert['product_name']) ?></td>
                                <td>
                                    <?php if (!empty($alert['variant_option'])): ?>
                                        <?= htmlspecialchars($alert['variant_option']) ?>: 
                                        <?= htmlspecialchars($alert['variant_value']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($alert['warehouse_name'] ?? 'All') ?></td>
                                <td>
                                    <?php 
                                        $badge_class = [
                                            'low_stock' => 'warning',
                                            'out_of_stock' => 'danger',
                                            'over_stock' => 'info'
                                        ][$alert['alert_type']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badge_class ?>">
                                        <?= ucwords(str_replace('_', ' ', $alert['alert_type'])) ?>
                                    </span>
                                </td>
                                <td><?= $alert['current_quantity'] ?></td>
                                <td><?= $alert['threshold_quantity'] ?></td>
                                <td>
                                    <?php 
                                        $badge_class = [
                                            'pending' => 'secondary',
                                            'sent' => 'primary',
                                            'acknowledged' => 'success'
                                        ][$alert['status']] ?? 'light';
                                    ?>
                                    <span class="badge bg-<?= $badge_class ?>">
                                        <?= ucfirst($alert['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($alert['status'] === 'pending'): ?>
                                            <form method="post" action="process_alert.php" class="d-inline">
                                                <input type="hidden" name="alert_id" value="<?= $alert['id'] ?>">
                                                <button type="submit" name="action" value="mark_sent" 
                                                    class="btn btn-primary" title="Mark as Sent">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($alert['status'] === 'sent'): ?>
                                            <form method="post" action="process_alert.php" class="d-inline">
                                                <input type="hidden" name="alert_id" value="<?= $alert['id'] ?>">
                                                <button type="submit" name="action" value="mark_acknowledged" 
                                                    class="btn btn-success" title="Mark as Acknowledged">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <a href="/inventory/products/?id=<?= $alert['product_id'] ?>" 
                                            class="btn btn-info" title="View Product">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
<script>
    // Update the form submission in alerts/index.php to use AJAX
$(document).ready(function() {
    $('form[action="process_alert.php"]').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var button = form.find('button[type="submit"]');
        
        button.prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update the status badge in the table
                    var row = form.closest('tr');
                    var statusCell = row.find('td:nth-child(8)');
                    
                    var newBadgeClass = {
                        'sent': 'primary',
                        'acknowledged': 'success'
                    }[response.new_status] || 'secondary';
                    
                    statusCell.html(`
                        <span class="badge bg-${newBadgeClass}">
                            ${response.new_status.charAt(0).toUpperCase() + response.new_status.slice(1)}
                        </span>
                    `);
                    
                    // Update the action buttons
                    var actionsCell = row.find('td:nth-child(9)');
                    if (response.new_status === 'sent') {
                        actionsCell.html(`
                            <form method="post" action="process_alert.php" class="d-inline">
                                <input type="hidden" name="alert_id" value="${form.find('input[name="alert_id"]').val()}">
                                <button type="submit" name="action" value="mark_acknowledged" 
                                    class="btn btn-success" title="Mark as Acknowledged">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <a href="/inventory/products/?id=${row.find('td:nth-child(2)').data('product-id')}" 
                                class="btn btn-info" title="View Product">
                                <i class="fas fa-eye"></i>
                            </a>
                        `);
                    } else if (response.new_status === 'acknowledged') {
                        actionsCell.html(`
                            <a href="/inventory/products/?id=${row.find('td:nth-child(2)').data('product-id')}" 
                                class="btn btn-info" title="View Product">
                                <i class="fas fa-eye"></i>
                            </a>
                        `);
                    }
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('An error occurred while processing your request');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    });
});
</script>
</body>
</html>