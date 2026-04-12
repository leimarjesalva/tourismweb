<?php
require_once('backend/db.php');
$conn = get_db();
$sql = file_get_contents('add_rating_columns.sql');

if ($conn->multi_query($sql)) {
    echo "Rating columns added successfully\n";
    // Consume all results
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo "Error adding rating columns: " . $conn->error . "\n";
}
?>