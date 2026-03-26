<?php
require_once 'db.php';
$db = get_db();

$result = $db->query('SHOW TABLES LIKE "event_alerts"');
if ($result->num_rows > 0) {
    echo 'event_alerts table exists' . PHP_EOL;
    $result = $db->query('DESCRIBE event_alerts');
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
    }
} else {
    echo 'event_alerts table does not exist' . PHP_EOL;
}

// Also check if there are any records
$result = $db->query('SELECT COUNT(*) as count FROM event_alerts');
if ($result) {
    $row = $result->fetch_assoc();
    echo 'Records in event_alerts: ' . $row['count'] . PHP_EOL;
}
?>