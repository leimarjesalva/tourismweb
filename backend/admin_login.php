<?php
// Set JSON header FIRST before including db.php
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

try {
    // Get input data - try JSON first, then POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data && !empty($_POST)) {
        $data = $_POST;
    }
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No input data provided']);
        exit;
    }
    
    $user = trim($data['username'] ?? '');
    $pass = trim($data['password'] ?? '');
    
    if (empty($user) || empty($pass)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password required']);
        exit;
    }
    
    // Verify credentials against constants
    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_user'] = $user;
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Login successful']);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
