<?php
session_start(); // Start the session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate a successful payment
    $cardNumber = $_POST['card_number'];
    $expiryDate = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
    $cardholderName = $_POST['cardholder_name'];

    // Validate card details (demo validation)
    if (!empty($cardNumber) && !empty($expiryDate) && !empty($cvv) && !empty($cardholderName)) {
        // Payment successful
        $_SESSION['payment_success'] = true;
        header('Location: /order_confirmation.php');
        exit;
    } else {
        // Payment failed
        $_SESSION['payment_error'] = 'Invalid card details. Please try again.';
        header('Location: /demo_payment.php');
        exit;
    }
} else {
    // Redirect to checkout if the form is not submitted
    header('Location: /checkout.php');
    exit;
}
?>