<?php
session_start(); // Start the session

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    die("Order ID is missing.");
}
$page_title = 'Order Confirmation';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
<nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Order Confirmation</li>
        </ol>
    </nav>
    <h2>Order Confirmation</h2>
    <p>Thank you for your purchase! , your order has been placed, delivery team will contact you soon</p>
    <p>You may view your order detail here  <a href="order_details.php?order_id=<?php echo htmlspecialchars($orderId); ?>">
    Order #<?php echo htmlspecialchars($orderId); ?></p>
    <a href="/" class="btn btn-primary">Continue Shopping</a>
</div>

<?php
// Clear the session variables
unset($_SESSION['payment_success']);
unset($_SESSION['order_placed']);
require_once __DIR__ . '/includes/footer.php';
?> 