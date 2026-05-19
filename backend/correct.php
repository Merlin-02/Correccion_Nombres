<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$input = isset($_GET['name']) ? trim($_GET['name']) : '';
if (empty($input)) {
    echo json_encode(['error' => 'Debes proporcionar un nombre']);
    exit;
}

$dictPath = __DIR__ . '/dictionary.json';
$dict = [];
if (file_exists($dictPath)) {
    $data = json_decode(file_get_contents($dictPath), true);
    $dict = $data['words'] ?? [];
}

function removeAccents($s) {
    return str_replace(
        ['Á','É','Í','Ó','Ú','Ü','á','é','í','ó','ú','ü','Ñ','ñ'],
        ['A','E','I','O','U','U','a','e','i','o','u','u','N','n'],
        $s
    );
}

function isAccentOnly($orig, $sug) {
    return removeAccents(mb_strtoupper($orig)) === removeAccents(mb_strtoupper($sug));
}

function hunspellSuggest($word) {
    $clean = preg_replace('/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ]/u', '', $word);
    if (strlen($clean) < 2) return null;

    $output = shell_exec("echo " . escapeshellarg($clean) . " | hunspell -d es_MX -a 2>/dev/null");
    if (!$output) return null;

    foreach (explode("\n", $output) as $line) {
        if (strpos($line, '&') !== 0) continue;
        $parts = explode(':', $line);
        if (count($parts) < 2) continue;
        $suggestions = explode(',', trim($parts[1]));
        foreach ($suggestions as $sug) {
            $sug = trim($sug);
            if (isAccentOnly($clean, $sug)) return $sug;
        }
    }
    return null;
}

$words = preg_split('/\s+/', trim($input));
$correctedWords = [];
$changes = [];
$usedHunspell = false;

foreach ($words as $word) {
    $upper = mb_strtoupper(trim($word));
    $key = removeAccents($upper);

    if (isset($dict[$key])) {
        $correctedWords[] = $dict[$key];
        if ($dict[$key] !== $upper) {
            $changes[] = ['from' => $upper, 'to' => $dict[$key]];
        }
    } else {
        $suggestion = hunspellSuggest($word);
        if ($suggestion && mb_strtoupper($suggestion) !== $upper) {
            $correctedWords[] = mb_strtoupper($suggestion);
            $changes[] = ['from' => $upper, 'to' => mb_strtoupper($suggestion)];
            $usedHunspell = true;
        } else {
            $correctedWords[] = $word;
        }
    }
}

$method = 'no_changes';
if (!empty($changes)) {
    $method = $usedHunspell ? 'hunspell' : 'dictionary';
}

echo json_encode([
    'original' => $input,
    'corrected' => implode(' ', $correctedWords),
    'method' => $method,
    'changes' => $changes
], JSON_UNESCAPED_UNICODE);
