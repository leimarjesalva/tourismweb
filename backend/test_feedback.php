<?php
/**
 * Quick Feedback Test - Shows exactly what's in the database
 */

header('Content-Type: application/json');
require_once('db.php');

$conn = get_db();

// Get all shop feedback
$query = "SELECT f.id, f.target_id, f.target_name, f.user_name, f.rating, f.message, f.created_at, s.id as shop_exists
          FROM feedback f
          LEFT JOIN shops s ON f.target_id = s.id AND f.target_type = 'shop'
          WHERE f.target_type = 'shop'
          ORDER BY f.created_at DESC";

$result = $conn->query($query);
$feedback = [];

while ($row = $result->fetch_assoc()) {
    $feedback[] = $row;
}

// Get all shops
$shopsQuery = "SELECT id, name FROM shops ORDER BY name";
$shopsResult = $conn->query($shopsQuery);
$shops = [];

while ($shop = $shopsResult->fetch_assoc()) {
    $shops[] = $shop;
}

echo json_encode([
    'success' => true,
    'total_feedback' => count($feedback),
    'total_shops' => count($shops),
    'feedback' => $feedback,
    'shops' => $shops
], JSON_PRETTY_PRINT);
?>
