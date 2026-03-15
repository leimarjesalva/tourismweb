<?php
$db = new mysqli(
    getenv('MYSQLHOST'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    getenv('MYSQLDATABASE'),
    getenv('MYSQLPORT')
);

if ($db->connect_errno) {
    die("DB ERROR: " . $db->connect_error);
}

// Disable foreign key checks
$db->query("SET FOREIGN_KEY_CHECKS=0");

// Drop all tables first
$result = $db->query("SHOW TABLES");
while($row = $result->fetch_array()) {
    $db->query("DROP TABLE IF EXISTS `" . $row[0] . "`");
}

// Re-enable foreign key checks
$db->query("SET FOREIGN_KEY_CHECKS=1");

// Read and run SQL
$sql = file_get_contents(__DIR__ . '/capstone_db.sql');
if (!$sql) die("Cannot read capstone_db.sql");

$db->multi_query($sql);
do { } while (@$db->next_result());

echo json_encode([
    'success' => true,
    'message' => 'Import complete!',
    'error' => $db->error ?: 'none'
]);