<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri === '/') {
    include __DIR__ . '/../frontend/index.html';
    exit;
}

$file = __DIR__ . $uri;
if (file_exists($file)) {
    return false;
}

include __DIR__ . '/../frontend/index.html';