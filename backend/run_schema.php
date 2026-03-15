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

$sql = file_get_contents(__DIR__ . '/capstone_db.sql');

if (!$sql) {
    die("Cannot read capstone_db.sql");
}

$db->multi_query($sql);

$results = [];
$errors = [];
do {
    $results[] = "OK";
    if ($db->errno && $db->errno != 1050) { // 1050 = table already exists
        $errors[] = $db->error;
    }
} while (@$db->next_result());

echo json_encode([
    'success' => true,
    'message' => 'Import done!',
    'queries_ran' => count($results),
    'errors' => $errors
]);