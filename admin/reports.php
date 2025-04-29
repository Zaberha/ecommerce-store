<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Set default timezone
date_default_timezone_set('Asia/Dubai');

// Export functionality
if (isset($_GET['export'])) {
    $report_type = $_GET['report'];
    $format = $_GET['format'];
    
    switch ($report_type) {
        case 'sales':
            exportSalesReport($format);
            break;
        case 'products':
            exportProductsReport($format);
            break;
        case 'customers':
            exportCustomersReport($format);
            break;
        case 'abandoned_carts':
            exportAbandonedCartsReport($format);
            break;
        case 'discounts':
            exportDiscountsReport($format);
            break;
        case 'inventory':
            exportInventoryReport($format);
            break;
        case 'reviews':
            exportReviewsReport($format);
            break;
        case 'shipping':
            exportShippingReport($format);
            break;
        case 'payment_methods':
            exportPaymentMethodsReport($format);
            break;
        case 'customer_acquisition':
            exportCustomerAcquisitionReport($format);
            break;
    }
    exit;
}

// Report data functions
function getSalesReport($start_date = null, $end_date = null) {
    global $conn;
    
    $query = "SELECT o.id, o.created_at, u.email, o.grand_total, o.order_status, 
                     COUNT(oi.id) as items_count, o.payment_method, p.cost
              FROM orders o
              JOIN users u ON o.user_id = u.id
              JOIN order_items oi ON o.id = oi.order_id
              JOIN products p On oi.product_id = p.id";
    
    if ($start_date && $end_date) {
        $query .= " WHERE o.created_at BETWEEN :start_date AND :end_date";
    }
    
    $query .= " GROUP BY o.id ORDER BY o.created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if ($start_date && $end_date) {
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getProductsReport() {
    global $conn;
    
    $query = "SELECT p.id, p.name, p.price, p.cost, 
                     (p.price - p.cost) as profit,
                     COUNT(oi.id) as sales_count,
                     SUM(oi.quantity) as total_quantity,
                     SUM(oi.quantity * oi.price) as total_revenue,
                     c.name as category
              FROM products p
              LEFT JOIN order_items oi ON p.id = oi.product_id
              LEFT JOIN categories c ON p.category_id = c.id
              GROUP BY p.id
              ORDER BY total_revenue DESC";
    
    $result = $conn->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC); 
}

