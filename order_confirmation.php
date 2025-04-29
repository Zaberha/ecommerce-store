<?php
session_start(); // Start the session

// Check if the payment was successful
if (!isset($_SESSION['payment_success'])) {
    header('Location: /checkout.php'); // Redirect back to checkout if not
    exit;
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
    <p>Thank you for your purchase! Your payment was successful, and your order has been placed.</p>
    <p>You may view your order detail here <a href="order_details.php"></a></p>
    <a href="/" class="btn btn-primary">Continue Shopping</a>
</div>

<?php
// Clear the session variables
unset($_SESSION['payment_success']);
unset($_SESSION['order_placed']);
require_once __DIR__ . '/includes/footer.php';
?>