<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$order = [
    'supplier_id' => '',
    'warehouse_id' => '',
    'order_number' => generateOrderNumber(),
    'order_date' => date('Y-m-d'),
    'expected_delivery_date' => '',
    'status' => 'draft',
    'subtotal' => 0,
    'tax_amount' => 0,
    'discount_amount' => 0,
    'total_amount' => 0,
    'notes' => ''
];

$items = [];
$suppliers = $conn->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();
$warehouses = $conn->query("SELECT id, name FROM warehouses WHERE status = 1 ORDER BY name")->fetchAll();

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM purchase_orders WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    
    $stmt = $conn->prepare("SELECT poi.*, p.name as product_name 
                           FROM purchase_order_items poi
                           JOIN products p ON poi.product_id = p.id
                           WHERE purchase_order_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order = $_POST['order'];
    $items = $_POST['items'] ?? [];
    
    try {
        $conn->beginTransaction();
        
        // Save order
        if ($id) {
            $stmt = $conn->prepare("UPDATE purchase_orders SET 
                supplier_id = ?, warehouse_id = ?, order_number = ?, 
                order_date = ?, expected_delivery_date = ?, status = ?, 
                subtotal = ?, tax_amount = ?, discount_amount = ?, 
                total_amount = ?, notes = ?
                WHERE id = ?");
            $params = [...array_values($order), $id];
        } else {
            $stmt = $conn->prepare("INSERT INTO purchase_orders 
                (supplier_id, warehouse_id, order_number, order_date, 
                expected_delivery_date, status, subtotal, tax_amount, 
                discount_amount, total_amount, notes, created_by) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $params = [...array_values($order),  $_SESSION['employee_id']];
        }
        $stmt->execute($params);
        
        $orderId = $id ?: $conn->lastInsertId();
        
        // Save items
        $conn->prepare("DELETE FROM purchase_order_items WHERE purchase_order_id = ?")
             ->execute([$orderId]);
        
        foreach ($items as $item) {
            $item['total_price'] = $item['total_price'] ?? 0;
    
    // Calculate if needed (recommended)
    if (!isset($item['total_price']) || empty($item['total_price'])) {
        $quantity = (float)($item['quantity'] ?? 0);
        $cost = (float)($item['cost_price'] ?? 0);
        $tax = (float)($item['tax_rate'] ?? 0);
        $item['total_price'] = $quantity * $cost * (1 + ($tax / 100));
    }
    
    // Force numeric value
    $item['total_price'] = (float)$item['total_price'];
            $stmt = $conn->prepare("INSERT INTO purchase_order_items 
                (purchase_order_id, product_id, quantity, cost_price, tax_rate, total_price)
                VALUES (?,?,?,?,?,?)");
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['cost_price'],
                $item['tax_rate'],
                $item['total_price']
            ]);
        }
        
        $conn->commit();
        header("Location: po_view.php?id=$orderId");
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error saving order: " . $e->getMessage();
    }
}

function generateOrderNumber() {
    return 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}
$page_title = 'AD Edit PO';
$current_page = 'Add Edit PO';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <?= $id ? "Edit Purchase Order" : "Create Purchase Order" ?>
        </h1>
        <div>
            <a href="purchase_orders.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <form method="post">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Supplier *</label>
                                    <select name="order[supplier_id]" class="form-control" required>
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($suppliers as $s) : ?>
                                            <option value="<?= $s['id'] ?>" 
                                                <?= $order['supplier_id'] == $s['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Warehouse</label>
                                    <select name="order[warehouse_id]" class="form-control">
                                        <option value="">Select Warehouse</option>
                                        <?php foreach ($warehouses as $w) : ?>
                                            <option value="<?= $w['id'] ?>" 
                                                <?= $order['warehouse_id'] == $w['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($w['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="mb-4">
                            <h5>Order Items</h5>
                            <div id="items-container">
                                <?php foreach ($items as $index => $item) : ?>
                                    <div class="item-row mb-3">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <select name="items[<?= $index ?>][product_id]" class="form-control" required>
                                                    <option value="">Select Product</option>
                                                    <?php 
                                                    $products = $conn->query("SELECT id, name FROM products")->fetchAll();
                                                    foreach ($products as $p) : ?>
                                                        <option value="<?= $p['id'] ?>" 
                                                            <?= $item['product_id'] == $p['id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($p['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" name="items[<?= $index ?>][quantity]" 
                                                    class="form-control" placeholder="Qty" 
                                                    value="<?= $item['quantity'] ?>" step="1" min="1" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" name="items[<?= $index ?>][cost_price]" 
                                                    class="form-control" placeholder="Cost" 
                                                    value="<?= $item['cost_price'] ?>" step="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" name="items[<?= $index ?>][tax_rate]" 
                                                    class="form-control" placeholder="Tax %" 
                                                    value="<?= $item['tax_rate'] ?>" step="0.01">
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger remove-item">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" name="items[<?= $index ?>][total_price]" 
                   value="<?= htmlspecialchars($item['total_price']) ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-item" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Order Number *</label>
                            <input type="text" name="order[order_number]" 
                                class="form-control" value="<?= $order['order_number'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Order Date *</label>
                            <input type="date" name="order[order_date]" 
                                class="form-control" value="<?= $order['order_date'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Expected Delivery Date</label>
                            <input type="date" name="order[expected_delivery_date]" 
                                class="form-control" value="<?= $order['expected_delivery_date'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select name="order[status]" class="form-control" required>
                                <?php foreach (['draft', 'ordered'] as $s) : ?>
                                    <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>Subtotal</label>
                            <input type="number" name="order[subtotal]" 
                                class="form-control" value="<?= $order['subtotal'] ?>" step="0.01" readonly>
                        </div>
                        <div class="form-group">
                            <label>Tax Amount</label>
                            <input type="number" name="order[tax_amount]" 
                                class="form-control" value="<?= $order['tax_amount'] ?>" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Discount</label>
                            <input type="number" name="order[discount_amount]" 
                                class="form-control" value="<?= $order['discount_amount'] ?>" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Total Amount</label>
                            <input type="number" name="order[total_amount]" 
                                class="form-control" value="<?= $order['total_amount'] ?>" step="0.01" readonly>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="order[notes]" class="form-control"><?= $order['notes'] ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            Save Purchase Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
<!-- Bootstrap Bundle with Popper -->
<script>
// Consolidated JavaScript code
document.addEventListener('DOMContentLoaded', function() {
    console.log('Purchase Order Form Initialized');
    
    // Get products data from PHP
    const products = <?= json_encode($conn->query("SELECT id, name FROM products")->fetchAll()) ?>;
    console.log('Loaded products:', products);

    // HTML escaping helper
    const escapeHtml = text => {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    };

    // Item index management
    let itemIndex = <?= count($items) ?>;
    console.log('Initial item index:', itemIndex);

    // Add Item Functionality
    const addButton = document.getElementById('add-item');
    if (addButton) {
        addButton.addEventListener('click', function() {
            console.log('Add item button clicked, current index:', itemIndex);
            
            // Create product options
            const options = products.map(p => 
                `<option value="${p.id}">${escapeHtml(p.name)}</option>`
            ).join('');

            // Create new item template
            const template = `
                <div class="item-row mb-3">
                    <div class="row">
                        <div class="col-md-5">
                            <select name="items[${itemIndex}][product_id]" class="form-control" required>
                                <option value="">Select Product</option>
                                ${options}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" 
                                   name="items[${itemIndex}][quantity]" 
                                   class="form-control" 
                                   placeholder="Qty" 
                                   step="1" 
                                   min="1" 
                                   required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" 
                                   name="items[${itemIndex}][cost_price]" 
                                   class="form-control" 
                                   placeholder="Cost" 
                                   step="0.01" 
                                   required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" 
                                   name="items[${itemIndex}][tax_rate]" 
                                   class="form-control" 
                                   placeholder="Tax %" 
                                   step="0.01">
                            <input type="hidden" 
                                   name="items[${itemIndex}][total_price]" 
                                   value="0">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-item">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>`;

            // Insert new item
            const container = document.getElementById('items-container');
            if (container) {
                container.insertAdjacentHTML('beforeend', template);
                itemIndex++;
                console.log('New item added, index now:', itemIndex);
            }
        });
    }

    // Remove Item Functionality
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const row = e.target.closest('.item-row');
            if (row) {
                console.log('Removing item row');
                row.remove();
                calculateTotals();
            }
        }
    });

    // Auto-calculation functionality (fixed syntax)
    function calculateTotals() {
        let subtotal = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const qtyInput = row.querySelector('[name$="[quantity]"]');
            const costInput = row.querySelector('[name$="[cost_price]"]');
            const taxInput = row.querySelector('[name$="[tax_rate]"]');
            const totalInput = row.querySelector('[name$="[total_price]"]');

            const qty = parseFloat(qtyInput?.value) || 0;
            const cost = parseFloat(costInput?.value) || 0;
            const taxRate = parseFloat(taxInput?.value) || 0;
            
            const total = qty * cost * (1 + taxRate / 100);
            subtotal += total;

            if (totalInput) {
                totalInput.value = total.toFixed(2);
            }
        });

        // Fixed syntax for tax amount parsing
        const taxAmount = parseFloat(document.querySelector('[name="order[tax_amount]"]')?.value) || 0;
        const discountAmount = parseFloat(document.querySelector('[name="order[discount_amount]"]')?.value) || 0;
        const totalAmount = subtotal + taxAmount - discountAmount;

        document.querySelector('[name="order[subtotal]"]').value = subtotal.toFixed(2);
        document.querySelector('[name="order[total_amount]"]').value = totalAmount.toFixed(2);
    }

    // Event listeners
    document.getElementById('items-container')?.addEventListener('input', calculateTotals);
    document.querySelector('[name="order[tax_amount]"]')?.addEventListener('input', calculateTotals);
    document.querySelector('[name="order[discount_amount]"]')?.addEventListener('input', calculateTotals);

    // Initial calculation
    calculateTotals();

    // Sidebar initialization
    function initSidebar() {
        // ... [your existing sidebar code] ...
    }
    initSidebar();
});
</script>
</body>
</html>