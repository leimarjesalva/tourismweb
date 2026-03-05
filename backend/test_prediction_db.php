<?php
require_once __DIR__ . '/db.php';

$db = get_db();

// Check if prediction column exists
$res = $db->query("SHOW COLUMNS FROM events LIKE 'prediction'");
echo "Prediction column exists: " . ($res && $res->num_rows > 0 ? "YES" : "NO") . "\n";

// Check events structure
echo "\nEvents table columns:\n";
$res = $db->query("SHOW COLUMNS FROM events");
while($row = $res->fetch_assoc()) {
    echo "  - {$row['Field']} ({$row['Type']})\n";
}

// Check actual data
echo "\nActual event data:\n";
$res = $db->query("SELECT id, title, prediction FROM events LIMIT 3");
while($ev = $res->fetch_assoc()) {
    echo "ID {$ev['id']}: {$ev['title']}\n";
    echo "  prediction: " . ($ev['prediction'] ? substr($ev['prediction'], 0, 100) . "..." : "(NULL)") . "\n";
}
?>
