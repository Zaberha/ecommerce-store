<?php
// Get theme settings from admin table
function getThemeSettings($conn) {
    $stmt = $conn->prepare("SELECT main_color, second_color, third_color, font_family FROM admin LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Generate CSS variables based on theme settings
function generateThemeCSS($conn) {
    $theme = getThemeSettings($conn);
    
    // Default values if not set
    $main_color = $theme['main_color'] ?? '#007bff';
    $second_color = $theme['second_color'] ?? '#6c757d';
    $third_color = $theme['third_color'] ?? '#28a745';
    $font_family = $theme['font_family'] ?? 'Arial, sans-serif';
    
    return "
    :root {
        --primary-color: {$main_color};
        --secondary-color: {$second_color};
        --accent-color: {$third_color};
        --primary-darker: " . adjustBrightness($main_color, -20) . ";
        --primary-lighter: " . adjustBrightness($main_color, 20) . ";
        --text-color: #333333;
        --text-light: #666666;
        --background-color: #ffffff;
        --background-alt: #f8f9fa;
        --border-color: #dee2e6;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
        --font-family: {$font_family};
    }";
}

// Helper function to adjust color brightness
function adjustBrightness($hex, $steps) {
    $hex = str_replace('#', '', $hex);
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}