function getCustomersReport() {
    global $conn;
    
    $query = "SELECT u.id, u.email, u.created_at, 
                     COUNT(o.id) as orders_count, 
                     SUM(o.grand_total) as total_spent,
                     MAX(o.created_at) as last_order_date
              FROM users u
              LEFT JOIN orders o ON u.id = o.user_id
              GROUP BY u.id
              ORDER BY total_spent DESC";
    
    $result = $conn->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function getAbandonedCartsReport() {
    global $conn;
    
    $query = "SELECT id, email, created_at, reminded,
                     JSON_LENGTH(cart_data) as items_count
              FROM abandoned_carts
              ORDER BY created_at DESC";
    
    $result = $conn->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function getDiscountsReport() {
    global $conn;
    
    $query = "SELECT dc.id, dc.code_name, dc.code, dc.discount_percentage, 
                     dc.created_at, dc.expiry_date, dc.active_flag,
                     COUNT(o.id) as usage_count,
                     SUM(o.discount_by_code) as total_discount
              FROM discount_codes dc
              LEFT JOIN orders o ON dc.id = o.discount_code_id
              GROUP BY dc.id
              ORDER BY dc.created_at DESC";
    
    $result = $conn->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function getInventoryReport() {
    global $conn;
    
    $query = "SELECT p.id, p.name, p.product_code, p.stock_limit, 
                     p.min_stock, p.cost, p.price, 
                     (SELECT SUM(quantity) FROM order_items oi 
                      JOIN orders o ON oi.order_id = o.id 
                      WHERE oi.product_id = p.id AND o.order_status = 'completed') as sold_quantity,
                     c.name as category
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              ORDER BY p.stock_limit ASC";
    
    $result = $conn->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function getReviewsReport() {
    global $conn;
    
    $query = "SELECT r.id, r.stars, r.review_text, r.created_at,
                     p.name as product_name, u.email as customer_email
              FROM reviews r
              JOIN products p ON r.product_id = p.id
              JOIN users u ON r.user_id = u.id
              ORDER BY r.created_at DESC";
    
    $result = $conn->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function getShippingReport() {
    global $conn;
    
    $query = "SELECT o.id as order_id, o.created_at, o.delivered_at, 
                     o.delivery_charges, o.method as shipping_method,
                     u.email as customer_email, o.grand_total,
                     a.city, a.country
              FROM orders o
              JOIN users u ON o.user_id = u.id
              LEFT JOIN actual_addresses a ON o.id = a.order_id
              WHERE o.order_status IN ('shipped', 'completed')
              ORDER BY o.created_at DESC";
    
    $result = $conn->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function getPaymentMethodsReport() {
    global $conn;
    
    $query = "SELECT o.payment_method, 
                     COUNT(o.id) as orders_count,
                     SUM(o.grand_total) as total_revenue
              FROM orders o
              WHERE o.order_status = 'completed'
              GROUP BY o.payment_method
              ORDER BY total_revenue DESC";
    
    $result = $conn->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

function getCustomerAcquisitionReport() {
    global $conn;
    
    $query = "SELECT DATE(created_at) as signup_date, 
                     COUNT(id) as new_customers,
                     (SELECT COUNT(id) FROM orders o 
                      WHERE DATE(o.created_at) = DATE(u.created_at) 
                      AND o.user_id = u.id) as first_day_orders
              FROM users u
              GROUP BY DATE(created_at)
              ORDER BY signup_date DESC";
    
    $result = $conn->query($query);
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

// Export functions
function exportSalesReport($format) {
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $data = getSalesReport($start_date, $end_date);
    
    $filename = "sales_report_" . date('Y-m-d');
    $headers = ['Order ID', 'Date', 'Customer Email', 'Total Amount', 'Status', 'Items Count', 'Payment Method'];
    
    exportData($data, $filename, $headers, $format);
}

function exportProductsReport($format) {
    $data = getProductsReport();
    
    $filename = "products_report_" . date('Y-m-d');
    $headers = ['ID', 'Product Name', 'Price', 'Cost', 'Profit', 'Sales Count', 'Total Quantity', 'Total Revenue', 'Category'];
    
    exportData($data, $filename, $headers, $format);
}

function exportCustomersReport($format) {
    $data = getCustomersReport();
    
    $filename = "customers_report_" . date('Y-m-d');
    $headers = ['ID', 'Email', 'Signup Date', 'Orders Count', 'Total Spent', 'Last Order Date'];
    
    exportData($data, $filename, $headers, $format);
}

function exportAbandonedCartsReport($format) {
    $data = getAbandonedCartsReport();
    
    $filename = "abandoned_carts_report_" . date('Y-m-d');
    $headers = ['ID', 'Email', 'Date', 'Reminded', 'Items Count'];
    
    exportData($data, $filename, $headers, $format);
}

function exportDiscountsReport($format) {
    $data = getDiscountsReport();
    
    $filename = "discounts_report_" . date('Y-m-d');
    $headers = ['ID', 'Code Name', 'Code', 'Discount %', 'Created At', 'Expiry Date', 'Active', 'Usage Count', 'Total Discount'];
    
    exportData($data, $filename, $headers, $format);
}

function exportInventoryReport($format) {
    $data = getInventoryReport();
    
    $filename = "inventory_report_" . date('Y-m-d');
    $headers = ['ID', 'Product Name', 'Product Code', 'Stock Limit', 'Min Stock', 'Cost', 'Price', 'Sold Quantity', 'Category'];
    
    exportData($data, $filename, $headers, $format);
}

function exportReviewsReport($format) {
    $data = getReviewsReport();
    
    $filename = "reviews_report_" . date('Y-m-d');
    $headers = ['ID', 'Rating', 'Review Text', 'Date', 'Product Name', 'Customer Email'];
    
    exportData($data, $filename, $headers, $format);
}

function exportShippingReport($format) {
    $data = getShippingReport();
    
    $filename = "shipping_report_" . date('Y-m-d');
    $headers = ['Order ID', 'Order Date', 'Delivered At', 'Shipping Cost', 'Method', 'Customer Email', 'Total Amount', 'City', 'Country'];
    
    exportData($data, $filename, $headers, $format);
}

function exportPaymentMethodsReport($format) {
    $data = getPaymentMethodsReport();
    
    $filename = "payment_methods_report_" . date('Y-m-d');
    $headers = ['Payment Method', 'Orders Count', 'Total Revenue'];
    
    exportData($data, $filename, $headers, $format);
}

function exportCustomerAcquisitionReport($format) {
    $data = getCustomerAcquisitionReport();
    
    $filename = "customer_acquisition_report_" . date('Y-m-d');
    $headers = ['Signup Date', 'New Customers', 'First Day Orders'];
    
    exportData($data, $filename, $headers, $format);
}

function exportData($data, $filename, $headers, $format) {
    if ($format == 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    } elseif ($format == 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        
        echo "<table><tr>";
        foreach ($headers as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";
        
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } elseif ($format == 'pdf') {
        require_once('tcpdf/tcpdf.php');
        
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle($filename);
        $pdf->AddPage();
        
        // Add title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, str_replace('_', ' ', ucfirst($filename)), 0, 1, 'C');
        $pdf->Ln(10);
        
        // Create table
        $pdf->SetFont('helvetica', '', 10);
        
        $html = '<table border="1" cellpadding="5">';
        $html .= '<tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Output($filename . '.pdf', 'D');
    }
}

// Get current report data
$current_report = $_GET['report'] ?? 'sales';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

switch ($current_report) {
    case 'sales':
        $report_data = getSalesReport($start_date, $end_date);
        $report_title = "Sales Report";
        break;
    case 'products':
        $report_data = getProductsReport();
        $report_title = "Products Performance Report";
        break;
    case 'customers':
        $report_data = getCustomersReport();
        $report_title = "Customers Report";
        break;
    case 'abandoned_carts':
        $report_data = getAbandonedCartsReport();
        $report_title = "Abandoned Carts Report";
        break;
    case 'discounts':
        $report_data = getDiscountsReport();
        $report_title = "Discount Codes Report";
        break;
    case 'inventory':
        $report_data = getInventoryReport();
        $report_title = "Inventory Report";
        break;
    case 'reviews':
        $report_data = getReviewsReport();
        $report_title = "Customer Reviews Report";
        break;
    case 'shipping':
        $report_data = getShippingReport();
        $report_title = "Shipping/Delivery Report";
        break;
    case 'payment_methods':
        $report_data = getPaymentMethodsReport();
        $report_title = "Revenue by Payment Method";
        break;
    case 'customer_acquisition':
        $report_data = getCustomerAcquisitionReport();
        $report_title = "Customer Acquisition Report";
        break;
    default:
        $report_data = getSalesReport($start_date, $end_date);
        $report_title = "Sales Report";
        $current_report = 'sales';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo $report_title; ?> - Admin Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/dynamic-styles.php">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
                body {
            background-color: var(--light-color);
            font-family: var(--font-family);
        }
        
                .report-card {
            transition: all 0.3s ease;
            cursor: pointer;

        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .report-card.active {
            border-left: 5px solid var(--main-color);
        }
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }
        .export-btn-group .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .bg-d{
    background-color: var(--main-color);
  }  
  .text-lights {
    color:var(--second-color);
  }

    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-d sidebar text-center collapse">
                <div class="position-sticky pt-3">
                    <h5 class="px-3  text-lights">Reports</h5>
                    <ul class="nav flex-column text-start">
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'sales' ? 'active' : ''; ?>" 
                               href="?report=sales&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>">
                                <i class="bi bi-graph-up"></i> Sales Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'products' ? 'active' : ''; ?>" 
                               href="?report=products">
                                <i class="bi bi-box-seam"></i> Product Performance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'customers' ? 'active' : ''; ?>" 
                               href="?report=customers">
                                <i class="bi bi-people"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'abandoned_carts' ? 'active' : ''; ?>" 
                               href="?report=abandoned_carts">
                                <i class="bi bi-cart-x"></i> Abandoned Carts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'discounts' ? 'active' : ''; ?>" 
                               href="?report=discounts">
                                <i class="bi bi-tag"></i> Discount Codes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'inventory' ? 'active' : ''; ?>" 
                               href="?report=inventory">
                                <i class="bi bi-clipboard-data"></i> Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'reviews' ? 'active' : ''; ?>" 
                               href="?report=reviews">
                                <i class="bi bi-star"></i> Customer Reviews
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'shipping' ? 'active' : ''; ?>" 
                               href="?report=shipping">
                                <i class="bi bi-truck"></i> Shipping/Delivery
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'payment_methods' ? 'active' : ''; ?>" 
                               href="?report=payment_methods">
                                <i class="bi bi-credit-card"></i> Payment Methods
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light report-card <?php echo $current_report == 'customer_acquisition' ? 'active' : ''; ?>" 
                               href="?report=customer_acquisition">
                                <i class="bi bi-person-plus"></i> Customer Acquisition
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo $report_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2 export-btn-group">
                            <a class="btn btn-sm btn-outline-secondary" href="admin_dashboard.php">Admin Panel</a>
                            <a href="?export=1&report=<?php echo $current_report; ?>&format=csv&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
                            </a>
                            <a href="?export=1&report=<?php echo $current_report; ?>&format=excel&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-excel"></i> Export Excel
                            </a>
                            <a href="?export=1&report=<?php echo $current_report; ?>&format=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-earmark-pdf"></i> Export PDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Date range filter (for sales report) -->
                <?php if ($current_report == 'sales'): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <input type="hidden" name="report" value="sales">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Report summary cards -->
                <div class="row mb-4">
                    <?php if ($current_report == 'sales'): ?>
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Orders</h5>
                                <p class="card-text h4"><?php echo count($report_data); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Revenue</h5>
                                <p class="card-text h4">AED <?php 
                                    $total = array_sum(array_column($report_data, 'grand_total'));
                                    echo number_format($total, 2);
                                ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total cost</h5>
                                <p class="card-text h4">AED <?php 
                                    $totalcost = array_sum(array_column($report_data, 'cost'));
                                    echo number_format($totalcost, 2);
                                ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Avg. Order Value</h5>
                                <p class="card-text h4">AED <?php 
                                    $count = count($report_data);
                                    $avg = $count > 0 ? $total / $count : 0;
                                    echo number_format($avg, 2);
                                ?></p>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($current_report == 'products'): ?>
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Products</h5>
                                <p class="card-text h4"><?php echo count($report_data); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Top Selling</h5>
                                <p class="card-text h4"><?php 
                                    if (!empty($report_data)) {
                                        echo $report_data[0]['name'];
                                    } else {
                                        echo 'N/A';
                                    }
                                ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Revenue</h5>
                                <p class="card-text h4">AED <?php 
                                    $total = array_sum(array_column($report_data, 'total_revenue'));
                                    echo number_format($total, 2);
                                ?></p>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($current_report == 'customers'): ?>
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Customers</h5>
                                <p class="card-text h4"><?php echo count($report_data); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Top Customer</h5>
                                <p class="card-text h4"><?php 
                                    if (!empty($report_data)) {
                                        echo $report_data[0]['email'];
                                    } else {
                                        echo 'N/A';
                                    }
                                ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Spent</h5>
                                <p class="card-text h4">AED <?php 
                                    $total = array_sum(array_column($report_data, 'total_spent'));
                                    echo number_format($total, 2);
                                ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Report data table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <?php if (!empty($report_data)): ?>
                                            <?php foreach (array_keys($report_data[0]) as $column): ?>
                                                <th><?php echo ucwords(str_replace('_', ' ', $column)); ?></th>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_data as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $cell): ?>
                                                <td><?php echo is_numeric($cell) && strpos($cell, '.') !== false ? 
                                                    number_format($cell, 2) : $cell; ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set end date max to today
        document.addEventListener('DOMContentLoaded', function() {
            const endDateInput = document.getElementById('end_date');
            if (endDateInput) {
                endDateInput.max = new Date().toISOString().split('T')[0];
            }
            
            // Set start date max to end date
            const startDateInput = document.getElementById('start_date');
            if (startDateInput && endDateInput) {
                startDateInput.max = endDateInput.value;
                endDateInput.addEventListener('change', function() {
                    startDateInput.max = this.value;
                });
            }
        });
    </script>
</body>
</html>