<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Pagination settings
$items_per_page = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 10;
$current_page_number = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page_number - 1) * $items_per_page;

// Filter by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Base query
$query = "
    SELECT o.id, o.created_at, o.grand_total, o.payment_method, o.order_status,
           u.username, u.phone, u.email, 
           d.country, d.city, d.street, d.building_name
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN delivery_addresses d ON o.user_id = d.user_id
";

// Add status filter if not 'all'
if ($status_filter !== 'all') {
    $query .= " WHERE o.order_status = :status";
}

// Count query for pagination
$count_query = "
    SELECT COUNT(*) as total 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    JOIN delivery_addresses d ON o.user_id = d.user_id
";
if ($status_filter !== 'all') {
    $count_query .= " WHERE o.order_status = :status";
}

// Execute count query
$stmt = $conn->prepare($count_query);
if ($status_filter !== 'all') {
    $stmt->bindParam(':status', $status_filter);
}
$stmt->execute();
$total_invoices = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_invoices / $items_per_page);

// Add pagination to main query
$query .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";

// Execute main query
$stmt = $conn->prepare($query);
if ($status_filter !== 'all') {
    $stmt->bindParam(':status', $status_filter);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle sending invoice email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_invoice'])) {
    $order_id = $_POST['order_id'];
    $email = $_POST['email'];
    
    try {
        // Get order details
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->bindParam(':id', $order_id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception("Invoice not found");
        }
        
        // Get customer details
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $order['user_id']);
        $stmt->execute();
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get order items
        $stmt = $conn->prepare("
            SELECT oi.*, p.name as product_name, p.product_code 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :order_id
        ");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get delivery address
        $stmt = $conn->prepare("
            SELECT * FROM actual_addresses 
            WHERE order_id = :order_id
        ");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Email content
        $subject = "Your Invoice #" . $order_id;
        $message = "
            <h2>Invoice #" . $order_id . "</h2>
            <p>Date: " . date('F j, Y', strtotime($order['created_at'])) . "</p>
            
            <h3>Customer Information</h3>
            <p>Name: " . htmlspecialchars($customer['username']) . "</p>
            <p>Email: " . htmlspecialchars($customer['email']) . "</p>
            <p>Phone: " . htmlspecialchars($customer['phone']) . "</p>
            
            <h3>Delivery Address</h3>
            <p>" . htmlspecialchars($address['building_name'] . ', ' . $address['street']) . "</p>
            <p>" . htmlspecialchars($address['city'] . ', ' . $address['country']) . "</p>
            
            <h3>Order Items</h3>
            <table border='1' cellpadding='5'>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>";
        
        foreach ($items as $item) {
            $message .= "
                <tr>
                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                    <td>" . number_format($item['price'], 2) . "</td>
                    <td>" . $item['quantity'] . "</td>
                    <td>" . number_format($item['price'] * $item['quantity'], 2) . "</td>
                </tr>";
        }
        
        $message .= "
            </table>
            
            <h3>Order Summary</h3>
            <p>Subtotal: " . number_format($order['total_amount'], 2) . "</p>
            <p>Discount: -" . number_format($order['discount'], 2) . "</p>
            <p>Tax: " . number_format($order['tax_amount'], 2) . "</p>
            <p>Delivery: " . number_format($order['delivery_charges'], 2) . "</p>
            <p><strong>Total: " . number_format($order['grand_total'], 2) . "</strong></p>
            
            <p>Thank you for your order!</p>
        ";
        
        // Email headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: no-reply@yourdomain.com\r\n";
        
        // Send email
        if (mail($email, $subject, $message, $headers)) {
            $_SESSION['message'] = 'Invoice sent successfully to ' . htmlspecialchars($email);
            $_SESSION['message_type'] = 'success';
        } else {
            throw new Exception("Failed to send email");
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = "Error sending invoice: " . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    
    header("Location: invoices.php");
    exit();
}
$page_title = 'Invoices Management';
$current_page = 'invoices';
require_once __DIR__ . '/includes/header.php';
?>


        <!-- Page Content -->
        <div class="container-fluid px-4">
            <!-- Message Alert -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>

            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Invoices Management</h1>
                <div class="d-flex">
                    <form method="GET" class="me-3">
                        <div class="input-group">
                            <label class="input-group-text" for="status">Filter:</label>
                            <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Invoices</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                    </form>
                    <form method="GET" class="d-flex">
                        <div class="input-group">
                            <label class="input-group-text" for="items_per_page">Items:</label>
                            <select name="items_per_page" id="items_per_page" class="form-select" onchange="this.form.submit()">
                                <option value="10" <?php echo $items_per_page == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $items_per_page == 20 ? 'selected' : ''; ?>>20</option>
                                <option value="50" <?php echo $items_per_page == 50 ? 'selected' : ''; ?>>50</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice ID</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($invoice['id']); ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($invoice['username']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($invoice['email']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($invoice['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($default_currency); echo number_format($invoice['grand_total'], 2); ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?php 
                                                switch($invoice['order_status']) {
                                                    case 'completed': echo 'bg-success'; break;
                                                    case 'processing': echo 'bg-info'; break;
                                                    case 'shipped': echo 'bg-warning'; break;
                                                    case 'pending': echo 'bg-primary'; break;
                                                    case 'cancelled': echo 'bg-danger'; break;
                                                    case 'refunded': echo 'bg-secondary'; break;
                                                    default: echo 'bg-secondary';
                                                }
                                            ?>">
                                                <?php echo ucfirst($invoice['order_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo ucfirst($invoice['payment_method']); ?></td>
                                        <td>
                                            <div class="d-flex">
                                                <a href="invoices/<?php echo $invoice['id']; ?>.pdf" class="btn btn-sm btn-primary me-2" target="_blank">
                                                    <i class="fas fa-eye"></i> 
                                                 </a>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="order_id" value="<?php echo $invoice['id']; ?>">
                                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($invoice['email']); ?>">
                                                    <button type="submit" name="send_invoice" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-paper-plane"></i>                                                     </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page_number > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page_number - 1; ?>&items_per_page=<?php echo $items_per_page; ?>&status=<?php echo $status_filter; ?>">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $current_page_number ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&items_per_page=<?php echo $items_per_page; ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($current_page_number < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page_number + 1; ?>&items_per_page=<?php echo $items_per_page; ?>&status=<?php echo $status_filter; ?>">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
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