<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$uri = strtok($uri, '?');

// ✅ MOVE THIS TO TOP - Serve backend uploads FIRST
if (strpos($uri, '/backend/uploads/') === 0) {
    $file = '/app' . $uri;
    if (file_exists($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png', 'mp4' => 'video/mp4',
            'gif' => 'image/gif', 'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
    http_response_code(404);
    exit;
}

// Then backend PHP files
if (strpos($uri, '/backend/') === 0 && substr($uri, -4) === '.php') {
    // ... rest unchanged