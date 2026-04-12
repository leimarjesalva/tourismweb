<?php
/**
 * Diagnostic API for Shop ID Mismatch
 * Helps identify why feedback isn't showing in shops
 */

header('Content-Type: application/json');
require_once('db.php');

$conn = get_db();

// Get all shops with their IDs and feedback count
$shopQuery = "SELECT s.id, s.name, COUNT(f.id) as feedback_count
              FROM shops s
              LEFT JOIN feedback f ON s.id = f.target_id AND f.target_type = 'shop'
              GROUP BY s.id
              ORDER BY s.name";

$shopResult = $conn->query($shopQuery);
$shops = [];
if ($shopResult) {
    while ($row = $shopResult->fetch_assoc()) {
        $shops[] = $row;
    }
}

// Get all feedback entries with their target info
$feedbackQuery = "SELECT id, target_id, target_name, user_name, rating, created_at
                  FROM feedback
                  WHERE target_type = 'shop'
                  ORDER BY created_at DESC
                  LIMIT 20";

$feedbackResult = $conn->query($feedbackQuery);
$feedback = [];
if ($feedbackResult) {
    while ($row = $feedbackResult->fetch_assoc()) {
        $feedback[] = $row;
    }
}

// Find mismatches
$mismatches = [];
foreach ($feedback as $fb) {
    $shopExists = false;
    foreach ($shops as $shop) {
        if ($shop['id'] == $fb['target_id']) {
            $shopExists = true;
            break;
        }
    }
    if (!$shopExists) {
        $mismatches[] = [
            'feedback_id' => $fb['id'],
            'stored_target_id' => $fb['target_id'],
            'stored_target_name' => $fb['target_name'],
            'actual_shop_in_db' => 'NOT FOUND',
            'message' => "Feedback references shop ID {$fb['target_id']} which doesn't exist in shops table"
        ];
    }
}

echo json_encode([
    'success' => true,
    'shops_in_database' => $shops,
    'recent_feedback' => $feedback,
    'mismatches_found' => count($mismatches),
    'mismatches' => $mismatches,
    'recommendation' => empty($mismatches) ? 'All feedback properly linked to shops' : 'MISMATCH FOUND! Use the fix endpoint to correct this.'
]);
?>
