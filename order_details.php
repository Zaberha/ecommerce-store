<?php
session_start(); // Start the session

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
$user_id=$_SESSION['user_id'];
require_once __DIR__ . '/db.php';
// Get the order ID from the query string
$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    die("Order ID is missing.");
}

// Fetch order details from the orders table
try {
    $stmt = $conn->prepare("
        SELECT * FROM orders 
        WHERE id = :order_id AND user_id = :user_id
    ");
    $stmt->execute([
        ':order_id' => $orderId,
        ':user_id' => $_SESSION['user_id']
    ]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order not found or you do not have permission to view this order.");
    }
} catch (PDOException $e) {
    die("Error fetching order details: " . $e->getMessage());
}

// Fetch order items from the order_items table
try {
    $stmt = $conn->prepare("
        SELECT oi.product_id, oi.quantity, oi.price, p.name, p.main_image, p.is_offer, p.discount_percentage,p.product_code, p.overview
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id
    ");
    $stmt->execute([':order_id' => $orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching order items: " . $e->getMessage());
}

// Include the header
$page_title = 'Order Details';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Order Details</h1>

    <!-- Order Summary -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Order Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">

            <div class="col-md-3">
                <div class="card-body">

                
                <?php $theuser = $conn->query("SELECT * FROM profiles WHERE user_id =$user_id")->fetch(PDO::FETCH_ASSOC);?>
                <?php $addresses = $conn->query("SELECT * FROM actual_addresses WHERE order_id =$orderId")->fetch(PDO::FETCH_ASSOC);?>
                <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-left">
                    <b>Order ID:</b><span><?php echo htmlspecialchars($order['id']); ?></span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-left">
                    <b>Order Status:</b><span><?php echo htmlspecialchars($order['order_status']); ?></span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    <b>Order Date:</b><span><?php echo htmlspecialchars(date('Y-m-d', strtotime($order['created_at']))); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    <b>First Name:</b><span><?php echo htmlspecialchars($theuser['first_name']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    <b>Last Name:</b><span><?php echo htmlspecialchars($theuser['last_name']); ?></span>
                    </li>
                    
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                <b>City:</b><span><?php echo htmlspecialchars($addresses['city']); ?></span>
                    </li>
                    </ul>
                    </div>
                </div>
                <div class="col-md-5">
                <div class="card-body">

                <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-left">
                   <b>Phone:</b><span><?php echo htmlspecialchars($addresses['alternative_phone']); ?></span>
                    </li>


                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    <b>E-mail:</b><span><?php echo htmlspecialchars($theuser['email']); ?></span>
                    </li>



                    <li class="list-group-item d-flex justify-content-between align-items-center">
                <b>Country:</b><span><?php echo htmlspecialchars($addresses['country']); ?></span>
                    </li>  
                    


                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    <b>Street:</b><span><?php echo htmlspecialchars($addresses['street']); ?></span> 
                    </li> 
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    <b>Building:</b><span><?php echo htmlspecialchars($addresses['building_name']); ?></span>
                </li>  
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                  <b>Building No.:</b><span><?php echo htmlspecialchars($addresses['building_number']); ?></span>
                    <b>Floor No.:</b><span><?php echo htmlspecialchars($addresses['floor_number']); ?></span>
                    <b>Flat No.:</b><span><?php echo htmlspecialchars($addresses['flat_number']); ?></span>
                </li> 
                    </ul>
                    </div>
                </div>
                <div class="col-md-4">
                <div class="card-body">
                <ul class="list-group list-group-flush">


                <li class="list-group-item d-flex justify-content-between align-items-center">
                Total Amount:<span><?php echo $default_currency; ?><?php echo htmlspecialchars($order['total_amount']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    Discount:<span><?php echo '-'; echo $default_currency; ?><?php echo htmlspecialchars($order['discount']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    Tax Amount:<span><?php echo $default_currency; ?><?php echo htmlspecialchars($order['tax_amount']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    Coupon:<span><?php echo '-'; echo $default_currency; ?><?php echo htmlspecialchars($order['discount_by_code']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    Delivery Charges:<span><?php echo $default_currency; ?><?php echo htmlspecialchars($order['delivery_charges']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                    <b class="colored-second">Grand Total:</b><span class="colored-second"><b><?php echo $default_currency; ?><?php echo htmlspecialchars($order['grand_total']); ?></b></span>
                    </li>
                    </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Order Items -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Order Items</h5>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php foreach ($orderItems as $item): ?>
                <div class="list-group-item p-3">
                    <div class="row align-items-center">
                        <!-- Product Image and Info -->
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="d-flex align-items-start">
                                <img src="/admin/images/<?php echo htmlspecialchars($item['main_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="img-thumbnail me-3" 
                                     style="width: 100px; height: auto;">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($item['product_code']); ?></small>
                                    <p class="small mb-0"><?php echo htmlspecialchars($item['overview']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quantity -->
                        <div class="col-md-2 mb-2 mb-md-0">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-2 d-sm-none">Quantity:</span>
                                <span class="badge bg-success p-2" style="font-size:1.2rem;"><?php echo htmlspecialchars($item['quantity']); ?></span>
                            </div>
                        </div>
                        
                        <!-- Price -->
                        <div class="col-md-4 text-md-end">
                            <div>
                                <?php if ($item['is_offer'] && $item['discount_percentage'] > 0): ?>
                                    <span class="text-danger text-decoration-line-through me-2">
                                        <?php echo htmlspecialchars($default_currency); echo number_format($item['price'], 2); ?>
                                    </span>
                                    <span class="text-success fw-bold">
                                        <?php echo htmlspecialchars($default_currency); echo number_format($item['price'] * (1 - $item['discount_percentage'] / 100), 2); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="fw-bold">
                                        <?php echo htmlspecialchars($default_currency); echo number_format($item['price'], 2); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>

<?php
// Include the footer
require_once __DIR__ . '/includes/footer.php';?>
 <!-- Bootstrap 5 JS and dependencies -->
 <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
        // Pass the PHP variable to JavaScript
        const defaultCurrency = "<?php echo $default_currency; ?>";
    </script>
    <script src="assets/js/script.js"></script>
                        </body>
                        </html>