<?php
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
const CATEGORIAS = ['CRI', 'PE', 'CSIS', 'AMACSS', 'DAE'];
$config = require __DIR__ . '/../config/database.php';
function db(): PDO {
    static $pdo;
    global $config;
    if (!$pdo) {
        $dsn = $config['driver'] === 'sqlite'
            ? 'sqlite:' . $config['database']
            : "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    }
    return $pdo;
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string { return $_SESSION['_token'] ??= bin2hex(random_bytes(32)); }
function route(string $name, $id = null): string {
    $paths = ['home'=>'/', 'preguntas.index'=>'/preguntas', 'preguntas.create'=>'/preguntas/create', 'preguntas.store'=>'/preguntas', 'preguntas.edit'=>"/preguntas/$id/edit", 'preguntas.show'=>"/preguntas/$id", 'preguntas.update'=>"/preguntas/$id", 'preguntas.destroy'=>"/preguntas/$id", 'juego.index'=>'/juego', 'juego.obtener-preguntas'=>'/juego/obtener-preguntas', 'juego.verificar-respuesta'=>'/juego/verificar-respuesta'];
    // La variante con query funciona incluso sin mod_rewrite.
    return ($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '?route=' . rawurlencode($paths[$name]);
}
function old(string $key, $default = '') { global $old; return $old[$key] ?? $default; }
function view(string $name, array $data = []): void {
    $errors = $_SESSION['errors'] ?? []; $old = $_SESSION['old'] ?? [];
    $success = $_SESSION['success'] ?? null;
    unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success']);
    $GLOBALS['old'] = $old;
    extract($data);
    require __DIR__ . '/../resources/views/' . str_replace('.', '/', $name) . '.php';
}
function redirect(string $name): never { header('Location: ' . route($name), true, 303); exit; }
function json_response($data, int $status = 200): never {
    http_response_code($status); header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); exit;
}
function questions(?array $categories = null, ?int $limit = null): array {
    $sql = 'SELECT p.* FROM preguntas p'; $args = [];
    if ($categories !== null) {
        $sql .= ' WHERE EXISTS (SELECT 1 FROM categoria_pregunta c WHERE c.pregunta_id=p.id AND c.categoria IN (' . implode(',', array_fill(0, count($categories), '?')) . '))';
        $args = $categories;
    }
    $sql .= $limit !== null ? (db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? ' ORDER BY RANDOM()' : ' ORDER BY RAND()') . ' LIMIT ' . $limit : ' ORDER BY p.id';
    $stmt = db()->prepare($sql); $stmt->execute($args); $rows = $stmt->fetchAll();
    $stmt = db()->prepare('SELECT id, pregunta_id, categoria FROM categoria_pregunta WHERE pregunta_id = ? ORDER BY id');
    foreach ($rows as &$row) { $stmt->execute([$row['id']]); $row['categorias_pivot'] = $stmt->fetchAll(); }
    return $rows;
}
function question(int $id): array {
    $stmt = db()->prepare('SELECT * FROM preguntas WHERE id = ?'); $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { http_response_code(404); throw new RuntimeException('Pregunta no encontrada.', 404); }
    $stmt = db()->prepare('SELECT id, pregunta_id, categoria FROM categoria_pregunta WHERE pregunta_id = ?'); $stmt->execute([$id]);
    $row['categorias_pivot'] = $stmt->fetchAll(); return $row;
}
function question_object(array $row): object {
    $row['categoriasPivot'] = array_map(fn($c)=>(object)$c, $row['categorias_pivot']);
    return (object)$row;
}
function valid_categories($value): bool {
    return is_array($value) && count($value) > 0 && count($value) <= count(CATEGORIAS) && count(array_filter($value, fn($c)=>is_string($c) && in_array($c, CATEGORIAS, true))) === count($value);
}
