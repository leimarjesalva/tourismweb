<?php
require 'backend/db.php';
$db = get_db();

// Check if feedback table exists and has feedback_type column
$result = $db->query("SHOW COLUMNS FROM feedback");
echo "=== Feedback Table Structure ===\n";
while ($col = $result->fetch_assoc()) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}

// Check feedback count
$countResult = $db->query("SELECT COUNT(*) as cnt FROM feedback");
$countRow = $countResult->fetch_assoc();
echo "\n=== Feedback Count ===\n";
echo "Total feedbacks: " . $countRow['cnt'] . "\n";

// Try to fetch feedbacks exactly like the API does
echo "\n=== Feedback Data (API Query) ===\n";
$res = $db->query('SELECT id, user_email as email, user_name as name, anonymous, feedback_type as type, message as feedback, rating, created_at FROM feedback ORDER BY created_at DESC LIMIT 100');

if($res) {
    $count = 0;
    while ($row = $res->fetch_assoc()) {
        $count++;
        echo "Feedback #$count: " . json_encode($row) . "\n";
    }
    echo "\nFetched $count feedbacks\n";
} else {
    echo "Error: " . $db->error . "\n";
}
?>
