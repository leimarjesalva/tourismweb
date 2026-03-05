<?php
header('Content-Type: application/json');
require_once('db.php');

// establish database connection
$conn = get_db();

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate required fields
    $name = isset($data['name']) ? trim($data['name']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $feedbackType = isset($data['feedback_type']) ? trim($data['feedback_type']) : '';
    $message = isset($data['message']) ? trim($data['message']) : '';
    $page = isset($data['page']) ? trim($data['page']) : 'index';
    
    // Basic validation
    if (!$name || !$email || !$feedbackType || !$message) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid email format']);
        exit;
    }
    
    // Validate feedback type
    $validTypes = ['suggestion', 'bug', 'praise', 'other'];
    if (!in_array($feedbackType, $validTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid feedback type']);
        exit;
    }
    
    // Insert feedback into database
    $query = "INSERT INTO feedback (user_email, user_name, feedback_type, message, anonymous) VALUES (?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('ssss', $email, $name, $feedbackType, $message);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $feedbackId = $stmt->insert_id;
    $stmt->close();
    
    // Also save to file for backup
    $feedbackDir = __DIR__ . '/feedback/';
    if (!is_dir($feedbackDir)) {
        mkdir($feedbackDir, 0755, true);
    }
    
    $backupJson = $feedbackDir . 'feedback_' . $feedbackId . '.json';
    $backupData = [
        'id' => $feedbackId,
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => $name,
        'email' => $email,
        'feedback_type' => $feedbackType,
        'page' => $page,
        'message' => $message,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    file_put_contents($backupJson, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    // Log to PHP error log for admin notification
    error_log('📝 New feedback submitted: [' . strtoupper($feedbackType) . '] from ' . $email . ' (ID: ' . $feedbackId . ')');
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your feedback!',
        'feedback_id' => $feedbackId
    ]);
    
} catch (Exception $e) {
    error_log('Feedback error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
