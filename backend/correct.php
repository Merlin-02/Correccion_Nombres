<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$nombres = trim($_GET['nombres'] ?? $_GET['name'] ?? '');
$apellidos = trim($_GET['apellidos'] ?? '');
$orden = $_GET['orden'] ?? 'nombres_apellidos';
$formato = $_GET['formato'] ?? 'MAYUSCULAS';

if (empty($nombres) && empty($apellidos)) {
    echo json_encode(['error' => 'Debes proporcionar nombre(s)']);
    exit;
}

require_once __DIR__ . '/../correct.php';
$c = new Corrector();
$result = $c->correctStructured($nombres, $apellidos, $orden, $formato);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
