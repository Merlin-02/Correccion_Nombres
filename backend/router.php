<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$frontendDir = __DIR__ . '/../frontend';

if ($uri === '/api/correct') {
    header('Access-Control-Allow-Origin: *');
    require __DIR__ . '/correct.php';
    return true;
}

if ($uri === '/api/dictionary') {
    header('Access-Control-Allow-Origin: *');
    require __DIR__ . '/dictionary.php';
    return true;
}

if ($uri === '/diccionario') {
    readfile($frontendDir . '/dictionary.html');
    return true;
}

$filePath = $frontendDir . ($uri === '/' ? '/index.html' : $uri);
if (file_exists($filePath) && !is_dir($filePath)) {
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimes = [
        'html' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript',
    ];
    header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream') . '; charset=utf-8');
    readfile($filePath);
    return true;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
return true;
