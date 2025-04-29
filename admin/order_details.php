<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
$page_title = 'Order Details';
$current_page = 'Orders';


// Get order ID from URL
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $order_id = $_POST['order_number'];
    $current_payment_status = $_POST['current_payment_status'] ?? '';
    $current_order_status = $order['order_status']; // Get current status from the fetched order
    
    try {
        // Initialize variables
        $should_update = false;
        $message = '';
        $message_type = 'info';
        $update_payment_status = false;
        $new_payment_status = '';
        
        // Check all validation rules
        if ($new_status == 'pending') {
            $message = 'You cannot make the order pending if already processed';
            $message_type = 'danger';
        }
        elseif ($new_status == 'processing' && $current_payment_status == 'pending') {
            $message = 'Cannot process order with pending payment';
            $message_type = 'danger';
        }
        elseif ($new_status == 'shipped') {
            $message = 'You have to select shipping method and pickup date below';
            $message_type = 'info';
        }
        elseif ($new_status == 'completed') {
            $message = 'You have to insert actual delivery date below';
            $message_type = 'info';
        }
        // Handle cancellation case
        elseif ($new_status == 'cancelled') {
            $should_update = true;
            $update_payment_status = true;
            $new_payment_status = 'cancelled';
            $message = 'Order status updated to cancelled';
            $message_type = 'success';
        }
        // Handle refund case
        elseif ($new_status == 'refunded') {
            if ($current_payment_status == 'received' || $current_payment_status == 'cancelled') {
                $should_update = true;
                $update_payment_status = true;
                $new_payment_status = 'returned';
                $message = 'Order status updated to refunded';
                $message_type = 'success';
            } else {
                $message = 'Cannot refund order with payment status: ' . $current_payment_status;
                $message_type = 'danger';
                header("Location: order_details.php?id=$order_id");
                exit();
            }
        }
        else {
            // Only these statuses will update the database directly
            $allowed_direct_updates = ['processing'];
            if (in_array($new_status, $allowed_direct_updates)) {
                $should_update = true;
                $message = 'Order status updated successfully';
                $message_type = 'success';
            }
        }
        
        // Only proceed with database update if validation passed
        if ($should_update) {
            $sql = "UPDATE orders SET order_status = :status";
            $params = [':status' => $new_status, ':id' => $order_id];
            
            // Add payment status update if needed
            if ($update_payment_status) {
                $sql .= ", payment_status = :payment_status";
                $params[':payment_status'] = $new_payment_status;
            }
            
            // Add timestamp updates based on status
            $current_datetime = date('Y-m-d H:i:s');
            
            if ($new_status == 'processing' && $current_payment_status != 'pending') {
                $sql .= ", processed_at = :processed_at";
                $params[':processed_at'] = $current_datetime;
            } 
            elseif ($new_status == 'cancelled') {
                $sql .= ", cancelled_at = :cancelled_at";
                $params[':cancelled_at'] = $current_datetime;
            } 
            elseif ($new_status == 'refunded') {
                $sql .= ", refunded_at = :refunded_at";
                $params[':refunded_at'] = $current_datetime;
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $conn->prepare($sql);
            
            // Bind all parameters
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            
            $stmt->execute();
        }
        
        // Set session message
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $message_type;
        
        header("Location: order_details.php?id=$order_id");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Error updating status: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: order_details.php?id=$order_id");
        exit();
    }
}
// Fetch order details

