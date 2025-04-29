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
$transfer_id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';

// Get all warehouses for dropdowns
$warehouses = [];
try {
    $stmt = $conn->query("SELECT id, name FROM warehouses WHERE status = 1 ORDER BY name");
    $warehouses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching warehouses: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        if (isset($_POST['create_transfer'])) {
            // Create new transfer
            $from_warehouse_id = $_POST['from_warehouse_id'];
            $to_warehouse_id = $_POST['to_warehouse_id'];
            $notes = $_POST['notes'] ?? '';
            
            // Validate warehouses are different
            if ($from_warehouse_id == $to_warehouse_id) {
                throw new Exception("Source and destination warehouses must be different");
            }

            $stmt = $conn->prepare("
                INSERT INTO inventory_transfers 
                (from_warehouse_id, to_warehouse_id, status, notes, created_by) 
                VALUES (?, ?, 'pending', ?, ?)
            ");
            $stmt->execute([$from_warehouse_id, $to_warehouse_id, $notes, $_SESSION['employee_id']]);
            $transfer_id = $conn->lastInsertId();
            $conn->commit(); // Explicitly commit before redirect
            
            $success = "Transfer created successfully! Add items below.";
            header("Location: transfers.php?action=edit&id=$transfer_id&success=" . urlencode($success));
            exit();

        } elseif (isset($_POST['add_transfer_item'])) {
            // Add item to transfer
            $transfer_id = $_POST['transfer_id'];
            $product_id = $_POST['product_id'];
            $variant_id = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
            $quantity = (int)$_POST['quantity'];
            
            // Validate quantity
            if ($quantity <= 0) {
                throw new Exception("Quantity must be greater than 0");
            }

            // Check available stock in source warehouse
            $stmt = $conn->prepare("
                SELECT quantity 
                FROM warehouse_stock 
                WHERE warehouse_id = (
                    SELECT from_warehouse_id FROM inventory_transfers WHERE id = ?
                ) 
                AND product_id = ? 
                " . ($variant_id ? "AND variant_id = ?" : "AND variant_id IS NULL") . "
            ");
            
            if ($variant_id) {
                $stmt->execute([$transfer_id, $product_id, $variant_id]);
            } else {
                $stmt->execute([$transfer_id, $product_id]);
            }
            
            $available = $stmt->fetchColumn();
            
            if ($available === false || $available < $quantity) {
                throw new Exception("Not enough stock available for transfer");
            }

            if ($variant_id) {
                // Verify variant exists for this product
                $stmt = $conn->prepare("SELECT id FROM product_variants WHERE id = ? AND product_id = ?");
                $stmt->execute([$variant_id, $product_id]);
                if (!$stmt->fetch()) {
                    throw new Exception("Invalid variant selected for this product");
                }
            }
            // Add item to transfer
            $stmt = $conn->prepare("
                INSERT INTO inventory_transfer_items 
                (transfer_id, product_id, variant_id, quantity) 
                VALUES (?, ?, ?, ?)
            ");
          // Execute with proper NULL handling
$stmt->execute([
    $transfer_id, 
    $product_id, 
    $variant_id ?: null, // Ensure NULL is passed when no variant
    $quantity
]);
            
            $success = "Item added to transfer successfully";

        } elseif (isset($_POST['complete_transfer'])) {
            // Complete the transfer
            $transfer_id = $_POST['transfer_id'];
            
            // Verify transfer exists and is pending
            $stmt = $conn->prepare("
                SELECT status, from_warehouse_id, to_warehouse_id 
                FROM inventory_transfers 
                WHERE id = ? FOR UPDATE
            ");
            $stmt->execute([$transfer_id]);
            $transfer = $stmt->fetch();
            
            if (!$transfer) {
                throw new Exception("Transfer not found");
            }
            
            if ($transfer['status'] != 'pending') {
                throw new Exception("Only pending transfers can be completed");
            }
            
            // Get all transfer items
            $stmt = $conn->prepare("
                SELECT product_id, variant_id, quantity 
                FROM inventory_transfer_items 
                WHERE transfer_id = ?
            ");
            $stmt->execute([$transfer_id]);
            $items = $stmt->fetchAll();
            
            // Process each item
            foreach ($items as $item) {
                // Remove from source warehouse
                $stmt = $conn->prepare("
                    UPDATE warehouse_stock 
                    SET quantity = quantity - ? 
                    WHERE warehouse_id = ? 
                    AND product_id = ? 
                    " . ($item['variant_id'] ? "AND variant_id = ?" : "AND variant_id IS NULL") . "
                ");
                
                $params = [$item['quantity'], $transfer['from_warehouse_id'], $item['product_id']];
                if ($item['variant_id']) {
                    $params[] = $item['variant_id'];
                }
                
                $stmt->execute($params);
                
                // Add to destination warehouse
                $stmt = $conn->prepare("
                    INSERT INTO warehouse_stock 
                    (warehouse_id, product_id, variant_id, quantity) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
                ");
                $stmt->execute([
                    $transfer['to_warehouse_id'],
                    $item['product_id'],
                    $item['variant_id'],
                    $item['quantity']
                ]);
                
                // Record inventory movements
                $current_time = date('Y-m-d H:i:s');
                
                // Outbound movement (negative)
                $stmt = $conn->prepare("
                    INSERT INTO inventory_movements 
                    (product_id, variant_id, warehouse_id, quantity, movement_type, reference_id, notes, user_id, created_at) 
                    VALUES (?, ?, ?, ?, 'transfer', ?, 'Transfer out', ?, ?)
                ");
                $stmt->execute([
                    $item['product_id'],
                    $item['variant_id'],
                    $transfer['from_warehouse_id'],
                    -$item['quantity'],
                    $transfer_id,
                    $_SESSION['employee_id'],
                    $current_time
                ]);
                
                // Inbound movement (positive)
                $stmt = $conn->prepare("
                    INSERT INTO inventory_movements 
                    (product_id, variant_id, warehouse_id, quantity, movement_type, reference_id, notes, user_id, created_at) 
                    VALUES (?, ?, ?, ?, 'transfer', ?, 'Transfer in', ?, ?)
                ");
                $stmt->execute([
                    $item['product_id'],
                    $item['variant_id'],
                    $transfer['to_warehouse_id'],
                    $item['quantity'],
                    $transfer_id,
                    $_SESSION['employee_id'],
                    $current_time
                ]);
            }
            
            // Update transfer status
            $stmt = $conn->prepare("
                UPDATE inventory_transfers 
                SET status = 'completed', completed_at = NOW(), completed_by = ? 
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['employee_id'], $transfer_id]);
            
            $success = "Transfer completed successfully";
        }

        $conn->commit();
        
        // Redirect to avoid form resubmission
        header("Location: transfers.php?id=$transfer_id&success=" . urlencode($success));
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Get current transfer details if editing
$transfer = null;
$transfer_items = [];
$products = [];

if ($transfer_id) {
    try {
        // Get transfer header
        $stmt = $conn->prepare("
        SELECT t.*, 
               fw.name as from_warehouse_name,
               tw.name as to_warehouse_name,
               uc.username as created_by_name,
               ua.username as completed_by_name
        FROM inventory_transfers t
        LEFT JOIN warehouses fw ON t.from_warehouse_id = fw.id
        LEFT JOIN warehouses tw ON t.to_warehouse_id = tw.id
        LEFT JOIN users uc ON t.created_by = uc.id
        LEFT JOIN users ua ON t.completed_by = ua.id
        WHERE t.id = ?
    ");
        $stmt->execute([$transfer_id]);
        $transfer = $stmt->fetch();
        
        if (!$transfer) {
            $error = "Transfer not found";
            $transfer_id = null;
        } else {
            // Get transfer items
            $stmt = $conn->prepare("
                SELECT ti.*, 
                       p.name as product_name,
                       p.product_code,
                       v.option_name as variant_option,
                       v.option_value as variant_value
                FROM inventory_transfer_items ti
                JOIN products p ON ti.product_id = p.id
                LEFT JOIN product_variants v ON ti.variant_id = v.id
                WHERE ti.transfer_id = ?
            ");
            $stmt->execute([$transfer_id]);
            $transfer_items = $stmt->fetchAll();
            
            // Get available products for the source warehouse
            $stmt = $conn->prepare("
                SELECT p.id, p.name, p.product_code 
                FROM warehouse_stock ws
                JOIN products p ON ws.product_id = p.id
                WHERE ws.warehouse_id = ? AND ws.quantity > 0
                ORDER BY p.name
            ");
            $stmt->execute([$transfer['from_warehouse_id']]);
            $products = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error = "Error fetching transfer details: " . $e->getMessage();
    }
}

// Get all pending/completed transfers for listing
$transfers = [];
try {
    $query = "
        SELECT t.*, 
               fw.name as from_warehouse_name,
               tw.name as to_warehouse_name,
               u.full_name as created_by_name,
               COUNT(ti.id) as item_count
        FROM inventory_transfers t
        JOIN warehouses fw ON t.from_warehouse_id = fw.id
        JOIN warehouses tw ON t.to_warehouse_id = tw.id
        JOIN employees u ON t.created_by = u.id
        LEFT JOIN inventory_transfer_items ti ON t.id = ti.transfer_id
        GROUP BY t.id
        ORDER BY t.created_at DESC
    ";
    $stmt = $conn->query($query);
    $transfers = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching transfers: " . $e->getMessage();
}
$page_title = 'Transfers';
$current_page = 'Transfers';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <?= $transfer_id ? 'Transfer #' . $transfer_id : 'Inventory Transfers' ?>
        </h1>
        <?php if (!$transfer_id): ?>
            <a href="transfers.php?action=create" class="d-none d-sm-inline-block btn btn-sm btn-primary">
                <i class="fas fa-exchange-alt"></i> New Transfer
            </a>
        <?php endif; ?>
    </div>

    <!-- Messages -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($action === 'create' || ($transfer_id && $transfer['status'] === 'pending')): ?>
        <!-- Transfer Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <?= $transfer_id ? 'Edit Transfer' : 'Create New Transfer' ?>
                </h6>
                <?php if ($transfer_id): ?>
                    <span class="badge bg-<?= $transfer['status'] === 'completed' ? 'success' : 'warning' ?>">
                        <?= ucfirst($transfer['status']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!$transfer_id): ?>
                    <!-- New Transfer Form -->
                    <form method="post">
                        <input type="hidden" name="create_transfer">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="from_warehouse_id" class="form-label">From Warehouse *</label>
                                <select class="form-control" id="from_warehouse_id" name="from_warehouse_id" required>
                                    <option value="">-- Select Source Warehouse --</option>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>">
                                            <?= htmlspecialchars($warehouse['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="to_warehouse_id" class="form-label">To Warehouse *</label>
                                <select class="form-control" id="to_warehouse_id" name="to_warehouse_id" required>
                                    <option value="">-- Select Destination Warehouse --</option>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>">
                                            <?= htmlspecialchars($warehouse['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Transfer
                        </button>
                        <a href="transfers.php" class="btn btn-primary">Cancel</a>
                    </form>
                <?php else: ?>
                    <!-- Existing Transfer Form -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>From Warehouse</label>
                                    <p class="form-control-static font-weight-bold">
                                        <?= htmlspecialchars($transfer['from_warehouse_name']) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>To Warehouse</label>
                                    <p class="form-control-static font-weight-bold">
                                        <?= htmlspecialchars($transfer['to_warehouse_name']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($transfer['notes']): ?>
                            <div class="form-group">
                                <label>Notes</label>
                                <p class="form-control-static"><?= htmlspecialchars($transfer['notes']) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Created By</label>
                                    <p class="form-control-static">
                                        <?= htmlspecialchars($transfer['created_by_name']) ?> on 
                                        <?= date('M j, Y H:i', strtotime($transfer['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($transfer['status'] === 'completed'): ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Completed By</label>
                                        <p class="form-control-static">
                                            <?= htmlspecialchars($transfer['completed_by_name']) ?> on 
                                            <?= date('M j, Y H:i', strtotime($transfer['completed_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Add Items Form -->
                    <?php if ($transfer['status'] === 'pending'): ?>
                        <form method="post" class="mb-4">
                            <input type="hidden" name="add_transfer_item">
                            <input type="hidden" name="transfer_id" value="<?= $transfer_id ?>">
                            
                            <div class="row">
                                <div class="col-md-5">
                                    <label for="product_id" class="form-label">Product *</label>
                                    <select class="form-control" id="product_id" name="product_id" required>
                                        <option value="">-- Select Product --</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?= $product['id'] ?>">
                                                <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['product_code']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="variant_id" class="form-label">Variant</label>
                                    <select class="form-control" id="variant_id" name="variant_id">
                                        <option value="">-- Select Variant --</option>
                                        <!-- Will be populated via AJAX -->
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="quantity" class="form-label">Quantity *</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                                </div>
                                
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <!-- Transfer Items Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Variant</th>
                                    <th>Quantity</th>
                                    <?php if ($transfer['status'] === 'pending'): ?>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transfer_items)): ?>
                                    <?php foreach ($transfer_items as $item): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($item['product_name']) ?>
                                                <small class="d-block text-muted"><?= htmlspecialchars($item['product_code']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($item['variant_option']): ?>
                                                    <?= htmlspecialchars($item['variant_option']) ?>: 
                                                    <?= htmlspecialchars($item['variant_value']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $item['quantity'] ?></td>
                                            <?php if ($transfer['status'] === 'pending'): ?>
                                                <td>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="delete_transfer_item" value="<?= $item['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-primary"
                                                                onclick="return confirm('Remove this item from transfer?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= $transfer['status'] === 'pending' ? '4' : '3' ?>" class="text-center">
                                            No items added to this transfer
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Transfer Actions -->
                    <?php if ($transfer['status'] === 'pending'): ?>
                        <div class="mt-4">
                            <?php if (!empty($transfer_items)): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="complete_transfer">
                                    <input type="hidden" name="transfer_id" value="<?= $transfer_id ?>">
                                    <button type="submit" class="btn btn-primary"
                                            onclick="return confirm('Complete this transfer? This will update inventory levels.')">
                                        <i class="fas fa-check"></i> Complete Transfer
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="transfers.php" class="btn btn-primary">Back to Transfers</a>
                        </div>
                    <?php else: ?>
                        <div class="mt-4">
                            <a href="transfers.php" class="btn btn-primary">Back to Transfers</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Transfers List -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Transfers</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="transfersTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transfers as $t): ?>
                                <tr>
                                    <td><?= $t['id'] ?></td>
                                    <td><?= htmlspecialchars($t['from_warehouse_name']) ?></td>
                                    <td><?= htmlspecialchars($t['to_warehouse_name']) ?></td>
                                    <td><?= $t['item_count'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $t['status'] === 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($t['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('M j, Y', strtotime($t['created_at'])) ?><br>
                                        <small><?= htmlspecialchars($t['created_by_name']) ?></small>
                                    </td>
                                    <td>
                                        <a href="transfers.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>



<?php require_once __DIR__ . '/includes/footer.php'; ?>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable for transfers list
    $('#transfersTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25
    });
    
    // Load variants when product is selected
 // In your JavaScript, modify the variant selection code:
$('#product_id').change(function() {
    const productId = $(this).val();
    if (!productId) {
        $('#variant_id').html('<option value="">-- Select Variant --</option>');
        return;
    }
    
    $.ajax({
        url: 'get_product_variants.php',
        method: 'GET',
        data: { 
            product_id: productId,
            warehouse_id: <?= $transfer ? $transfer['from_warehouse_id'] : 'null' ?>
        },
        success: function(data) {
            let options = '<option value="">-- No Variant --</option>';
            
            if (data.variants && data.variants.length > 0) {
                data.variants.forEach(function(variant) {
                    // Ensure variant.id exists and is valid
                    if (variant.id) {
                        options += `<option value="${variant.id}">${variant.option_name}: ${variant.option_value} (Stock: ${variant.quantity})</option>`;
                    }
                });
            }
            
            $('#variant_id').html(options);
        }
    });
});
    
    // Confirm before completing transfer
    $('form').submit(function() {
        if ($(this).find('button[name="complete_transfer"]').length) {
            return confirm('Are you sure you want to complete this transfer? This will update inventory levels.');
        }
        return true;
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