<?php
$request = $_SERVER['REQUEST_URI'];

if (preg_match("/\/json*/", $request)) {
    require __DIR__ . '/api.php';
} else if ($request == '' || $request == '/') {
    require __DIR__ . '/views/index.html';
} else {
    http_response_code(404);
    echo "404 Not Found";
}
