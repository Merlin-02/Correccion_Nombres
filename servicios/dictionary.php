<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';
$conn = getDbConnection();
if (!$conn) {
    echo json_encode(['error' => 'Error de conexión a BD']);
    exit;
}

$action = $_GET['action'] ?? '';

if (!in_array($action, ['list', 'add'], true)) {
    echo json_encode(['error' => 'Acción no válida']);
    exit;
}

match ($action) {
    'list' => handleList($conn),
    'add' => handleAdd($conn),
};

function handleList(mysqli $conn): void {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = max(1, min(100, (int)($_GET['per_page'] ?? 50)));
    $offset = ($page - 1) * $perPage;
    $search = trim($_GET['search'] ?? '');

    if ($search !== '') {
        $like = '%' . $conn->real_escape_string($search) . '%';
        $totalResult = $conn->query("SELECT COUNT(*) AS total FROM dictionary WHERE word_no_accent LIKE '$like' OR word_accented LIKE '$like'");
        $total = $totalResult->fetch_assoc()['total'];
        $stmt = $conn->prepare("SELECT word_no_accent, word_accented FROM dictionary WHERE word_no_accent LIKE ? OR word_accented LIKE ? ORDER BY id LIMIT ? OFFSET ?");
        $stmt->bind_param('ssii', $like, $like, $perPage, $offset);
    } else {
        $totalResult = $conn->query("SELECT COUNT(*) AS total FROM dictionary");
        $total = $totalResult->fetch_assoc()['total'];
        $stmt = $conn->prepare("SELECT word_no_accent, word_accented FROM dictionary ORDER BY id LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $perPage, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $words = [];
    while ($row = $result->fetch_assoc()) {
        $words[] = $row;
    }

    echo json_encode([
        'words' => $words,
        'page' => $page,
        'per_page' => $perPage,
        'total' => (int)$total,
        'total_pages' => (int)ceil($total / $perPage),
    ]);
}

function handleAdd(mysqli $conn): void {
    $wordNoAccent = trim($_GET['word_no_accent'] ?? $_POST['word_no_accent'] ?? '');
    $wordAccented = trim($_GET['word_accented'] ?? $_POST['word_accented'] ?? '');

    if (empty($wordNoAccent) || empty($wordAccented)) {
        echo json_encode(['error' => 'Faltan parámetros: word_no_accent y word_accented']);
        return;
    }

    require_once __DIR__ . '/../correct.php';
    $cleanKey = Corrector::removeAccents(mb_strtoupper($wordNoAccent));
    $corrected = mb_strtoupper($wordAccented);

    if ($cleanKey === $corrected) {
        echo json_encode(['error' => 'La palabra sin acentos es igual a la acentuada, no hay corrección que aplicar']);
        return;
    }

    $stmt = $conn->prepare("INSERT IGNORE INTO dictionary (word_no_accent, word_accented) VALUES (?, ?)");
    $stmt->bind_param('ss', $cleanKey, $corrected);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'word_no_accent' => $cleanKey, 'word_accented' => $corrected]);
    } else {
        echo json_encode(['error' => 'La palabra ya existe en el diccionario']);
    }
}
