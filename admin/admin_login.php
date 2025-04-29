<?php
session_start();
include 'db.php';

// Define all available pages for privilege management
$all_pages = [
    'admin_dashboard' => 'Dashboard',
    'products' => 'Products',
    'categories' => 'Categories',
    'brands' => 'Brands',
    'suppliers' => 'Suppliers',
    'orders' => 'Orders',
    'invoices' => 'Invoices',
    'shipments' => 'Shipments',
    'transactions' => 'Transactions',
    'customers' => 'Customers',
    'customer_groups' => 'Customer Groups',
    'reviews' => 'Reviews',
    'inventory_dashboard' => 'Inventory Dashboard',
    'warehouses' => 'Warehouses',
    'movements' => 'Movements',
    'adjustments' => 'Stock Adjustments',
    'transfers' => 'Stock transfers',
    'inventory_alerts' => 'Inventory Alerts',
    'purchase_orders' => 'Purchase Orders',
    'promotion' => 'Promotions',
    'newrelease' => 'New Release',
    'coupons' => 'Coupons',
    'email_campaigns' => 'Email Campaigns',
    'abandoned' => 'Abandoned Cart',
    'loyalty' => 'Loyalty Program',
    'social_media' => 'Social Media',
    'settings' => 'Settings',
    'alerts_settings' => 'Alerts settings',
    'stores' => 'Store Information',
    'themes' => 'Themes',
    'links' => 'Links',
    'images' => 'Images',
    'payment_methods' => 'Payment Methods',
    'shipping_methods' => 'Shipping Methods',
    'delivery-options' => 'Delivery Options',
    'reports' => 'Reports',
    'privileges' => 'Employee Privileges'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    try {
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM employees WHERE username = :username AND is_active = 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $employee['password'])) {
                // Password is correct, set session variables
                $_SESSION['employee_logged_in'] = true;
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_username'] = $employee['username'];
                $_SESSION['employee_role'] = $employee['role'];
                $_SESSION['employee_full_name'] = $employee['full_name'];
                
                // Redirect based on role
                if ($employee['role'] == 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: manager.php");
                }
                exit();
            } else {
                $login_error = "Invalid username or password.";
            }
        } else {
            $login_error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $login_error = "Login failed. Please try again later.";
    }
}

$page_title = $page_title ?? 'Admin Login';
$current_page = $current_page ?? '';
$admin = $conn->query("SELECT * FROM admin LIMIT 1")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - E-commerce Store</title>
    <!-- Favicon -->
    <link rel="icon" href="assets/img/icon/favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/icon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/icon/apple-touch-icon.png">
    <link rel="manifest" href="assets/img/icon/site.webmanifest">
    <!-- Bootstrap 5 CSS (Latest Version) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="../includes/dynamic-styles.php">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="../assets/css/font-awesome.min.css">
    <style>
        body {
            direction: <?= ($_SESSION['lang'] == 'ar') ? 'rtl' : 'ltr'; ?>;
            text-align: <?= ($_SESSION['lang'] == 'ar') ? 'right' : 'left'; ?>;
            padding:0;
        }
        .login-container {
            max-width: 400px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header img {
            max-width: 150px;
            margin-bottom: 15px;
        }

        
   
    </style>
</head>
<body>
<header>
<div class="row bg-primary">
                    <div class="col-xl-12 col-lg-12 text-center text-lg-left">
                        <h2 class="text-white mt-4">Welcome to Promedia E-Store</h2>
                    </div>
                
                </div>
</header>
    <main>
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($page_title); ?></li>
                </ol>
            </nav>
            <h2 class="fw-bold">Employees Login</h2>
            <div class="login-container text-center">
                <div class="login-header">
                    <img src="images/logo.png" alt="Company Logo">
                   
                    <p>Please enter your credentials!</p>
                </div>
                <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer-section">
        
        <div class="copyright-area">
            <div class="container">
                <div class="row">
                    <div class="col-xl-6 col-lg-6 text-center text-lg-left">
                        <div class="copyright-text">
                            <p>&copy; <?php echo date('Y'); ?> <?php echo $admin['store_name'] ?? 'E-commerce Store'; ?>. All rights reserved.</p>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 d-none d-lg-block text-right">
                        <div class="footer-menu">
                            <a href="https://www.advancedpromedia.com">developed by Advanced Promedia Digital Marketing, UAE</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Bootstrap 5 JS and dependencies -->

</body>
</html>