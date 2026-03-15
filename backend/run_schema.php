<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db.php';
$db = get_db();

$sql = file_get_contents(__DIR__ . '/capstone_db(2).sql');

if (!$sql) {
    die(json_encode(['error' => 'Cannot read SQL file']));
}

$queries = array_filter(array_map('trim', explode(';', $sql)));

$results = [];
foreach ($queries as $query) {
    if (empty($query)) continue;
    if ($db->query($query)) {
        $results[] = "OK: " . substr($query, 0, 60);
    } else {
        $results[] = "ERROR: " . $db->error . " | " . substr($query, 0, 60);
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'total' => count($results), 'results' => $results]);