<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

// Guest feedback image upload - no admin required
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['error'=>'POST required']);
    exit;
}

if (empty($_FILES['file'])){
    http_response_code(400);
    echo json_encode(['error'=>'no file uploaded']);
    exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK){
    http_response_code(400);
    echo json_encode(['error'=>'upload error','code'=>$file['error']]);
    exit;
}

// Limit file size to 3MB for feedback images
$maxBytes = 3 * 1024 * 1024;
if ($file['size'] > $maxBytes){
    http_response_code(413);
    echo json_encode(['error'=>'file too large (max 3MB)']);
    exit;
}

// Validate image using getimagesize (safer than trusting MIME)
$info = @getimagesize($file['tmp_name']);
if ($info === false){
    http_response_code(400);
    echo json_encode(['error'=>'not a valid image']);
    exit;
}

$mime = $info['mime'];
$allowed = ['image/jpeg' => 'jpg','image/png' => 'png','image/webp' => 'webp','image/gif' => 'gif'];
if (!isset($allowed[$mime])){
    http_response_code(400);
    echo json_encode(['error'=>'unsupported image type']);
    exit;
}

$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

$ext = $allowed[$mime];
$name = 'fb_' . bin2hex(random_bytes(12)) . '.' . $ext;
$target = $uploadsDir . '/' . $name;

if (!move_uploaded_file($file['tmp_name'], $target)){
    http_response_code(500);
    echo json_encode(['error'=>'file save failed']);
    exit;
}

// Set restrictive permissions
@chmod($target, 0644);

$urlPath = '../backend/uploads/' . $name;

echo json_encode(['success'=>true,'path'=>$urlPath]);

?>
