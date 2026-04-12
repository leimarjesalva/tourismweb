<?php
/**
 * Fix Shop ID Mismatches
 * Ensures all feedback is linked to valid shops
 */

header('Content-Type: application/json');
require_once('db.php');

$conn = get_db();
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'sync_feedback':
        syncFeedbackWithShops($conn);
        break;
    
    case 'fix_shop_references':
        fixShopReferences($conn);
        break;
    
    case 'verify_shop':
        verifyShop($conn);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

/**
 * Sync feedback - rename shop IDs to match the actual shop structure
 */
function syncFeedbackWithShops($conn) {
    // Get mapping of shop names to IDs
    $shopQuery = "SELECT id, name FROM shops";
    $shopResult = $conn->query($shopQuery);
    $shopMap = [];
    
    while ($shop = $shopResult->fetch_assoc()) {
        $shopMap[$shop['name']] = $shop['id'];
    }
    
    // Get all feedback with mismatched IDs
    $feedbackQuery = "SELECT id, target_id, target_name FROM feedback WHERE target_type = 'shop'";
    $feedbackResult = $conn->query($feedbackQuery);
    
    $fixed = 0;
    $errors = [];
    
    while ($feedback = $feedbackResult->fetch_assoc()) {
        // Check if the target_id matches any shop
        $checkQuery = "SELECT id FROM shops WHERE id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('s', $feedback['target_id']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            // Shop ID doesn't exist, try to find by name
            if (isset($shopMap[$feedback['target_name']])) {
                $newId = $shopMap[$feedback['target_name']];
                $updateQuery = "UPDATE feedback SET target_id = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param('si', $newId, $feedback['id']);
                if ($updateStmt->execute()) {
                    $fixed++;
                } else {
                    $errors[] = "Failed to fix feedback {$feedback['id']}: " . $updateStmt->error;
                }
            } else {
                $errors[] = "Shop '{$feedback['target_name']}' not found in database";
            }
        }
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => "Fixed $fixed feedback records",
        'errors' => $errors,
        'total_errors' => count($errors)
    ]);
}

/**
 * Fix shop references - update target_name to match current shop names
 */
function fixShopReferences($conn) {
    $updateQuery = "UPDATE feedback f
                    JOIN shops s ON f.target_id = s.id AND f.target_type = 'shop'
                    SET f.target_name = s.name
                    WHERE f.target_name != s.name";
    
    if ($conn->query($updateQuery)) {
        $affected = $conn->affected_rows;
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => "Updated $affected feedback records with correct shop names",
            'affected_rows' => $affected
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $conn->error
        ]);
    }
}

/**
 * Verify a specific shop's feedback
 */
function verifyShop($conn) {
    $shopId = isset($_GET['shop_id']) ? trim($_GET['shop_id']) : '';
    
    if (!$shopId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'shop_id required']);
        exit;
    }
    
    // Get shop info
    $shopQuery = "SELECT id, name FROM shops WHERE id = ?";
    $shopStmt = $conn->prepare($shopQuery);
    $shopStmt->bind_param('s', $shopId);
    $shopStmt->execute();
    $shopResult = $shopStmt->get_result();
    
    if ($shopResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Shop not found']);
        exit;
    }
    
    $shop = $shopResult->fetch_assoc();
    
    // Get feedback for this shop
    $feedbackQuery = "SELECT id, user_name, rating, created_at FROM feedback 
                     WHERE target_type = 'shop' AND target_id = ?
                     ORDER BY created_at DESC";
    $feedbackStmt = $conn->prepare($feedbackQuery);
    $feedbackStmt->bind_param('s', $shopId);
    $feedbackStmt->execute();
    $feedbackResult = $feedbackStmt->get_result();
    
    $feedback = [];
    while ($fb = $feedbackResult->fetch_assoc()) {
        $feedback[] = $fb;
    }
    
    // Get stats
    $statsQuery = "SELECT 
                    COUNT(*) as total,
                    AVG(rating) as avg_rating,
                    MIN(rating) as min_rating,
                    MAX(rating) as max_rating
                   FROM feedback
                   WHERE target_type = 'shop' AND target_id = ?";
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->bind_param('s', $shopId);
    $statsStmt->execute();
    $statsResult = $statsStmt->get_result();
    $stats = $statsResult->fetch_assoc();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'shop' => $shop,
        'feedback_count' => count($feedback),
        'feedback' => $feedback,
        'statistics' => $stats
    ]);
}

?>
