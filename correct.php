<?php
/**
 * Corrector de acentos para nombres hispanos
 *
 * Uso CLI:     php correct.php "JOSE GARCIA LOPEZ"
 *             echo "JOSE GARCIA" | php correct.php
 * Uso librería: require 'correct.php'; $c = new Corrector(); $c->correct("JOSE");
 */

class Corrector {
    private array $dict = [];

    public function __construct() {
        $path = __DIR__ . '/backend/dictionary.json';
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            $this->dict = $data['words'] ?? [];
        }
    }

    private static function removeAccents(string $s): string {
        return str_replace(
            ['Á','É','Í','Ó','Ú','Ü','á','é','í','ó','ú','ü','Ñ','ñ'],
            ['A','E','I','O','U','U','a','e','i','o','u','u','N','n'], $s
        );
    }

    private static function isAccentOnly(string $orig, string $sug): bool {
        return self::removeAccents(mb_strtoupper($orig)) === self::removeAccents(mb_strtoupper($sug));
    }

    private static function hunspellSuggest(string $word): ?string {
        $clean = preg_replace('/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ]/u', '', $word);
        if (strlen($clean) < 2) return null;
        $output = shell_exec("echo " . escapeshellarg($clean) . " | hunspell -d es_MX -a 2>/dev/null");
        if (!$output) return null;
        foreach (explode("\n", $output) as $line) {
            if (strpos($line, '&') !== 0) continue;
            $parts = explode(':', $line);
            if (count($parts) < 2) continue;
            foreach (explode(',', trim($parts[1])) as $sug) {
                $sug = trim($sug);
                if (self::isAccentOnly($clean, $sug)) return $sug;
            }
        }
        return null;
    }

    public function correct(string $name): string {
        $words = preg_split('/\s+/', trim($name));
        $result = [];
        foreach ($words as $word) {
            $upper = mb_strtoupper(trim($word));
            $key = self::removeAccents($upper);
            if (isset($this->dict[$key])) {
                $result[] = $this->dict[$key];
            } else {
                $sug = self::hunspellSuggest($word);
                $result[] = $sug ? mb_strtoupper($sug) : $word;
            }
        }
        return implode(' ', $result);
    }

    public function correctWithDetails(string $name): array {
        $words = preg_split('/\s+/', trim($name));
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
                $sug = self::hunspellSuggest($word);
                if ($sug && mb_strtoupper($sug) !== $upper) {
                    $correctedWords[] = mb_strtoupper($sug);
                    $changes[] = ['from' => $upper, 'to' => mb_strtoupper($sug)];
                } else {
                    $correctedWords[] = $word;
                }
            }
        }
        return [
            'original' => $name,
            'corrected' => implode(' ', $correctedWords),
            'changes' => $changes,
        ];
    }
}

// --- CLI entry point (solo cuando se ejecuta directamente, no al ser incluido) ---
if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === realpath(__FILE__)) {
    $input = $argc > 1 ? $argv[1] : trim(file_get_contents('php://stdin'));
    if (empty($input)) {
        fwrite(STDERR, "Uso: php correct.php \"NOMBRE SIN ACENTOS\"\n");
        exit(1);
    }
    $c = new Corrector();
    echo $c->correct($input) . "\n";
    exit(0);
}
