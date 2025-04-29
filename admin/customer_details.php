<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
// Check if customer ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: customers.php");
    exit();
}

$customer_id = (int)$_GET['id'];



// Fetch customer basic information with order stats
$customer_query = "
    SELECT 
        u.id, 
        u.email, 
        u.phone, 
        u.created_at AS registration_date,
        pr.first_name, 
        pr.last_name,
        pr.username,
        pr.address,
        da.country, 
        da.city, 
        da.street, 
        da.building_name,
        da.building_number,
        da.floor_number,
        da.flat_number,
        da.alternative_phone,
        cg.name AS customer_group,
        COUNT(o.id) AS order_count,
        COALESCE(SUM(o.grand_total), 0) AS total_spent,
        MAX(o.created_at) AS last_order_date
    FROM 
        users u
    LEFT JOIN 
        profiles pr ON u.id = pr.user_id
    LEFT JOIN 
        delivery_addresses da ON u.id = da.user_id
    LEFT JOIN
        customer_groups cg ON u.group_id = cg.id
    LEFT JOIN
        orders o ON u.id = o.user_id
    WHERE 
        u.id = :customer_id
    GROUP BY
        u.id
";

$stmt = $conn->prepare($customer_query);
$stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$stmt->execute();
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    header("Location: customers.php");
    exit();
}

// Fetch customer orders
$orders_query = "
    SELECT 
        o.id,
        o.total_amount,
        o.discount,
        o.payment_method,
        o.order_status,
        o.created_at,
        o.delivery_charges,
        o.tax_amount,
        o.discount_by_code,
        o.discount_code,
        o.grand_total,
        o.method,
        o.picked_at,
        o.delivered_at,
        o.actual_delivery,
        COUNT(oi.id) AS item_count
    FROM 
        orders o
    LEFT JOIN 
        order_items oi ON o.id = oi.order_id
    WHERE 
        o.user_id = :customer_id
    GROUP BY 
        o.id
    ORDER BY 
        o.created_at DESC
";

