<?php
/*
 * api_create_delivery.php
 * Stateless POST endpoint for external partners to submit delivery requests.
 * Creates delivery with status 'available' and no assigned user.
 * Returns JSON response only - no UI.
 */

require_once 'config.php';

// Set JSON response headers
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

// Check Content-Type and extract data accordingly
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
$data = [];

if (strpos($content_type, 'application/json') !== false) {
    // Handle JSON input
    $json_input = file_get_contents('php://input');
    $data = json_decode($json_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid JSON format.'
        ]);
        exit;
    }
} else {
    // Handle form-encoded input (fallback)
    $data = $_POST;
}

// Extract delivery data
$source = $data['Source'] ?? null;
$destination = $data['Destination'] ?? null;
$weight_g = $data['Weight'] ?? null;
$is_bulky = isset($data['isBulky']) ? (bool)$data['isBulky'] : false;
$is_fresh = isset($data['isFresh']) ? (bool)$data['isFresh'] : false;

// Validate required fields
if (empty($source) || empty($destination) || empty($weight_g)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Missing required fields: Source, Destination, and Weight are required.'
    ]);
    exit;
}

// Validate weight is numeric and positive
if (!is_numeric($weight_g) || $weight_g <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Weight must be a positive number.'
    ]);
    exit;
}

// Calculate price (same logic as before)
$base_fee = 5.0;
$price_per_kg = 1.5;
$price = $base_fee + (($weight_g / 1000) * $price_per_kg);

try {
    // Create delivery with status 'available' and no assigned user
    $sql = "INSERT INTO DELIVERY (source, destination, weight_g, is_bulky, is_fresh, status, id_user_assigned, price) 
            VALUES (?, ?, ?, ?, ?, 'available', NULL, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $source,
        $destination,
        (float)$weight_g,
        (bool)$is_bulky,
        (bool)$is_fresh,
        $price
    ]);

    $delivery_id = $pdo->lastInsertId();

    // Return success response
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'delivery_id' => (int)$delivery_id,
        'status' => 'available',
        'price' => number_format($price, 2),
        'message' => 'Delivery created successfully and is now available for drivers to claim.'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: Unable to create delivery. Please try again.'
    ]);
    
    // Log error for debugging (in production, use proper logging)
    error_log('Delivery creation error: ' . $e->getMessage());
}