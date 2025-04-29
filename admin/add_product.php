<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle file uploads
function uploadFile($file, $targetDir = "images/") {
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true); // Create the directory if it doesn't exist
    }

    $fileName = basename($file['name']);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return $fileName;
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Upload main image
    $mainImage = uploadFile($_FILES['main_image']);
    $image2 = uploadFile($_FILES['image2']);
    $image3 = uploadFile($_FILES['image3']);
    $image4 = uploadFile($_FILES['image4']);

    // Prepare data for insertion
    $name = $_POST['name'];
    $description = $_POST['description'];
    $is_new = $_POST['is_new'];
    $sort_order = $_POST['sort_order'];
    $overview = $_POST['overview'];
    $price = $_POST['price'];
    $discount_percentage = $_POST['discount_percentage'];
    $product_code = $_POST['product_code'];
    $minimum_order = $_POST['minimum_order'];
    $max_order = $_POST['max_order'];
    $stock_limit = $_POST['stock_limit'];
    $min_stock = $_POST['min_stock'];
    $cost = $_POST['cost'];
    $weight = $_POST['weight'];
    $delivery_rate = $_POST['delivery_rate'];
    $delivery_duration = $_POST['delivery_duration'];
    $international_code = $_POST['international_code'];
    $volume = $_POST['volume'];
    $origin_country = $_POST['origin_country'];
    $category_id = $_POST['category_id'];
    $brand_id = $_POST['brand_id'];
    $supplier_id = $_POST['supplier_id'];
    $is_offer = isset($_POST['is_offer']) ? 1 : 0;
    $active = isset($_POST['active']) ? 1 : 0;
    $free_shipping = isset($_POST['free_shipping']) ? 1 : 0;
    $return_allowed = isset($_POST['return_allowed']) ? 1 : 0;
    $affiliate_link = $_POST['affiliate_link'];
   

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO products (
        name, description, price, discount_percentage, product_code, minimum_order, max_order, stock_limit, cost, weight,
        international_code, volume, origin_country, category_id, brand_id, supplier_id, min_stock,
        is_offer, active, free_shipping, return_allowed, affiliate_link, delivery_rate, delivery_duration, overview, is_new, sort_order,
        main_image, image2, image3, image4
    ) VALUES (
        :name, :description, :price, :discount_percentage, :product_code, :minimum_order, :max_order, :stock_limit, :cost, :weight,
        :international_code, :volume, :origin_country, :category_id, :brand_id, :supplier_id, :min_stock,
        :is_offer, :active, :free_shipping, :return_allowed, :affiliate_link, :delivery_rate, :delivery_duration, :overview, :is_new, :sort_order,
        :main_image, :image2, :image3, :image4
    )");

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':discount_percentage', $discount_percentage);
    $stmt->bindParam(':product_code', $product_code);
    $stmt->bindParam(':minimum_order', $minimum_order);
    $stmt->bindParam(':max_order', $max_order);
    $stmt->bindParam(':stock_limit', $stock_limit);
    $stmt->bindParam(':min_stock', $min_stock);
    $stmt->bindParam(':overview', $overview);
    $stmt->bindParam(':is_new', $is_new);
    $stmt->bindParam(':sort_order', $sort_order);
    $stmt->bindParam(':cost', $cost);
    $stmt->bindParam(':weight', $weight);
    $stmt->bindParam(':international_code', $international_code);
    $stmt->bindParam(':volume', $volume);
    $stmt->bindParam(':origin_country', $origin_country);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':brand_id', $brand_id);
    $stmt->bindParam(':supplier_id', $supplier_id);
    $stmt->bindParam(':is_offer', $is_offer);
    $stmt->bindParam(':active', $active);
    $stmt->bindParam(':free_shipping', $free_shipping);
    $stmt->bindParam(':return_allowed', $return_allowed);
    $stmt->bindParam(':affiliate_link', $affiliate_link);
    $stmt->bindParam(':delivery_rate', $delivery_rate);
    $stmt->bindParam(':delivery_duration', $delivery_duration);
    $stmt->bindParam(':main_image', $mainImage);
    $stmt->bindParam(':image2', $image2);
    $stmt->bindParam(':image3', $image3);
    $stmt->bindParam(':image4', $image4);

    if ($stmt->execute()) {
        header("Location: products.php");
        exit();
    } else {
        echo "Error: " . $stmt->errorInfo()[2];
    }
}
?>