$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$orders_stmt->execute();
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch order items for each order
$order_items = [];
foreach ($orders as $order) {
    $items_query = "
        SELECT 
            oi.*,
            p.name AS product_name,
            p.main_image AS product_image
        FROM 
            order_items oi
        JOIN 
            products p ON oi.product_id = p.id
        WHERE 
            oi.order_id = :order_id
    ";
    
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bindParam(':order_id', $order['id'], PDO::PARAM_INT);
    $items_stmt->execute();
    $order_items[$order['id']] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch customer reviews
$reviews_query = "
    SELECT 
        r.*,
        p.name AS product_name,
        p.main_image AS product_image
    FROM 
        reviews r
    JOIN 
        products p ON r.product_id = p.id
    WHERE 
        r.user_id = :customer_id
    ORDER BY 
        r.created_at DESC
";

$reviews_stmt = $conn->prepare($reviews_query);
$reviews_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$reviews_stmt->execute();
$reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch customer cart items
$cart_query = "
    SELECT 
        c.*,
        p.name AS product_name,
        p.price,
        p.main_image AS product_image,
        p.stock_limit
    FROM 
        cart c
    JOIN 
        products p ON c.product_id = p.id
    WHERE 
        c.user_id = :customer_id
";

$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$cart_stmt->execute();
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch customer wishlist items
$wishlist_query = "
    SELECT 
        w.*,
        p.name AS product_name,
        p.price,
        p.main_image AS product_image
    FROM 
        wishlist w
    JOIN 
        products p ON w.product_id = p.id
    WHERE 
        w.user_id = :customer_id
";

$wishlist_stmt = $conn->prepare($wishlist_query);
$wishlist_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
$wishlist_stmt->execute();
$wishlist_items = $wishlist_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all customer groups for the dropdown
$groups_query = "SELECT id, name FROM customer_groups";
$groups_stmt = $conn->query($groups_query);
$customer_groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Customer Details';
$current_page = 'Customer Details';

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
        <nav class="navbar navbar-expand navbar-light bg-white top-navbar shadow mb-4">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="customers.php">Customers</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></li>
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
                            <li><a class="dropdown-item" href="privileges.php"><i class="fas fa-cog fa-fw"></i> Privileges</a></li>
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
                <h1 class="h3 mb-0 text-gray-800">Customer Details</h1>
                <div>
                    <a href="customers.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Customers
                    </a>
                </div>
            </div>

            <!-- Customer Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                                    </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Personal Information</h5>
                            <div class="mb-3">
                                <label class="fw-bold">Full Name:</label>
                                <p><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Username:</label>
                                <p><?php echo htmlspecialchars($customer['username'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Email:</label>
                                <p><?php echo htmlspecialchars($customer['email']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Phone:</label>
                                <p><?php echo htmlspecialchars($customer['phone']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Alternative Phone:</label>
                                <p><?php echo htmlspecialchars($customer['alternative_phone'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Registration Date:</label>
                                <p><?php echo date('M d, Y H:i', strtotime($customer['registration_date'])); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Customer Group:</label>
                                <p><?php echo htmlspecialchars($customer['customer_group'] ?? 'No Group'); ?></p>
                            </div>
                            <h5 class="mb-3">Order Statistics</h5>
                            <div class="mb-3">
                                <label class="fw-bold">Total Orders:</label>
                                <p><?php echo $customer['order_count']; ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Total Spent:</label>
                                <p><?php echo htmlspecialchars($default_currency); ?><?php echo number_format($customer['total_spent'], 2); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Last Order Date:</label>
                                <p><?php echo $customer['last_order_date'] ? date('M d, Y H:i', strtotime($customer['last_order_date'])) : 'No orders yet'; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">

                            <h5 class="mb-3 mt-4">Address Information</h5>
                            <div class="mb-3">
                                <label class="fw-bold">Country:</label>
                                <p><?php echo htmlspecialchars($customer['country'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">City:</label>
                                <p><?php echo htmlspecialchars($customer['city'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Street:</label>
                                <p><?php echo htmlspecialchars($customer['street'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Building Name:</label>
                                <p><?php echo htmlspecialchars($customer['building_name'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Building Number:</label>
                                <p><?php echo htmlspecialchars($customer['building_number'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Floor Number:</label>
                                <p><?php echo htmlspecialchars($customer['floor_number'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Flat Number:</label>
                                <p><?php echo htmlspecialchars($customer['flat_number'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order History</h6>
                </div>
                <div class="card-body">
                    <?php if (count($orders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo $order['item_count']; ?></td>
                                            <td><?php echo htmlspecialchars($default_currency); ?><?php echo number_format($order['grand_total'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    switch($order['order_status']) {
                                                        case 'completed': echo 'success'; break;
                                                        case 'processing': echo 'info'; break;
                                                        case 'shipped': echo 'warning'; break;
                                                        case 'pending': echo 'primary'; break;
                                                        case 'cancelled': echo 'danger'; break;
                                                        case 'refunded': echo 'secondary'; break;
                                                        default: echo 'light';
                                                    }
                                                ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo ucwords($order['payment_method']); ?></td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> 
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">This customer hasn't placed any orders yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Product Reviews</h6>
                </div>
                <div class="card-body">
                    <?php if (count($reviews) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Rating</th>
                                        <th>Review</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviews as $review): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="images/<?php echo htmlspecialchars($review['product_image']); ?>" alt="<?php echo htmlspecialchars($review['product_name']); ?>" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php echo htmlspecialchars($review['product_name']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i <= $review['stars'] ? ' text-warning' : ' text-secondary'; ?>"></i>
                                                <?php endfor; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($review['review_text'], 0, 50)); ?><?php echo strlen($review['review_text']) > 50 ? '...' : ''; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal<?php echo $review['id']; ?>">
                                                    <i class="fas fa-eye"></i> 
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">This customer hasn't submitted any reviews yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cart Items Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Cart Items</h6>
                </div>
                <div class="card-body">
                    <?php if (count($cart_items) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="images/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($default_currency); ?><?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo htmlspecialchars($default_currency); ?><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">This customer doesn't have any items in their cart.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Wishlist Items Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Wishlist Items</h6>
                </div>
                <div class="card-body">
                    <?php if (count($wishlist_items) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Date Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($wishlist_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="images/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($default_currency); ?><?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">This customer doesn't have any items in their wishlist.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>



    <!-- Review Modals -->
    <?php foreach ($reviews as $review): ?>
        <div class="modal fade" id="reviewModal<?php echo $review['id']; ?>" tabindex="-1" aria-labelledby="reviewModalLabel<?php echo $review['id']; ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reviewModalLabel<?php echo $review['id']; ?>">Review for <?php echo htmlspecialchars($review['product_name']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="fw-bold">Rating:</label>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?php echo $i <= $review['stars'] ? ' text-warning' : ' text-secondary'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Review:</label>
                            <p><?php echo htmlspecialchars($review['review_text']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Date:</label>
                            <p><?php echo date('M d, Y H:i', strtotime($review['created_at'])); ?></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
                // Initialize sidebar functionality
                initSidebar();   
        
    });

    function initSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        if (!sidebarToggle || !sidebar || !mainContent) return;

        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }

        function toggleSidebar() {
            const isOpen = !sidebar.classList.contains('active');
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
            sidebarToggle.setAttribute('aria-expanded', isOpen);
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    }

    </script>
</body>
</html>