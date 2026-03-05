<?php
header('Content-Type: application/json');
require_once 'db.php';
require_admin();

// raise limits to accommodate large videos; host may override
@ini_set('post_max_size', '512M');
@ini_set('upload_max_filesize', '512M');
@ini_set('max_execution_time', '300');
@ini_set('max_input_time', '300');

try {
    // prevent huge requests: check against PHP post_max_size
    $max = ini_get('post_max_size');
    $unit = strtoupper(substr($max, -1));
    $mult = 1;
    if ($unit === 'G') $mult = 1024*1024*1024;
    elseif ($unit === 'M') $mult = 1024*1024;
    elseif ($unit === 'K') $mult = 1024;
    $bytes = intval($max) * $mult;
    if (!empty($_SERVER['CONTENT_LENGTH']) && intval($_SERVER['CONTENT_LENGTH']) > $bytes) {
        j(['success'=>false, 'error'=>'POST size exceeds server limit (post_max_size = '.$max.')']);
    }
    // log upload_max_filesize for reference
    $umax = ini_get('upload_max_filesize');
    $uunit = strtoupper(substr($umax, -1));
    $mult2 = 1;
    if ($uunit === 'G') $mult2 = 1024*1024*1024;
    elseif ($uunit === 'M') $mult2 = 1024*1024;
    elseif ($uunit === 'K') $mult2 = 1024;
    $umax_bytes = intval($umax) * $mult2;
    error_log('DEBUG: server upload_max_filesize='.$umax.' ('.$umax_bytes.' bytes)');

    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Debug: Log what we received
    $debug_info = [
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'unknown',
        'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? 'unknown',
        'FILES_keys' => array_keys($_FILES),
        'FILES_image_exists' => isset($_FILES['image']),
        'FILES_full' => $_FILES
    ];
    error_log('DEBUG: Upload request: ' . json_encode($debug_info));
    
    if (!isset($_FILES['image'])) {
        error_log('DEBUG: $_FILES[image] not set. Available keys: ' . json_encode(array_keys($_FILES)));
        j(['success' => false, 'error' => 'No image uploaded (FILE not found)', 'debug' => $debug_info]);
    }
    
    if (empty($_FILES['image']['tmp_name'])) {
        error_log('DEBUG: tmp_name is empty. Error code: ' . ($_FILES['image']['error'] ?? 'unknown'));
        $error_msg = 'No image uploaded';
        if (isset($_FILES['image']['error'])) {
            $errors = [
                1 => 'File exceeds upload_max_filesize',
                2 => 'File exceeds MAX_FILE_SIZE',
                3 => 'File partially uploaded',
                4 => 'No file uploaded',
                6 => 'Missing temp folder',
                7 => 'Cannot write to disk',
                8 => 'Extension blocked'
            ];
            $error_msg = $errors[$_FILES['image']['error']] ?? 'Upload error code: ' . $_FILES['image']['error'];
        }
        j(['success' => false, 'error' => $error_msg]);
    }

    $file = $_FILES['image'];
    $mime = $file['type'];
    
    // Get file extension from original name
    $original_name = $file['name'];
    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    
    error_log('DEBUG: File upload attempt - Name: ' . $original_name . ', MIME: ' . $mime . ', Ext: ' . $file_ext . ', Size: ' . $file['size']);
    if (isset($umax_bytes) && $file['size'] > $umax_bytes) {
        error_log('DEBUG: File exceeds upload_max_filesize limit');
        j(['success'=>false,'error'=>'File exceeds server upload_max_filesize limit ('.$umax.')']);
    }
    
    // Allow images and MP4 videos - check both MIME and extension
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'application/octet-stream'];
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4'];
    
    // Check if file type is allowed (either by MIME or by extension)
    $mime_ok = in_array($mime, $allowed_mimes);
    $ext_ok = in_array($file_ext, $allowed_exts);
    
    error_log('DEBUG: MIME check - mime_ok=' . ($mime_ok ? 'true' : 'false') . ', ext_ok=' . ($ext_ok ? 'true' : 'false'));
    
    if (!$mime_ok && !$ext_ok) {
        $error_detail = 'Invalid file type. Only images (JPEG, PNG, GIF, WebP) and MP4 videos allowed. File: ' . $original_name . ', MIME: ' . $mime . ', Ext: ' . $file_ext;
        error_log('DEBUG: ' . $error_detail);
        j(['success' => false, 'error' => $error_detail]);
    }

    // Determine file extension based on original or MIME type
    $extension_map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'video/mp4' => 'mp4'
    ];
    
    // Use file extension if available, otherwise use MIME
    if (in_array($file_ext, $allowed_exts)) {
        $ext = $file_ext;
    } else {
        $ext = $extension_map[$mime] ?? 'bin';
    }
    
    $target_filename = 'festival_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target_path = $upload_dir . $target_filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        error_log('DEBUG: File uploaded successfully: ' . $target_filename);
        // also mirror to frontend for direct serving
        $frontend_dir = __DIR__ . '/../frontend/img/uploads/';
        if (!is_dir($frontend_dir)) {
            mkdir($frontend_dir, 0755, true);
        }
        @copy($target_path, $frontend_dir . $target_filename);
        j(['success' => true, 'path' => 'backend/uploads/' . $target_filename]);
    } else {
        error_log('DEBUG: move_uploaded_file failed for: ' . $target_filename);
        j(['success' => false, 'error' => 'Upload failed']);
    }
} catch (Exception $e) {
    error_log('DEBUG: Exception: ' . $e->getMessage());
    j(['success' => false, 'error' => $e->getMessage()]);
}
?>