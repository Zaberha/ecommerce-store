<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
include 'db.php';
header('Content-Type: application/json');

// Check if it's an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!$is_ajax) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Get the raw POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($data['city'])) {
    echo json_encode(['error' => 'Invalid input data']);
    exit;
}

$city = trim($data['city']);

try {
    // Fetch the freight rate for the specified city
    $stmt = $conn->prepare("
        SELECT area_freight_rate 
        FROM delivery_area 
        WHERE area_name = :city 
        LIMIT 1
    ");
    $stmt->bindParam(':city', $city, PDO::PARAM_STR);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && isset($result['area_freight_rate'])) {
        echo json_encode([
            'success' => true,
            'area_freight_rate' => (float)$result['area_freight_rate'],
            'city' => $city
        ]);
    } else {
        // If city not found, return default flat rate
        $stmt = $conn->prepare("
            SELECT flat_rate 
            FROM delivery_options 
            LIMIT 1
        ");
        $stmt->execute();
        $default_rate = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'area_freight_rate' => (float)$default_rate,
            'default_rate' => true,
            'message' => 'Using default rate for city: ' . $city
        ]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}