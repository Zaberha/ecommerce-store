<?php
header("Content-type: text/css");
require_once __DIR__ . '/../db.php';

// Fetch admin settings
$stmt = $conn->prepare("SELECT * FROM admin LIMIT 1");
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Output CSS variables
echo ":root {
    --main-color: " . ($settings['main_color'] ?? '#007bff') . ";
    --second-color: " . ($settings['second_color'] ?? '#6c757d') . ";
    --third-color: " . ($settings['third_color'] ?? '#28a745') . ";
    --forth-color: " . ($settings['forth_color'] ?? '#4dd6e0') . ";
    --secondary-color: " . ($settings['third_color'] ?? '#28a745') . ";
    --font-family: " . ($settings['font_family'] ?? "'Arial', sans-serif") . ";
    
    /* Derived variables */
    --main-color-light: " . adjustBrightness($settings['main_color'] ?? '#007bff', 20) . ";
    --main-color-dark: " . adjustBrightness($settings['main_color'] ?? '#007bff', -20) . ";
    --light-color: " . adjustBrightness($settings['main_color'] ?? '#007bff', 20) . ";
    --dark-color: " . adjustBrightness($settings['main_color'] ?? '#007bff', -20) . ";
    --second-color-light: " . adjustBrightness($settings['second_color'] ?? '#6c757d', 20) . ";
    --second-color-dark: " . adjustBrightness($settings['second_color'] ?? '#6c757d', -20) . ";
    --text-color: #333333;
    --text-light: #666666;
    --background-color: #ffffff;
    --background-alt: #151414;
    --border-color: #dee2e6;
}";








// Helper function to adjust color brightness
function adjustBrightness($hex, $steps) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Convert to RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert back to hex
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}
?>
