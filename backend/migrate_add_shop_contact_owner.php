<?php
require_once __DIR__ . '/db.php';
$db = get_db();
$needs = [];
$res = $db->query("SHOW COLUMNS FROM shops LIKE 'contact'");
if ($res->num_rows === 0) $needs[] = "ADD COLUMN contact VARCHAR(255)";
$res = $db->query("SHOW COLUMNS FROM shops LIKE 'owner_name'");
if ($res->num_rows === 0) $needs[] = "ADD COLUMN owner_name VARCHAR(255)";
$res = $db->query("SHOW COLUMNS FROM shops LIKE 'image'");
if ($res->num_rows === 0) $needs[] = "ADD COLUMN image VARCHAR(255) DEFAULT NULL";

if (empty($needs)) {
    echo json_encode(['success'=>true,'message'=>'No changes needed']);
    exit;
}

$sql = 'ALTER TABLE shops ' . implode(', ', $needs);
if ($db->query($sql) === TRUE) {
    echo json_encode(['success'=>true,'query'=>$sql]);
} else {
    echo json_encode(['success'=>false,'error'=>$db->error,'query'=>$sql]);
}
