<?php
// Dev helper: update event location directly. Only allowed from localhost.
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!in_array($ip, ['127.0.0.1', '::1', 'localhost'])){
    echo json_encode(['success'=>false,'error'=>'forbidden']);
    exit;
}
$db = get_db();
$event_id = intval($_GET['event_id'] ?? 0);
$location = isset($_GET['location']) ? trim($_GET['location']) : null;
if ($event_id <= 0 || !$location) { echo json_encode(['success'=>false,'error'=>'invalid input']); exit; }
$stmt = $db->prepare('UPDATE events SET location=? WHERE id=?');
$stmt->bind_param('si', $location, $event_id);
if ($stmt->execute()) echo json_encode(['success'=>true,'event_id'=>$event_id,'location'=>$location]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
?>