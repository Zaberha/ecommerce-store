<?php
session_start();
require_once __DIR__ . '/db.php'; // Include your database connection



// Initialize variables
$order = null;
$orderItems = [];
$error = '';
$email = '';
$orderId = '';

// Handle redirect after POST to prevent resubmission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['track_form_data'] = $_POST;
    $_SESSION['track_order_result'] = [];
    
    $email = trim($_POST['email'] ?? '');
    $orderId = trim($_POST['order_id'] ?? '');
    
    try {
        if (!empty($orderId)) {
            // Search by order ID with all timestamp fields
            $stmt = $conn->prepare("
                SELECT o.*, 
                       a.street, a.building_name, a.building_number, a.city, a.country,
                       a.floor_number, a.alternative_phone, a.flat_number,
                       o.processed_at, o.picked_at, o.delivered_at, 
                       o.refunded_at, o.cancelled_at
                FROM orders o
                LEFT JOIN actual_addresses a ON o.id = a.order_id
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order) {
                // Get order items
                $stmt = $conn->prepare("
                    SELECT oi.*, p.name, p.main_image 
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$orderId]);
                $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error = 'No order found with that ID. Please check and try again.';
            }
        } elseif (!empty($email)) {
            // Search by email with all timestamp fields
            $stmt = $conn->prepare("
                SELECT o.*, 
                       a.street, a.building_name, a.building_number, a.city, a.country,
                       a.floor_number, a.alternative_phone, a.flat_number
                       o.processed_at, o.picked_at, o.delivered_at, 
                       o.refunded_at, o.cancelled_at
                FROM orders o
                JOIN profiles p ON o.user_id = p.user_id
                LEFT JOIN actual_addresses a ON o.id = a.order_id
                WHERE p.email = ?
                ORDER BY o.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$email]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order) {
                // Get order items
                $stmt = $conn->prepare("
                    SELECT oi.*, p.name, p.main_image 
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$order['id']]);
                $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!isset($_SESSION['user_id'])) {
                    $error = 'Please <a href="login.php" class="text-dark">login</a> to view all your orders.';
                }
            } else {
                $error = 'No orders found with that email. Please check and try again.';
            }
        } else {
            $error = 'Please enter either an order ID or your email address.';
        }
        
        $_SESSION['track_order_result'] = [
            'order' => $order,
            'orderItems' => $orderItems,
            'error' => $error
        ];
        
        $redirectUrl = $_SERVER['PHP_SELF'];
        if (!empty($orderId)) {
            $redirectUrl .= '?order_id=' . urlencode($orderId);
        } elseif (!empty($email)) {
            $redirectUrl .= '?email=' . urlencode($email);
        }
        
        header('Location: ' . $redirectUrl);
        exit;
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
        $_SESSION['track_order_result'] = ['error' => $error];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Check for redirected results
if (isset($_SESSION['track_order_result'])) {
    $result = $_SESSION['track_order_result'];
    $order = $result['order'] ?? null;
    $orderItems = $result['orderItems'] ?? [];
    $error = $result['error'] ?? '';
    
    // Get the original search values from session
    if (isset($_SESSION['track_form_data'])) {
        $email = $_SESSION['track_form_data']['email'] ?? '';
        $orderId = $_SESSION['track_form_data']['order_id'] ?? '';
    }
    
    unset($_SESSION['track_order_result']);
    unset($_SESSION['track_form_data']);
}

// Also check for direct access with query parameters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['order_id'])) {
        $orderId = trim($_GET['order_id']);
    } elseif (isset($_GET['email'])) {
        $email = trim($_GET['email']);
    }
}
$page_title = 'Track Your Order';
$current_page = 'track';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">

<nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><?php echo htmlspecialchars($page_title); ?></h2>
    
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-4 text-center fw-bold">Track Your Order</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <form method="post" class="mb-5" id="trackOrderForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="order_id" name="order_id" 
                                           placeholder="Order ID" value="<?= htmlspecialchars($orderId) ?>"
                                           data-other-field="email">
                                    <label for="order_id">Order Number</label>
                                </div>
                                <div class="text-center mt-2" id="orSeparator">
                                    <span class="text-muted small">- OR -</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Email" value="<?= htmlspecialchars($email) ?>"
                                           data-other-field="order_id">
                                    <label for="email">Your Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-3">Track Order</button>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($order): ?>
                        <!-- Order Summary -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h2 class="h5 mb-0">Order #<?= $order['id'] ?></h2>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h3 class="h6">Order Details</h3>
                                        <ul class="list-unstyled">
                                            <li><strong>Date:</strong> <?= date('F j, Y', strtotime($order['created_at'])) ?></li>
                                            <li><strong>Status:</strong> 
                                                <span class="badge bg-<?= 
                                                    $order['order_status'] === 'completed' ? 'success' : 
                                                    ($order['order_status'] === 'processing' ? 'info' : 
                                                    ($order['order_status'] === 'shipped' ? 'warning' :
                                                    ($order['order_status'] === 'refunded' ? 'secondary' :
                                                    ($order['order_status'] === 'cancelled' ? 'danger' : 'primary'))))
                                                ?>">
                                                    <?= ucfirst($order['order_status']) ?>
                                                </span>
                                            </li>
                                            <li><strong>Payment Method:</strong> <?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h3 class="h6">Order Total</h3>
                                        <ul class="list-unstyled">
                                            <li><strong>Subtotal:</strong> <?= $default_currency . number_format($order['total_amount'], 2) ?></li>
                                            <?php if ($order['discount'] > 0): ?>
                                                <li><strong>Discount:</strong> -<?= $default_currency . number_format($order['discount'], 2) ?></li>
                                            <?php endif; ?>
                                            <?php if ($order['discount_by_code'] > 0): ?>
                                                <li><strong>Coupon Discount:</strong> -<?= $default_currency . number_format($order['discount_by_code'], 2) ?></li>
                                            <?php endif; ?>
                                            <li><strong>Tax:</strong> <?= $default_currency . number_format($order['tax_amount'], 2) ?></li>
                                            <li><strong>Shipping:</strong> <?= $default_currency . number_format($order['delivery_charges'], 2) ?></li>
                                            <li class="fw-bold mt-2"><strong>Total:</strong> <?= $default_currency . number_format($order['grand_total'], 2) ?></li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <!-- Shipping Address -->
                                <div class="mb-4">
                                    <h3 class="h6">Shipping Address</h3>
                                    <address class="mb-0">
                                        <?= htmlspecialchars($order['street']) ?><br>
                                        <?php if (!empty($order['building_name'])): ?>
                                            <?= htmlspecialchars($order['building_name']) ?>, 
                                        <?php endif; ?>
                                        <?php if (!empty($order['building_number'])): ?>
                                            <?= htmlspecialchars($order['building_number']) ?><br>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['country']) ?><br>
                                        <?php if (!empty($order['floor_number'])): ?>
                                            Floor: <?= htmlspecialchars($order['floor_number']) ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($order['flat_number'])): ?>
                                            Flat No.: <?= htmlspecialchars($order['flat_number']) ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($order['alternative_phone'])): ?>
                                            Phone: <?= htmlspecialchars($order['alternative_phone']) ?>
                                        <?php endif; ?>
                                    </address>
                                </div>
                                
                                <!-- Order Items -->
                                <h3 class="h6 mb-3">Order Items</h3>
                                <div class="list-group">
                                    <?php foreach ($orderItems as $item): ?>
                                        <div class="list-group-item border-0 px-0 py-3">
                                            <div class="row align-items-center">
                                                <div class="col-3 col-md-2">
                                                    <img src="admin/images/<?= htmlspecialchars($item['main_image']) ?>" 
                                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                                         class="img-fluid rounded">
                                                </div>
                                                <div class="col-5 col-md-6">
                                                    <h4 class="h6 mb-1"><?= htmlspecialchars($item['name']) ?></h4>
                                                    <p class="small text-muted mb-0">Qty: <?= $item['quantity'] ?></p>
                                                </div>
                                                <div class="col-4 col-md-4 text-end">
                                                    <span class="fw-bold"><?= $default_currency . number_format($item['price'], 2) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="index.php" class="btn btn-outline-secondary">Continue Shopping</a>
                                    <?php if (isset($_SESSION['user_id'])): 
        // Store active tab in session
        $_SESSION['active_tab'] = 'orders'; ?>
                                        <a href="profile.php" class="btn btn-primary">All Orders</a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary">All Orders</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        



