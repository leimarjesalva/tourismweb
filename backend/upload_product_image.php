<?php
error_reporting(0);
ini_set('display_errors', '0');
ob_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Admin check with proper output buffering
try {
  if (empty($_SESSION)) session_start();
  if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
  }
} catch (Exception $e) {
  ob_end_clean();
  http_response_code(401);
  echo json_encode(['error'=>'Auth error']);
  exit;
}

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

// Limit file size to 10MB
$maxBytes = 10 * 1024 * 1024;
if ($file['size'] > $maxBytes){
    ob_end_clean();
    http_response_code(413);
    echo json_encode(['error'=>'file too large']);
    exit;
}

// Validate image using getimagesize
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
$name = 'product_' . bin2hex(random_bytes(12)) . '.' . $ext;
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
