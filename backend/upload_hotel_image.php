<?php
ob_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', '0');

require_once __DIR__ . '/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['error'=>'POST required']);
    exit;
}

if (empty($_FILES['image'])){
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['error'=>'no file uploaded']);
    exit;
}

$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK){
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['error'=>'upload error','code'=>$file['error']]);
    exit;
}

$maxBytes = 10 * 1024 * 1024;
if ($file['size'] > $maxBytes){
    ob_end_clean();
    http_response_code(413);
    echo json_encode(['error'=>'file too large']);
    exit;
}

$info = @getimagesize($file['tmp_name']);
if ($info === false){
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['error'=>'not an image']);
    exit;
}

$mime = $info['mime'];
$allowed = ['image/jpeg' => 'jpg','image/png' => 'png','image/webp' => 'webp'];
if (!isset($allowed[$mime])){
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['error'=>'unsupported image type']);
    exit;
}

$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

$ext = $allowed[$mime];
$name = 'hotel_' . bin2hex(random_bytes(12)) . '.' . $ext;
$target = $uploadsDir . '/' . $name;

if (!move_uploaded_file($file['tmp_name'], $target)){
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error'=>'move failed']);
    exit;
}

@chmod($target, 0644);
$urlPath = 'backend/uploads/' . $name;

ob_end_clean();
http_response_code(200);
echo json_encode(['success'=>true,'path'=>$urlPath,'file'=>$urlPath]);
?>
