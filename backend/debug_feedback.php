<?php
header('Content-Type: application/json');
require_once('db.php');

$conn = get_db();

// Get all feedback
$query = "SELECT id, target_type, target_id, target_name, rating, user_name, created_at FROM feedback ORDER BY id DESC";
$result = $conn->query($query);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

$feedback = [];
while ($row = $result->fetch_assoc()) {
    $feedback[] = $row;
}

echo json_encode([
    'success' => true,
    'total_count' => count($feedback),
    'feedback' => $feedback
]);
?>
