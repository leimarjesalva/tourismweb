<?php
// Dev helper: set event coordinates directly. Only allowed from localhost.
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!in_array($ip, ['127.0.0.1', '::1', 'localhost'])){
    echo json_encode(['success'=>false,'error'=>'forbidden']);
    exit;
}
$db = get_db();
$event_id = intval($_GET['event_id'] ?? 0);
$start_lat = isset($_GET['start_lat']) ? floatval($_GET['start_lat']) : null;
$start_lng = isset($_GET['start_lng']) ? floatval($_GET['start_lng']) : null;
$end_lat = isset($_GET['end_lat']) ? floatval($_GET['end_lat']) : null;
$end_lng = isset($_GET['end_lng']) ? floatval($_GET['end_lng']) : null;
if ($event_id <= 0) { echo json_encode(['success'=>false,'error'=>'invalid event_id']); exit; }
$stmt = $db->prepare('UPDATE events SET start_lat=?, start_lng=?, end_lat=?, end_lng=? WHERE id=?');
$stmt->bind_param('ddddi', $start_lat, $start_lng, $end_lat, $end_lng, $event_id);
if ($stmt->execute()) echo json_encode(['success'=>true,'event_id'=>$event_id,'start_lat'=>$start_lat,'start_lng'=>$start_lng,'end_lat'=>$end_lat,'end_lng'=>$end_lng]);
else echo json_encode(['success'=>false,'error'=>$stmt->error]);
?>