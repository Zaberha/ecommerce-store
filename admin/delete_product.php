<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete images first
    $stmt = $conn->prepare("SELECT main_image, image2, image3, image4 FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    foreach (['main_image', 'image2', 'image3', 'image4'] as $image) {
        if (!empty($product[$image])) {
            unlink('images/' . $product[$image]);
        }
    }

    // Delete the product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: products.php");
    exit();
}
?>
