<?php
// Update festival dates in the database
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'capstone';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Update the festival dates
$updates = [
    5 => ['date_start' => '2026-05-01', 'date_end' => '2026-05-31'],
    6 => ['date_start' => '2026-05-01', 'date_end' => '2026-05-31'],
    7 => ['date_start' => '2026-06-01', 'date_end' => '2026-06-03']
];

foreach ($updates as $id => $dates) {
    $sql = "UPDATE festivals_events SET date_start = '{$dates['date_start']}', date_end = '{$dates['date_end']}' WHERE id = {$id}";
    if ($conn->query($sql) === TRUE) {
        echo "Festival ID {$id} updated successfully.<br>";
    } else {
        echo "Error updating festival ID {$id}: " . $conn->error . "<br>";
    }
}

// Verify the updates
$result = $conn->query("SELECT id, name, date_start, date_end FROM festivals_events WHERE id IN (5, 6, 7)");
echo "<br><strong>Updated Festival Dates:</strong><br>";
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . ': ' . $row['name'] . ' - ' . $row['date_start'] . ' to ' . $row['date_end'] . "<br>";
}

$conn->close();
echo "<br>Festival dates have been updated! Refresh your website to see the changes.";
?>
