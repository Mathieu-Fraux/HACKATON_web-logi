<?php
/*
 * api_create_delivery.php
 * Public API endpoint to register a new delivery.
 * This inserts a delivery with status 'pending' and no assigned user.
 *
 * This API expects URL parameters (GET request).
 * Example: /api_create_delivery.php?Source=...&Destination=...&Weight=...
 */

// Include config for database connection
require_once 'config.php';

// Set content type to JSON for all responses
header('Content-Type: application/json');

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed. Use GET.']);
    exit;
}

// --- Validate Input ---
// Get parameters from the URL query string
$source = $_GET['Source'] ?? null;
$destination = $_GET['Destination'] ?? null;
$weight_g = $_GET['Weight'] ?? null;
$is_bulky = $_GET['isBulky'] ?? false;
$is_fresh = $_GET['isFresh'] ?? false;

// Check for required fields
if (empty($source) || empty($destination) || empty($weight_g)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: Source, Destination, and Weight are required.'
    ]);
    exit;
}

// --- Insert into Database ---
try {
    // As per the brief, this API creates a pending delivery.
    // The "user selection" must be a separate process.
    $sql = "INSERT INTO DELIVERY (source, destination, weight_g, is_bulky, is_fresh, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        $source,
        $destination,
        (float)$weight_g,
        (bool)$is_bulky,
        (bool)$is_fresh,
        'pending' // Default status for a new API submission
    ]);

    // Get the ID of the newly inserted delivery
    $new_delivery_id = $pdo->lastInsertId();

    // --- Send Success Response ---
    http_response_code(201); // Created
    echo json_encode([
        'success' => true,
        'message' => 'Delivery created successfully.',
        'id_delivery' => $new_delivery_id
    ]);
    exit;

} catch (PDOException $e) {
    // Handle database error
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

