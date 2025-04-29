<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Filters
$status = $_GET['status'] ?? '';
$supplier_id = $_GET['supplier_id'] ?? '';
$warehouse_id = $_GET['warehouse_id'] ?? '';
$search = $_GET['search'] ?? '';

// Base query
$query = "SELECT po.*, s.name as supplier_name, w.name as warehouse_name 
          FROM purchase_orders po
          LEFT JOIN suppliers s ON po.supplier_id = s.id
          LEFT JOIN warehouses w ON po.warehouse_id = w.id
          WHERE 1=1";

$params = [];
$types = '';

if (!empty($status)) {
    $query .= " AND po.status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($supplier_id)) {
    $query .= " AND po.supplier_id = ?";
    $params[] = $supplier_id;
    $types .= 'i';
}

if (!empty($warehouse_id)) {
    $query .= " AND po.warehouse_id = ?";
    $params[] = $warehouse_id;
    $types .= 'i';
}

if (!empty($search)) {
    $query .= " AND (po.order_number LIKE ? OR s.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$query .= " ORDER BY po.order_date DESC";

try {
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching orders: " . $e->getMessage();
}

// Get filters data
$suppliers = $conn->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();
$warehouses = $conn->query("SELECT id, name FROM warehouses WHERE status = 1 ORDER BY name")->fetchAll();

$page_title = 'PO';
$current_page = 'PO';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Purchase Orders</h1>
        <a href="po_add_edit.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus"></i> Create New Order
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-3 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <?php foreach (['draft', 'ordered', 'received', 'partial', 'cancelled'] as $s) : ?>
                            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Supplier</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">All Suppliers</option>
                        <?php foreach ($suppliers as $s) : ?>
                            <option value="<?= $s['id'] ?>" <?= $supplier_id == $s['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Warehouse</label>
                    <select name="warehouse_id" class="form-control">
                        <option value="">All Warehouses</option>
                        <?php foreach ($warehouses as $w) : ?>
                            <option value="<?= $w['id'] ?>" <?= $warehouse_id == $w['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($w['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Search</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Supplier</th>
                                <th>Warehouse</th>
                                <th>Order Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= htmlspecialchars($order['supplier_name']) ?></td>
                                    <td><?= htmlspecialchars($order['warehouse_name'] ?? 'N/A') ?></td>
                                    <td><?= formatDateTime($order['order_date']) ?></td>
                                    <td><?= formatCurrency($order['total_amount']) ?></td>
                                    <td>
                                        <span class="badge <?= getStatusBadge($order['status']) ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="po_view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                      
                                        <a href="po_add_edit.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                       
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
function getStatusBadge($status) {
    $map = [
        'draft' => 'bg-success',
        'ordered' => 'bg-warning',
        'received' => 'bg-primary',
        'partial' => 'bg-info',
        'cancelled' => 'bg-danger'
    ];
    return $map[$status] ?? 'secondary';
}
require_once __DIR__ . '/includes/footer.php'; ?>
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