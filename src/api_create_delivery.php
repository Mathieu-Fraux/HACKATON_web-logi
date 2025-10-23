<?php
/*
 * api_create_delivery.php
 * Public API endpoint to register a new delivery.
 * This inserts a delivery with status 'pending' and no assigned user.
 *
 * This API expects a JSON POST body.
 */

// Include config for database connection
require_once 'config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed. Use POST.']);
    exit;
}

// Get the raw POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

// --- Validate Input ---
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload.']);
    exit;
}

// Get parameters from the decoded JSON
$source = $data['Source'] ?? null;
$destination = $data['Destination'] ?? null;
$weight_g = $data['Weight'] ?? null;
$is_bulky = $data['isBulky'] ?? false;
$is_fresh = $data['isFresh'] ?? false;

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
    // As per the brief, we should send a confirmation.
    // The "redirect with POST" is not feasible from an API endpoint.
    // The client calling this API will receive this JSON and must handle
    // its own redirection and confirmation.
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
