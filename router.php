<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Route backend PHP files
if (strpos($uri, '/backend/') === 0) {
    $file = __DIR__ . $uri;
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// Serve frontend files
$file = __DIR__ . '/frontend' . $uri;
if ($uri === '/' || !file_exists($file)) {
    include __DIR__ . '/frontend/index.html';
    exit;
}
return false;