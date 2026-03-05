<?php
header('Content-Type: application/json');
require_once 'db.php';
require_admin();

try {
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (!isset($_FILES['image']) || empty($_FILES['image']['tmp_name'])) {
        $error_msg = 'No image uploaded';
        if (isset($_FILES['image']['error']) && $_FILES['image']['error'] !== 0) {
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
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $allowed_types)) {
        j(['success' => false, 'error' => 'Invalid image type']);
    }

    $target_filename = 'experience_' . time() . '_' . basename($file['name']);
    $target_path = $upload_dir . $target_filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        j(['success' => true, 'path' => 'backend/uploads/' . $target_filename]);
    } else {
        j(['success' => false, 'error' => 'Upload failed']);
    }
} catch (Exception $e) {
    j(['success' => false, 'error' => $e->getMessage()]);
}
?>