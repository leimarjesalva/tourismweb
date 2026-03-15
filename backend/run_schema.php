<?php
require 'db.php';
$db = get_db();

$sql = file_get_contents(__DIR__ . '/schema.sql');

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

echo json_encode(['success' => true, 'results' => $results]);