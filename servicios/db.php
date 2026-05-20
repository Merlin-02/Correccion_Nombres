<?php

function getDbConnection(): ?mysqli {
    static $conn = null;
    if ($conn !== null) return $conn;

    $configPaths = [
        __DIR__ . '/config.ini',
        __DIR__ . '/../config.ini',
    ];

    $config = null;
    foreach ($configPaths as $path) {
        if (file_exists($path)) {
            $config = parse_ini_file($path);
            break;
        }
    }

    if (!$config) return null;

    $conn = new mysqli($config['host'], $config['user'], $config['pass'], $config['db']);
    if ($conn->connect_error) {
        $conn = null;
        return null;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function loadDictionary(): array {
    $conn = getDbConnection();
    if (!$conn) return [];

    $result = $conn->query("SELECT word_no_accent, word_accented FROM dictionary");
    if (!$result || $result->num_rows === 0) return [];

    $dict = [];
    while ($row = $result->fetch_assoc()) {
        $dict[$row['word_no_accent']] = $row['word_accented'];
    }
    $result->free();
    return $dict;
}
