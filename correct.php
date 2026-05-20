<?php

class Corrector {
    private array $dict = [];

    public function __construct() {
        $dbPath = __DIR__ . '/servicios/db.php';
        if (!file_exists($dbPath)) return;
        require_once $dbPath;
        $conn = getDbConnection();
        if (!$conn) return;
        $result = $conn->query("SELECT word_no_accent, word_accented FROM dictionary");
        if (!$result) return;
        while ($row = $result->fetch_assoc()) {
            $this->dict[$row['word_no_accent']] = $row['word_accented'];
        }
        $result->free();
    }

    public static function removeAccents(string $s): string {
        return str_replace(
            ['Á','É','Í','Ó','Ú','Ü','á','é','í','ó','ú','ü','Ñ','ñ'],
            ['A','E','I','O','U','U','a','e','i','o','u','u','N','n'], $s
        );
    }

    private static function applyFormat(string $word, string $formato): string {
        return match($formato) {
            'MAYUSCULAS' => mb_strtoupper($word),
            'minusculas' => mb_strtolower($word),
            'Capitalizado' => mb_strtoupper(mb_substr($word, 0, 1)) . mb_strtolower(mb_substr($word, 1)),
            default => mb_strtoupper($word),
        };
    }

    public function correct(string $name): string {
        $words = preg_split('/\s+/', trim($name));
        $result = [];
        foreach ($words as $word) {
            $upper = mb_strtoupper(trim($word));
            $key = self::removeAccents($upper);
            $result[] = $this->dict[$key] ?? $word;
        }
        return implode(' ', $result);
    }

    public function correctStructured(
        string $nombres,
        string $apellidos = '',
        string $orden = 'nombres_apellidos',
        string $formato = 'MAYUSCULAS'
    ): array {
        $nombres = trim($nombres);
        $apellidos = trim($apellidos);
        $combined = match($orden) {
            'apellidos_nombres' => trim($apellidos . ' ' . $nombres),
            default => trim($nombres . ' ' . $apellidos),
        };
        if ($combined === '') {
            return ['original' => '', 'corrected' => '', 'method' => 'no_changes', 'changes' => []];
        }

        $words = preg_split('/\s+/', $combined);
        $correctedWords = [];
        $changes = [];

        foreach ($words as $word) {
            $upper = mb_strtoupper(trim($word));
            $key = self::removeAccents($upper);
            if (isset($this->dict[$key])) {
                $correctedWords[] = $this->dict[$key];
                if ($this->dict[$key] !== $upper) {
                    $changes[] = ['from' => $upper, 'to' => $this->dict[$key]];
                }
            } else {
                $correctedWords[] = $word;
            }
        }

        $corrected = implode(' ', $correctedWords);
        $correctedWords = explode(' ', $corrected);
        $correctedWords = array_map(fn($w) => self::applyFormat($w, $formato), $correctedWords);
        $corrected = implode(' ', $correctedWords);

        $method = !empty($changes) ? 'dictionary' : 'no_changes';

        return [
            'original' => $combined,
            'corrected' => $corrected,
            'method' => $method,
            'changes' => $changes,
        ];
    }
}

if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === realpath(__FILE__)) {
    $args = $argv;
    array_shift($args);

    $nombres = '';
    $apellidos = '';
    $orden = 'nombres_apellidos';
    $formato = 'MAYUSCULAS';

    foreach ($args as $arg) {
        if (str_starts_with($arg, '--')) {
            $parts = explode('=', substr($arg, 2), 2);
            $key = $parts[0];
            $val = $parts[1] ?? true;
            match($key) {
                'nombres' => $nombres = $val,
                'apellidos' => $apellidos = $val,
                'orden' => $orden = $val,
                'formato' => $formato = $val,
                default => null,
            };
        }
    }

    if (empty($nombres) && !empty($args) && !str_starts_with($args[0], '--')) {
        $nombres = $args[0];
    }

    if (empty($nombres)) {
        $nombres = trim(file_get_contents('php://stdin'));
    }

    if (empty($nombres)) {
        fwrite(STDERR, "Uso: php correct.php [--nombres=STRING] [--apellidos=STRING] [--orden=nombres_apellidos|apellidos_nombres] [--formato=MAYUSCULAS|minusculas|Capitalizado]\n");
        exit(1);
    }

    $c = new Corrector();
    $result = $c->correctStructured($nombres, $apellidos, $orden, $formato);
    echo $result['corrected'] . "\n";
    exit(0);
}
