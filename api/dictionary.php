<?php
$config = parse_ini_file(__DIR__ . '/../config.ini');
$conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
$conn->set_charset('utf8mb4');

$dict = [];

// Word-level dictionary
$result = $conn->query("
    SELECT DISTINCT w_upper AS raw, w_upper_corrected AS corrected FROM (
        SELECT DISTINCT
            UPPER(SUBSTRING_INDEX(SUBSTRING_INDEX(nombre_completo, ' ', n.digit+1), ' ', -1)) AS w_upper,
            UPPER(SUBSTRING_INDEX(SUBSTRING_INDEX(correccion, ' ', n.digit+1), ' ', -1)) AS w_upper_corrected
        FROM personas
        JOIN (SELECT 0 digit UNION SELECT 1 UNION SELECT 2) n
        ON LENGTH(correccion) - LENGTH(REPLACE(correccion, ' ', '')) >= n.digit
    ) sub WHERE w_upper != ''
");
while ($row = $result->fetch_assoc()) {
    $raw = trim($row['raw']);
    $corrected = trim($row['corrected']);
    if ($raw !== '' && $corrected !== '') {
        if (!isset($dict[$raw]) || $corrected !== $raw) {
            $dict[$raw] = $corrected;
        }
    }
}

// Full name dictionary
$result2 = $conn->query("SELECT DISTINCT UPPER(nombre_completo) AS raw, UPPER(correccion) AS corrected FROM personas");
while ($row = $result2->fetch_assoc()) {
    $raw = trim($row['raw']);
    $corrected = trim($row['corrected']);
    if ($raw !== '' && $corrected !== '') {
        $dict[$raw] = $corrected;
    }
}

$output = [
    'words' => $dict,
    'totalWords' => count($dict)
];

file_put_contents(__DIR__ . '/../public/dictionary.json', json_encode($output, JSON_UNESCAPED_UNICODE));
echo "Dictionary built: " . count($dict) . " entries\n";

$conn->close();
