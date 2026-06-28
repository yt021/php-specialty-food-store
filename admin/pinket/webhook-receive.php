<?php
// Webhook receive endpoint for testing
header('Content-Type: application/json; charset=utf-8');

// Set up the environment like the admin system does
$indexed = 1;
include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");

// Log webhook data
$logFile = 'webhook_log.txt';
$timestamp = date('Y-m-d H:i:s');
$input = file_get_contents('php://input');

// Log the incoming webhook
$logEntry = "[$timestamp] Webhook received:\n$input\n" . str_repeat('-', 50) . "\n";
file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

try {
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Validate required fields
    if (!isset($data['event']) || !isset($data['order_id'])) {
        throw new Exception('Missing required fields: event, order_id');
    }
    
    // Process webhook based on event type
    switch ($data['event']) {
        case 'order_status_changed':
            $message = "Order status changed for order: " . $data['order_id'];
            break;
        case 'order_cancelled':
            $message = "Order cancelled: " . $data['order_id'];
            break;
        case 'order_updated':
            $message = "Order updated: " . $data['order_id'];
            break;
        default:
            $message = "Unknown event: " . $data['event'];
    }
    
    // Simulate processing time
    usleep(100000); // 0.1 second
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'webhook_id' => uniqid('wh_'),
        'processed_at' => date('Y-m-d H:i:s'),
        'received_data' => $data
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'received_input' => $input
    ], JSON_UNESCAPED_UNICODE);
}
?> 