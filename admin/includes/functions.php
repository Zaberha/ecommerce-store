<?php
function hasAccess($page_name, $privileges, $is_admin = false) {
    if ($is_admin) return true;
    
    foreach ($privileges as $priv) {
        if ($priv['page_name'] == $page_name && $priv['can_access']) {
            return true;
        }
    }
    return false;
}



// Format currency
function formatCurrency($amount, $currency = null) {
    global $default_currency;
    $currency = $currency ?: $default_currency;
    return $currency . number_format($amount, 2);
}

// Format date/time
function formatDateTime($datetime) {
    return date('M j, Y H:i', strtotime($datetime));
}

// Check permissions
function hasPermission($permission) {
    // Implement your permission checking logic
    return true; // Modify this based on your auth system
}

// Image upload handler
function uploadImage($file, $folder) {
    $target_dir = __DIR__ . "/../uploads/$folder/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_ext, $allowed_ext)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    $filename = uniqid() . '.' . $file_ext;
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Error uploading file'];
    }
}

// Delete image
function deleteImage($filename, $folder) {
    $filepath = __DIR__ . "/../uploads/$folder/" . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}




//po
function getPurchaseOrder($orderId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM purchase_orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching purchase order: " . $e->getMessage());
        return false;
    }
}

function populateSelect($table, $selectedValue = '', $allowNull = false) {
    global $conn;
    try {
        $output = '';
        if ($allowNull) {
            $output .= '<option value="">None</option>';
        }
        
        $stmt = $conn->query("SELECT id, name FROM $table ORDER BY name");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $selected = $row['id'] == $selectedValue ? 'selected' : '';
            $output .= "<option value='{$row['id']}' $selected>{$row['name']}</option>";
        }
        return $output;
    } catch (PDOException $e) {
        error_log("Database error in populateSelect: " . $e->getMessage());
        return '<option value="">Error loading data</option>';
    }
}

function getPurchaseOrderItems($orderId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM purchase_order_items WHERE purchase_order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting PO items: " . $e->getMessage());
        return [];
    }
}



function savePurchaseOrder($orderData, $isEdit) {
    global $conn;
    try {
        if ($isEdit) {
            $stmt = $conn->prepare("UPDATE purchase_orders SET 
                supplier_id = ?, warehouse_id = ?, order_number = ?, order_date = ?,
                expected_delivery_date = ?, status = ?, subtotal = ?, tax_amount = ?,
                discount_amount = ?, total_amount = ?, notes = ?, updated_at = NOW()
                WHERE id = ?");
            $stmt->execute(array_values($orderData));
            return $orderData['id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO purchase_orders 
                (supplier_id, warehouse_id, order_number, order_date, expected_delivery_date,
                status, subtotal, tax_amount, discount_amount, total_amount, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(array_values($orderData));
            return $conn->lastInsertId();
        }
    } catch (PDOException $e) {
        error_log("Error saving purchase order: " . $e->getMessage());
        return false;
    }
}