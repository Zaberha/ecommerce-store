<?php
session_start();
require_once 'db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['employee_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}


// Get filters from query string
$filter_product_id = $_GET['product_id'] ?? null;
$filter_warehouse_id = $_GET['warehouse_id'] ?? null;
$filter_type = $_GET['type'] ?? null;
$filter_date_from = $_GET['date_from'] ?? null;
$filter_date_to = $_GET['date_to'] ?? null;

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_movements_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, [
    'Date',
    'Product Code',
    'Product Name',
    'Variant',
    'Warehouse',
    'Type',
    'Quantity',
    'Reference',
    'User',
    'Notes'
]);

// Build query
$query_params = [];
$query = "
    SELECT m.created_at, 
           p.product_code,
           p.name as product_name,
           CONCAT(v.option_name, ': ', v.option_value) as variant,
           w.name as warehouse_name,
           m.movement_type,
           m.quantity,
           m.reference_id,
           u.full_name as user_name,
           m.notes
    FROM inventory_movements m
    JOIN products p ON m.product_id = p.id
    LEFT JOIN product_variants v ON m.variant_id = v.id
    LEFT JOIN warehouses w ON m.warehouse_id = w.id
    LEFT JOIN employees u ON m.user_id = u.id
    WHERE 1=1
";

// Apply filters
if ($filter_product_id) {
    $query .= " AND m.product_id = ?";
    $query_params[] = $filter_product_id;
}

if ($filter_warehouse_id) {
    $query .= " AND m.warehouse_id = ?";
    $query_params[] = $filter_warehouse_id;
}

if ($filter_type) {
    $query .= " AND m.movement_type = ?";
    $query_params[] = $filter_type;
}

if ($filter_date_from) {
    $query .= " AND m.created_at >= ?";
    $query_params[] = $filter_date_from . ' 00:00:00';
}

if ($filter_date_to) {
    $query .= " AND m.created_at <= ?";
    $query_params[] = $filter_date_to . ' 23:59:59';
}

$query .= " ORDER BY m.created_at DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($query_params);
    
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['created_at'],
            $row['product_code'],
            $row['product_name'],
            $row['variant'],
            $row['warehouse_name'],
            ucfirst($row['movement_type']),
            $row['quantity'],
            $row['reference_id'],
            $row['user_name'],
            $row['notes']
        ]);
    }
} catch (PDOException $e) {
    // Log error
    error_log("Export failed: " . $e->getMessage());
    
    // Output error message as CSV
    fputcsv($output, ["Error: Could not generate export"]);
}

fclose($output);
exit;