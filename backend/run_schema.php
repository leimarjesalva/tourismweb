<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$sql = file_get_contents(__DIR__ . '/capstone_db.sql');

if (!$sql) {
    die("Cannot read capstone_db.sql");
}

// Enable multi query
$db->multi_query($sql);

$results = [];
do {
    $results[] = "OK";
} while ($db->next_result());

if ($db->errno) {
    $results[] = "ERROR: " . $db->error;
}

echo json_encode([
    'success' => true,
    'message' => 'Import done!',
    'queries_ran' => count($results),
    'last_error' => $db->error ?: 'none'
]);