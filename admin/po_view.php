<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

try {
    // Get order details
    $stmt = $conn->prepare("SELECT po.*, s.name as supplier_name, w.name as warehouse_name, u.username
                           FROM purchase_orders po
                           LEFT JOIN suppliers s ON po.supplier_id = s.id
                           LEFT JOIN warehouses w ON po.warehouse_id = w.id
                           LEFT JOIN employees u ON po.created_by = u.id
                           WHERE po.id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    // Get order items
    $stmt = $conn->prepare("SELECT poi.*, p.name as product_name, p.product_code
                           FROM purchase_order_items poi
                           JOIN products p ON poi.product_id = p.id
                           WHERE purchase_order_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching order: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        $status = $_POST['status'];
        $receivedItems = $_POST['received'] ?? [];
        
        // Update order status
        $conn->prepare("UPDATE purchase_orders SET status = ? WHERE id = ?")
             ->execute([$status, $id]);
        
        // Update received quantities
        foreach ($receivedItems as $itemId => $qty) {
            $conn->prepare("UPDATE purchase_order_items 
                           SET received_quantity = received_quantity + ?
                           WHERE id = ?")
                 ->execute([$qty, $itemId]);
            
            // Add to inventory if received
            if ($qty > 0) {
                $item = $conn->query("SELECT * FROM purchase_order_items WHERE id = $itemId")->fetch();
                
                // Update warehouse stock
                $conn->prepare("INSERT INTO warehouse_stock 
                              (warehouse_id, product_id, quantity)
                              VALUES (?,?,?)
                              ON DUPLICATE KEY UPDATE quantity = quantity + ?")
                     ->execute([$order['warehouse_id'], $item['product_id'], $qty, $qty]);
                
                // Record inventory movement
                $conn->prepare("INSERT INTO inventory_movements 
                              (product_id, warehouse_id, quantity, movement_type, 
                               reference_id, user_id)
                              VALUES (?,?,?, 'purchase',?,?)")
                     ->execute([
                         $item['product_id'],
                         $order['warehouse_id'],
                         $qty,
                         $id,
                         $_SESSION['employee_id']
                     ]);
            }
        }
        
        $conn->commit();
        header("Refresh:0");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error updating order: " . $e->getMessage();
    }
}
$page_title = 'PO View';
$current_page = 'PO View';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Purchase Order #<?= $order['order_number'] ?></h1>
        <div>
            <a href="purchase_orders.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Supplier</h5>
                            <p><?= htmlspecialchars($order['supplier_name']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Warehouse</h5>
                            <p><?= htmlspecialchars($order['warehouse_name'] ?? 'N/A') ?></p>
                        </div>
                    </div>

                    <h5>Order Items</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Ordered</th>
                                    <th>Received</th>
                                    <th>Pending</th>
                                    <th>Unit Cost</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= $item['received_quantity'] ?></td>
                                        <td><?= $item['quantity'] - $item['received_quantity'] ?></td>
                                        <td><?= formatCurrency($item['cost_price']) ?></td>
                                        <td><?= formatCurrency($item['total_price']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <?php foreach (['ordered', 'received', 'partial', 'cancelled'] as $s) : ?>
                                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr>
                        
                        <h5>Receive Items</h5>
                        <?php foreach ($items as $item) : ?>
                            <div class="form-group">
                                <label><?= htmlspecialchars($item['product_name']) ?></label>
                                <input type="number" name="received[<?= $item['id'] ?>]" 
                                    class="form-control" min="0" max="<?= $item['quantity'] - $item['received_quantity'] ?>"
                                    value="0">
                            </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-primary btn-block">
                            Update Order Status
                        </button>
                    </form>

                    <hr>
                    
                    <div class="mb-3">
                        <label>Order Total</label>
                        <h4><?= formatCurrency($order['total_amount']) ?></h4>
                    </div>
                    
                    <div class="mb-3">
                        <label>Created By</label>
                        <p><?= htmlspecialchars($order['username']) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <label>Notes</label>
                        <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
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