<?php
session_start();
require_once 'db.php';
// Only admin can access this page
if ($_SESSION['employee_role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}
// Fetch total sales
$total_sales = $conn->query("
    SELECT SUM(oi.grand_total) AS total_sales
    FROM orders oi
    WHERE order_status = 'completed'
")->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

// Fetch total number of orders
$total_orders = $conn->query("SELECT COUNT(*) AS total_orders FROM orders WHERE order_status = 'completed'")->fetch(PDO::FETCH_ASSOC)['total_orders'] ?? 0;
$total_pending_orders = $conn->query("SELECT COUNT(*) AS total_orders FROM orders WHERE order_status = 'pending'")->fetch(PDO::FETCH_ASSOC)['total_orders'] ?? 0;

// Fetch top products (most ordered)
$top_products = $conn->query("
    SELECT p.name, SUM(oi.quantity) AS total_quantity
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Leaders Board
$top_users = $conn->query("
    SELECT username, points
    FROM users
    ORDER BY points DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch top cities (most orders)
$top_cities = $conn->query("
    SELECT d.city, COUNT(o.id) AS total_orders
    FROM orders o
    JOIN delivery_addresses d ON o.user_id = d.user_id
    GROUP BY d.city
    ORDER BY total_orders DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent orders using created_at
$recent_orders = $conn->query("
    SELECT o.id, o.created_at, o.grand_total, o.order_status, u.username
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch  products (at min stock ordered)
$min_products = $conn->query("
    SELECT name, main_image, stock_limit, min_stock
    FROM products
    WHERE stock_limit<= min_stock
 
")->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Dashboard';
$current_page = 'dashboard';

?>
<?php

// Fetch admin settings
$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
$stmt->execute();
$admin_settings = $stmt->fetch(PDO::FETCH_ASSOC);
$default_currency = $admin_settings['default_currency'];
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
        <nav class="navbar navbar-expand top-navbar shadow mb-4">
            <div class="container-fluid">
                <div class="d-flex align-items-center">

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Overview</li>
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
                            <li><a class="dropdown-item" href="privileges.php"><i class="fas fa-cog fa-fw"></i>privileges</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
                <a href="reports.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <!-- Total Sales Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card stat-card primary h-100 py-2 border-left-primary">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-third text-uppercase mb-1">
                                        Total Sales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo htmlspecialchars( $default_currency); echo number_format($total_sales, 2); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-third-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Orders Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card stat-card success h-100 py-2 border-left-success">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-second text-uppercase mb-1">
                                        Completed Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-second-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders Card -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card stat-card warning h-100 py-2 border-left-warning">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-first text-uppercase mb-1">
                                        Pending Orders</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_pending_orders; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-first-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Row -->
            <div class="row">
                <!-- Top Products -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-second">Top Selling Products</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-end">Quantity Sold</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_products as $product): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td class="text-end"><?php echo $product['total_quantity']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                                        <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-second">Leaders Board</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Username</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_users as $leader): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($leader['username']); ?></td>
                                                <td class="text-end"><?php echo $leader['points']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Cities -->
                <div class="col-xl-6 col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-second">Top Cities by Orders</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>City</th>
                                            <th class="text-end">Orders</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_cities as $city): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($city['city']); ?></td>
                                                <td class="text-end"><?php echo $city['total_orders']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-second">Products at Minimum Stock</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start">Product</th>
                                            <th class="text-center">Available</th>
                                            <th class="text-center">Min Stock</th>
                                            <th class="text-end">image</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($min_products  as $min): ?>
                                            <tr>
                                                <td class="text-start"><?php echo htmlspecialchars($min['name']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($min['stock_limit']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($min['min_stock']); ?></td>
                                                <td class="text-end"><img src="images/<?= htmlspecialchars($min['main_image']) ?>" alt="?php echo htmlspecialchars($min['name']); ?>" class="img-thumbnail"></td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>





                                <!-- Products under min stock -->
                                <div class="col-xl-6 col-lg-12">

                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-second">Recent Orders</h6>
                            <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars( $default_currency); echo number_format($order['grand_total'], 2); ?></td>
                                                <td>
                                                    <span class="badge rounded-pill <?php 
                                                        switch($order['order_status']) {
                                                            case 'completed': echo 'bg-success'; break;
                                                            case 'pending': echo 'bg-warning text-dark'; break;
                                                            case 'cancelled': echo 'bg-danger'; break;
                                                            default: echo 'bg-secondary';
                                                        }
                                                    ?>">
                                                        <?php echo ucfirst($order['order_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->
<script>
// Wait for DOM to be fully loaded
document.addEventListener("DOMContentLoaded", function() {
    // Initialize sidebar functionality only if elements exist
    initSidebar();
    // Initialize tooltips if any exist
    initTooltips();
});

function initSidebar() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    // Exit if essential elements don't exist
    if (!sidebarToggle || !sidebar || !mainContent) {
        console.warn('Sidebar elements not found - skipping sidebar initialization');
        return;
    }

    // Create overlay element
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    // Toggle sidebar function
    function toggleSidebar() {
        const isOpen = !sidebar.classList.contains('active');
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.classList.toggle('sidebar-open');
        sidebarToggle.setAttribute('aria-expanded', isOpen);
    }

    // Add event listeners
    sidebarToggle.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Close sidebar when pressing Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });
}

function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipElements.length > 0) {
        Array.from(tooltipElements).forEach(el => {
            new bootstrap.Tooltip(el);
        });
    }
}

</script>

</body>
</html>