<!-- Delivery Status Timeline -->
<!-- Delivery Status Timeline -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="h6 mb-0">Order Status Timeline</h3>
    </div>
    <div class="card-body">
        <div class="timeline">
            <?php
            $statuses = [
                'pending' => ['icon' => 'fas fa-clock', 'color' => 'primary'],
                'processing' => ['icon' => 'fas fa-cog', 'color' => 'info'],
                'shipped' => ['icon' => 'fas fa-truck', 'color' => 'warning'],
                'completed' => ['icon' => 'fas fa-check-circle', 'color' => 'success'],
                'refunded' => ['icon' => 'fas fa-undo', 'color' => 'secondary'],
                'cancelled' => ['icon' => 'fas fa-times-circle', 'color' => 'danger']
            ];
            
            $currentStatus = $order['order_status'];
            $currentIndex = array_search($currentStatus, array_keys($statuses));
            ?>
            
            <?php foreach ($statuses as $status => $data): ?>
                <?php 
                $statusIndex = array_search($status, array_keys($statuses));
                $isCompleted = $statusIndex < $currentIndex;
                $isCurrent = $status === $currentStatus;
                
                // Determine which timestamp to use for each status
                $timestamp = '';
                $statusLabel = ucfirst($status);
                
                if ($status === 'pending' && !empty($order['created_at'])) {
                    $timestamp = $order['processed_at'];
                } elseif ($status === 'processing' && !empty($order['processed_at'])) {
                    $timestamp = $order['picked_at'];
                } elseif ($status === 'shipped' && !empty($order['picked_at'])) {
                    $timestamp = $order['picked_at'];
                } elseif ($status === 'completed' && !empty($order['actual_delivery'])) {
                    $timestamp = $order['actual_delivery'];
                } elseif ($status === 'refunded' && !empty($order['refunded_at'])) {
                    $timestamp = $order['refunded_at'];
                } elseif ($status === 'cancelled' && !empty($order['cancelled_at'])) {
                    $timestamp = $order['cancelled_at'];
                }
                ?>
                
                <div class="timeline-item <?= $isCompleted ? 'completed' : '' ?> <?= $isCurrent ? 'current' : '' ?>">
                    <div class="timeline-icon bg-<?= $data['color'] ?>">
                        <i class="<?= $data['icon'] ?> text-white"></i>
                    </div>
                    <div class="timeline-content">
                        <h4 class="h6 mb-1"><?= $statusLabel ?></h4>
                        <?php if ($isCompleted): ?>
                            <?php if (!empty($timestamp)): ?>
                                <p class="small text-muted mb-0">Completed on <?= date('M j, Y', strtotime($timestamp)) ?></p>
                            <?php else: ?>
                                <p class="small text-muted mb-0">Completed</p>
                            <?php endif; ?>
                        <?php elseif ($isCurrent): ?>
                            <p class="small text-muted mb-0">Currently at this stage</p>
                            <?php if ($status === 'shipped' && !empty($order['picked_at'])): ?>
                                <p class="small text-muted mb-0">Estimated delivery date <?= date('M j, Y', strtotime($order['delivered_at'])) ?></p>
                            <?php elseif ($status === 'completed' && !empty($order['delivered_at'])): ?>
                                <p class="small text-muted mb-0">Delivered on <?= date('M j, Y', strtotime($order['actual_delivery'])) ?></p>
                         
                            <?php elseif ($status === 'cancelled' && !empty($order['cancelled_at'])): ?>
                                <p class="small text-muted mb-0">Cancelled on <?= date('M j, Y', strtotime($order['cancelled_at'])) ?></p>
                                <?php elseif ($status === 'refunded' && !empty($order['refunded_at'])): ?>
                                    <p class="small text-muted mb-0">Refunded on <?= date('M j, Y', strtotime($order['refunded_at'])) ?></p>
                                <?php endif; ?>
                        <?php else: ?>
                            <p class="small text-muted mb-0">Pending</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<?php endif; ?>


                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 50px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 30px;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-icon {
        position: absolute;
        left: -50px;
        top: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .timeline-content {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
    }
    .timeline-item.completed .timeline-content {
        background: #e8f4fd;
    }
    .timeline-item.current .timeline-content {
        background: #d1e7ff;
        border-left: 3px solid var(--third-color);
    }
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -30px;
        top: 40px;
        width: 2px;
        height: 100%;
        background: #dee2e6;
    }
    .timeline-item.completed:before {
        background: var(--third-color);
    }
    .timeline-item:last-child:before {
        display: none;
    }

</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('trackOrderForm');
    const orderIdField = document.getElementById('order_id');
    const emailField = document.getElementById('email');
    const orSeparator = document.getElementById('orSeparator');
    
    function toggleFields() {
        if (orderIdField.value.trim() !== '') {
            emailField.disabled = true;
            emailField.closest('.col-md-6').style.opacity = '0.5';
            orSeparator.style.display = 'none';
        } else if (emailField.value.trim() !== '') {
            orderIdField.disabled = true;
            orderIdField.closest('.col-md-6').style.opacity = '0.5';
            orSeparator.style.display = 'none';
        } else {
            orderIdField.disabled = false;
            emailField.disabled = false;
            orderIdField.closest('.col-md-6').style.opacity = '1';
            emailField.closest('.col-md-6').style.opacity = '1';
            orSeparator.style.display = 'block';
        }
    }
    
    // Initialize on page load
    toggleFields();
    
    // Add event listeners
    orderIdField.addEventListener('input', toggleFields);
    emailField.addEventListener('input', toggleFields);
    
    // Clear the other field when one is focused
    [orderIdField, emailField].forEach(field => {
        field.addEventListener('focus', function() {
            const otherFieldName = this.getAttribute('data-other-field');
            const otherField = document.getElementById(otherFieldName);
            if (otherField.value.trim() !== '') {
                otherField.value = '';
                toggleFields();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>

    </body>
            </html>