$stmt = $conn->prepare("
    SELECT o.*, u.username, u.email, w.name as warehouse_name,
           p.first_name, p.last_name, d.country, d.city, d.street, 
           d.building_name, d.building_number, d.floor_number, d.flat_number, d.alternative_phone
    FROM orders o
    LEFT JOIN warehouses w ON o.warehouse_id = w.id  
    JOIN users u ON o.user_id = u.id
    JOIN profiles p ON o.user_id = p.user_id
    JOIN actual_addresses d ON o.id = d.order_id
    WHERE o.id = :id
");


$stmt->bindParam(':id', $order_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Fetch order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name as product_name, p.main_image as product_image, p.product_code
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = :order_id
");
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Fetch warehouses
$warehouses = $conn->query("SELECT * FROM warehouses WHERE status = 1")->fetchAll(PDO::FETCH_ASSOC);

// Available statuses
$statuses = [
    'pending' => 'Pending',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
    'refunded' => 'Refunded'
];
// Handle shipping info update

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_shipping'])) {
    try {
        // Get form data
        $method = $_POST['shipping_method_id'];
        $warehouse_id = $_POST['warehouse_id'];
        $picked_at = $_POST['pickdate'];
        $delivered_at = $_POST['deliveredat'];
        
        // Validate dates
        if (strtotime($delivered_at) < strtotime($picked_at)) {
            $_SESSION['message'] = 'Delivery date must be after pickup date';
            $_SESSION['message_type'] = 'danger';
            header("Location: order_details.php?id=$order_id");
            exit();
        }

        // 1. Validate stock availability
        $stmt = $conn->prepare("
        SELECT 
            oi.product_id,
            oi.quantity,
            COALESCE(ws.quantity, 0) AS stock,
            p.name AS product_name
        FROM order_items oi
        LEFT JOIN warehouse_stock ws 
            ON ws.product_id = oi.product_id 
            AND ws.warehouse_id = :warehouse_id
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id
    ");
        $stmt->execute([
            ':warehouse_id' => $warehouse_id,
            ':order_id' => $order_id
        ]);
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            if ($item['stock'] < $item['quantity']) {
                throw new Exception("Insufficient stock for {$item['product_name']} (ID: {$item['product_id']}) 
                    in selected warehouse. Available: {$item['stock']}, Required: {$item['quantity']}");
            }
        }
        // 2. Update warehouse stock
        $stmt = $conn->prepare("
            UPDATE warehouse_stock 
            SET quantity = quantity - :quantity 
            WHERE product_id = :product_id 
            AND warehouse_id = :warehouse_id
        ");
        
        foreach ($items as $item) {
            $stmt->execute([
                ':quantity' => $item['quantity'],
                ':product_id' => $item['product_id'],
                ':warehouse_id' => $warehouse_id
            ]);
        }

        // 3. Record inventory movements
        $stmt = $conn->prepare("
            INSERT INTO inventory_movements 
            (product_id, warehouse_id, quantity, movement_type, reference_id, user_id)
            VALUES (:product_id, :warehouse_id, -:quantity, 'sale', :order_id, :user_id)
        ");
        
        foreach ($items as $item) {
            $stmt->execute([
                ':product_id' => $item['product_id'],
                ':warehouse_id' => $warehouse_id,
                ':quantity' => $item['quantity'],
                ':order_id' => $order_id,
                ':user_id' => $_SESSION['employee_id']
            ]);
        }

        // Update order status and shipping info
        $stmt = $conn->prepare("UPDATE orders SET 
            method = :method, 
            picked_at = :picked_at, 
            delivered_at = :delivered_at,
               warehouse_id = :warehouse_id,
            order_status = 'shipped'
            WHERE id = :order_id");
        
        $stmt->bindParam(':method', $method);
        $stmt->bindParam(':picked_at', $picked_at);
        $stmt->bindParam(':delivered_at', $delivered_at);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->bindParam(':warehouse_id', $warehouse_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Shipping information updated and order marked as shipped';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to update shipping information';
            $_SESSION['message_type'] = 'danger';
        }
        
        header("Location: order_details.php?id=$order_id");
        exit();
        
    } catch (Exception $e) { // Changed to catch general Exception
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: order_details.php?id=$order_id");
        exit();
    }
}

// Handle actual delivery completion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_delivery'])) {
    try {
        $actual_delivery = $_POST['actual_delivery'];
        
        // Validate date is not in future
        if (strtotime($actual_delivery) > time()) {
            $_SESSION['message'] = 'Actual delivery date cannot be in the future';
            $_SESSION['message_type'] = 'danger';
            header("Location: order_details.php?id=$order_id");
            exit();
        }
        
        // Update order with actual delivery date and mark as completed
        $stmt = $conn->prepare("UPDATE orders SET 
            actual_delivery = :actual_delivery,
            order_status = 'completed',
            payment_status = 'received'
            WHERE id = :order_id");
        
        $stmt->bindParam(':actual_delivery', $actual_delivery);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Order marked as completed with actual delivery date';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to update delivery information';
            $_SESSION['message_type'] = 'danger';
        }
        
        header("Location: order_details.php?id=$order_id");
        exit();
        
    } catch (PDOException $e) {
        $_SESSION['message'] = "Database error: " . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: order_details.php?id=$order_id");
        exit();
    }
}
?>
 <?php

// Fetch admin settings
$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
$stmt->execute();
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);
$default_currency = $admin_settings['default_currency'];




// Handle roll back request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['roll_back'])) {
    $order_id = $_POST['order_id'];
    
    try {
        $conn->beginTransaction();
        
        // A. Restore product quantities
        $stmt = $conn->prepare("
            SELECT oi.product_id, oi.quantity 
            FROM order_items oi 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        
        foreach ($items as $item) {
            $stmt = $conn->prepare("
                UPDATE products 
                SET stock_limit = stock_limit + ? 
                WHERE id = ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // B. Handle loyalty points if enabled
        $stmt = $conn->prepare("SELECT loyalty_program_enabled, loyalty_points_rate FROM admin LIMIT 1");
        $stmt->execute();
        $admin_settings = $stmt->fetch();
        
        if ($admin_settings['loyalty_program_enabled'] == 1) {
            // Get order total and customer email
            $stmt = $conn->prepare("SELECT grand_total, user_id FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();
            
            if ($order) {
                $points_to_deduct = $order['grand_total'] * $admin_settings['loyalty_points_rate'];
                
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET points = points - ? 
                    WHERE id = ?
                ");
                $stmt->execute([$points_to_deduct, $order['user_id']]);
            }
        }
        
        // C. Mark order as rolled back
        $stmt = $conn->prepare("
            UPDATE orders 
            SET rolled_flag = 1 
            WHERE id = ?
        ");
        $stmt->execute([$order_id]);
        
        $conn->commit();
        
        $_SESSION['message'] = 'Order successfully rolled back';
        $_SESSION['message_type'] = 'success';
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['message'] = 'Error rolling back order: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    
    header("Location: order_details.php?id=$order_id");
    exit();
}
?>
<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo $page_title; ?> - Admin Panel</title>
        
        <!-- Bootstrap 5 CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Dynamic Style - Local Style -->
            <link rel="stylesheet" href="css/dynamic-styles.php">
            <link rel="stylesheet" href="css/styles.css">
        
        <!-- Font Awesome -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
       
        <!-- Datepicker CSS -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <!-- DataTables CSS -->
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
            <script>
        // JavaScript to toggle submenus
                document.addEventListener("DOMContentLoaded", function () {
                const submenuToggles = document.querySelectorAll(".submenu-toggle");

                    submenuToggles.forEach((toggle) => {
                        toggle.addEventListener("click", function () {
                            const submenu = this.nextElementSibling;
                            submenu.style.display = submenu.style.display === "block" ? "none" : "block";
                            this.classList.toggle("active");
                        });
                    });
                });
            </script>
        </head>


        
<body>
    <!-- Sidebar Toggle -->
    <button class="btn btn-link d-md-none rounded-circle me-3 position-fixed top-0 start-0 mt-2 mb-2 ms-2 z-3" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay"></div>

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand navbar-light bg-white top-navbar shadow mb-4">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Order #<?php echo $order_id; ?></li>
                        </ol>
                    </nav>
                </div>
                
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle fa-fw"></i> <?php echo htmlspecialchars($_SESSION['employee_full_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-fw"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="privileges.php"><i class="fas fa-cog fa-fw"></i> Privileges</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Message Alert -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Order #<?php echo $order_id; ?></h1>
                <div class="d-flex">
                    <a href="orders.php" class="btn btn-primary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <form method="POST" class="d-flex">
                        <select name="status" class="form-select me-2" required>
                            <?php foreach ($statuses as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $order['order_status'] == $value ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="current_payment_status" value="<?= $order['payment_status'] ?>">
                        <input type="hidden" name="order_number" value="<?= $order['id'] ?>">
                        <?php if ($order['rolled_flag'] != 1): ?>
                        <button type="submit" name="update_status" class="btn btn-primary" style="width:300px;">
                            <i class="fas fa-sync-alt"></i> Update Status
                        </button>
                        <?php elseif ($order['rolled_flag'] == 1): ?>
                            <button type="submit" name="update_status" class="btn btn-secondary" style="width:300px;" disabled>
                            <i class="fas fa-sync-alt"></i> Update Status
                        </button>
                            <?php endif; ?>
                    </form>

                    <?php if (($order['payment_status'] == 'cancelled' || $order['payment_status'] == 'returned') && $order['rolled_flag'] != 1): ?>
    <form method="post" class="d-inline">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <button type="submit" name="roll_back" class="btn btn-primary mx-2 pb-2" 
                onclick="return confirm('Are you sure you want to roll back this order? This will restore product quantities and adjust loyalty points.')">
            <i class="fas fa-undo"></i> Roll Back
        </button>
    </form>
<?php elseif ($order['rolled_flag'] == 1): ?>
    <button class="btn btn-secondary mx-2" disabled>
        <i class="fas fa-undo"></i> Already Rolled Back
    </button>
<?php endif; ?>
                </div>
            </div>

            <!-- Order Summary Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-second">Order Summary</h6>
                    <span class="badge rounded-pill <?php 
                        switch($order['order_status']) {
                            case 'completed': echo 'bg-success'; break;
                            case 'processing': echo 'bg-info'; break;
                            case 'shipped': echo 'bg-warning'; break;
                            case 'pending': echo 'bg-primary'; break;
                            case 'cancelled': echo 'bg-danger'; break;
                            case 'refunded': echo 'bg-secondary'; break;
                            default: echo 'bg-secondary';
                        }
                    ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>

                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Order Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Order ID:</th>
                                    <td>#<?php echo $order_id; ?></td>
                                </tr>
                                <tr>
                                    <th>Order Date:</th>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td><?php echo ucfirst($order['payment_method']); ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Status:</th>
                                    <td><?php echo ucfirst($order['payment_status']); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td><?php echo htmlspecialchars( $default_currency); echo number_format($order['grand_total'], 2); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Customer Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th>Customer:</th>
                                    <td><?php echo htmlspecialchars($order['first_name']. ' ' . $order['last_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td><?php echo htmlspecialchars($order['alternative_phone']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Shipping Address</h5>
                            <address>
                            
                                Building Name: <?php echo htmlspecialchars($order['building_name']); ?>, 
                                <?php if (!empty($order['building_number'])): ?>
                                    Building No. <?php echo htmlspecialchars($order['building_number']); ?>, 
                                    Floor No. <?php echo htmlspecialchars($order['floor_number']); ?><br>
                                    Flat No. <?php echo htmlspecialchars($order['flat_number']); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($order['street']); ?><br>
                                <?php echo htmlspecialchars($order['city'] . ', ' . $order['country']); ?><br>
                                <?php if (!empty($order['additional_info'])): ?>
                                    <strong>Additional Info:</strong> <?php echo htmlspecialchars($order['additional_info']); ?>
                                <?php endif; ?>
                            </address>
                        </div>


                        <div class="col-md-6">
                            <h5>Shipping Information</h5>
                            <?php if (!empty($order['picked_at'])): ?>
                                <strong>Agent:</strong> <?php echo htmlspecialchars($order['method']); ?><br/>
                                    <strong>Pick up Date:</strong> <?php echo date('d-m-Y ', strtotime($order['picked_at'])); ?><br/>
                                    <strong>Estimated Delivery Date:</strong> <?php echo date('d-m-Y ', strtotime($order['delivered_at'])); ?><br/>
                                    <?php endif; ?>

                                    <?php if (!empty($order['actual_delivery'])): ?>
                                    <strong>Actual Delivery Date:</strong> <?php echo date('d-m-Y ', strtotime($order['actual_delivery']));?>
                                    <?php endif; ?>
                                    <?php if (!empty($order['cancelled_at'])): ?>
                                    <br/><strong>Cancellation Date:</strong> <?php echo date('d-m-Y ', strtotime($order['cancelled_at'])); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($order['refunded_at'])): ?>
                                    <br/><strong>Refund Date:</strong> <?php echo date('d-m-Y ', strtotime($order['refunded_at'])); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($order['warehouse_id'])): ?>
  
    <strong>Fulfillment Warehouse:</strong><?= htmlspecialchars($order['warehouse_name'] ?? 'N/A') ?>

<?php endif; ?>



                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-second">Order Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['product_image'])): ?>
                                                    <img src="images/<?php echo htmlspecialchars($item['product_image']); ?>" class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                    <small class="text-muted">SKU: <?php echo htmlspecialchars($item['product_code']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars( $default_currency); echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo htmlspecialchars( $default_currency); echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            
                                <tr>
                                    <th colspan="3" class="text-end">MRP Price:</th>
                                    <td>$<?php echo number_format($order['total_amount'] , 2); ?></td>
                                </tr>
                                
                                <tr>
                                        <th colspan="3" class="text-end">Discount:</th>
                                        <td>-<?php echo htmlspecialchars( $default_currency); echo number_format($order['discount'], 2); ?></td>
                                    </tr>
                                    <tr>
                                    <th colspan="3" class="text-end">Discounted Price:</th>
                                    <td><?php echo htmlspecialchars( $default_currency); echo number_format($order['total_amount'] - ($order['discount'] ?? 0), 2); ?></td>
                                </tr>
                                <tr>
                                        <th colspan="3" class="text-end">Tax:</th>
                                        <td><?php echo htmlspecialchars( $default_currency); echo number_format($order['tax_amount'], 2); ?></td>
                                    </tr>
                                    <tr>
                                    <th colspan="3" class="text-end">Delivery Charges:</th>
                                    <td><?php echo htmlspecialchars( $default_currency); echo number_format(($order['delivery_charges'] ?? 0), 2); ?></td>
                                </tr>
                            
                                <?php if (($order['discount_code'])<> NULL): ?>
                                    <tr>
                                        <th colspan="3" class="text-end">Coupon:(<?php echo ($order['discount_code']); ?>)</th>
                                        <td>-<?php echo htmlspecialchars( $default_currency); echo number_format($order['discount_by_code'], 2); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th colspan="3" class="text-end" style="background-color: var(--main-color-light); font-weight:bold;">Total:</th>
                                    <td class="colored" style="background-color: var(--main-color-light); font-weight:bold;"><?php echo htmlspecialchars( $default_currency); echo number_format($order['grand_total'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

 <!-- Shipping Information Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-second">Shipping Information</h6>
    </div>
    <div class="card-body">
    <?php if(empty($order['picked_at'])): ?>
        <!-- Form for initial shipping information -->
        <form method="POST">

        <div class="mb-3">
            <label for="warehouse" class="form-label">Select Warehouse</label>
            <select class="form-select" id="warehouse" name="warehouse_id" required>
                <option value="">-- Select Warehouse --</option>
                <?php foreach ($warehouses as $warehouse): ?>
                    <option value="<?= $warehouse['id'] ?>" 
                        <?= ($order['warehouse_id'] ?? '') == $warehouse['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($warehouse['name']) ?> - <?= htmlspecialchars($warehouse['location']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


            <div class="mb-3">
                <label for="shippingMethodSelect" class="form-label">Shipping Method</label>
                <select class="form-select" id="shippingMethodSelect" name="shipping_method_id" required>
                    <option value="">-- Select Shipping Method --</option>
                    <?php
                    $shipping_options = $conn->query("SELECT id, name FROM shipping_methods ORDER BY name");
                    while ($option = $shipping_options->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($order['method'] ?? '') == $option['name'] ? 'selected' : '';
                        echo '<option value="'.htmlspecialchars($option['name']).'" '.$selected.'>'.htmlspecialchars($option['name']).'</option>';
                    }
                    ?>
                </select>
            </div>
           
            <div class="mb-3">
                <label for="pickdate" class="form-label">Picked At</label>
                <input class="form-control" id="pickdate" name="pickdate" type="date" 
                    value="<?php echo htmlspecialchars($order['picked_at'] ?? ''); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="deliveredat" class="form-label">Estimated Delivery Date</label>
                <input class="form-control" id="deliveredat" name="deliveredat" type="date" 
                    value="<?php echo htmlspecialchars($order['delivered_at'] ?? ''); ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary" name="add_shipping">Save Shipping Info</button>
        </form>
    <?php elseif(empty($order['actual_delivery'])): ?>
        <!-- Show shipping info and form for actual delivery date -->
        <div class="alert alert-info mb-3">
            Order is "shipped", Items are sent to delivery Company.
        </div>
        
        <div class="mb-3">
            <label class="form-label">Shipping Method:</label>
            <p class="form-control-static"><?php echo htmlspecialchars($order['method']); ?></p>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Picked At:</label>
            <p class="form-control-static"><?php echo htmlspecialchars($order['picked_at']); ?></p>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Estimated Delivery:</label>
            <p class="form-control-static"><?php echo htmlspecialchars($order['delivered_at']); ?></p>
        </div>
        
        <form method="POST">
            <div class="mb-3">
                <label for="actual_delivery" class="form-label">Actual Delivery Date</label>
                <input class="form-control" id="actual_delivery" name="actual_delivery" type="date" required>
            </div>
            
            <button type="submit" class="btn btn-primary" name="complete_delivery">Mark as Delivered</button>
        </form>
    <?php else: ?>
        <!-- Show completed order info -->
        <div class="alert alert-success mb-3">
            Order is completed, item delivered!
        </div>
        
        <div class="mb-3">
            <label class="form-label">Shipping Method:</label>
            <p class="form-control-static"><?php echo htmlspecialchars($order['method']); ?></p>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Picked At:</label>
            <p class="form-control-static"><?php echo htmlspecialchars($order['picked_at']); ?></p>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Estimated Delivery:</label>
            <p class="form-control-static"><?php echo htmlspecialchars($order['delivered_at']); ?></p>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Actual Delivery:</label>
            <p class="form-control-static"><?php echo htmlspecialchars($order['actual_delivery']); ?></p>
        </div>
    <?php endif; ?>
</div>
</div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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