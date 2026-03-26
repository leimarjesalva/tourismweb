<?php
require_once 'db.php';
$db = get_db();

echo "Testing event_alerts table...\n";

$result = $db->query('DESCRIBE event_alerts');
if ($result) {
    echo "event_alerts table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} else {
    echo "Error describing table: " . $db->error . "\n";
}

// Check if there are any records
$result = $db->query('SELECT COUNT(*) as count FROM event_alerts');
if ($result) {
    $row = $result->fetch_assoc();
    echo "Records in event_alerts: " . $row['count'] . "\n";
} else {
    echo "Error counting records: " . $db->error . "\n";
}

// Try to insert a test record
$stmt = $db->prepare("INSERT INTO event_alerts (event_id, alert_type, content) VALUES (?, ?, ?)");
if ($stmt) {
    $stmt->bind_param('iss', $event_id, $alert_type, $content);
    $event_id = 1;
    $alert_type = 'test';
    $content = 'Test alert';
    if ($stmt->execute()) {
        echo "Test insert successful, ID: " . $db->insert_id . "\n";
    } else {
        echo "Test insert failed: " . $stmt->error . "\n";
    }
} else {
    echo "Prepare failed: " . $db->error . "\n";
}
?>