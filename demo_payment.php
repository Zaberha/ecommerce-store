<?php
session_start(); // Start the session

// Check if the user is redirected from the checkout page
if (!isset($_SESSION['order_placed'])) {
    header('Location: /checkout.php'); // Redirect back to checkout if not
    exit;
}
$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    die("Order ID is missing.");
}
$page_title = 'Demo E-Payment';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h2>Demo E-Payment</h2>
    <p>Please enter your card details to complete the payment.</p>

    <!-- Demo Payment Form -->
    <form id="demo-payment-form" action="/process_payment.php" method="POST">
        <div class="form-group">
            <label for="card-number">Card Number:</label>
            <input type="text" id="card-number" name="card_number" placeholder="1234 5678 9012 3456" required>
        </div>
        <div class="form-group">
            <label for="expiry-date">Expiry Date:</label>
            <input type="text" id="expiry-date" name="expiry_date" placeholder="MM/YY" required>
        </div>
        <div class="form-group">
            <label for="cvv">CVV:</label>
            <input type="text" id="cvv" name="cvv" placeholder="123" required>
        </div>
        <div class="form-group">
            <label for="cardholder-name">Cardholder Name:</label>
            <input type="text" id="cardholder-name" name="cardholder_name" placeholder="John Doe" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit Payment</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>