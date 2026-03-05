<?php
require_once __DIR__ . '/db.php';
// Accepts multipart/form-data with file field 'image'
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['error'=>'POST required']);
    exit;
}

if (empty($_FILES['image'])){
    http_response_code(400);
    echo json_encode(['error'=>'no file uploaded']);
    exit;
}

$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK){
    http_response_code(400);
    echo json_encode(['error'=>'upload error','code'=>$file['error']]);
    exit;
}

// Limit file size to 5MB
$maxBytes = 5 * 1024 * 1024;
if ($file['size'] > $maxBytes){
    http_response_code(413);
    echo json_encode(['error'=>'file too large']);
    exit;
}

// Validate image using getimagesize
$info = @getimagesize($file['tmp_name']);
if ($info === false){
    http_response_code(400);
    echo json_encode(['error'=>'not an image']);
    exit;
}

$mime = $info['mime'];
$allowed = ['image/jpeg' => 'jpg','image/png' => 'png','image/webp' => 'webp'];
if (!isset($allowed[$mime])){
    http_response_code(400);
    echo json_encode(['error'=>'unsupported image type']);
    exit;
}

$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

$ext = $allowed[$mime];
$name = 'admin_profile_' . bin2hex(random_bytes(12)) . '.' . $ext;
$target = $uploadsDir . '/' . $name;

if (!move_uploaded_file($file['tmp_name'], $target)){
    http_response_code(500);
    echo json_encode(['error'=>'move failed']);
    exit;
}

@chmod($target, 0644);
$urlPath = 'backend/uploads/' . $name;

// Store in admin_settings
$db = get_db();
$stmt = $db->prepare('INSERT INTO admin_settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value=?');
$stmt->bind_param('sss', $key, $urlPath, $urlPath);
$key = 'admin_profile_image';
$stmt->execute();

echo json_encode(['success'=>true,'path'=>$urlPath,'file'=>$urlPath]);
?>
