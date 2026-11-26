<?php
declare(strict_types=1);

function h(mixed $value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function db(): mysqli {
    static $connection;
    if ($connection instanceof mysqli) return $connection;
    $path = APP_ROOT . '/config/config.php';
    $config = require (is_file($path) ? $path : APP_ROOT . '/config/config.example.php');
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'],
        $config['db_name'], $config['db_port'], $config['db_socket'] ?? null);
    $connection->set_charset('utf8mb4');
    return $connection;
}
/** Las variables se envían separadas del SQL mediante consultas preparadas. */
function query(string $sql, string $types = '', array $values = []): mysqli_stmt {
    $statement = db()->prepare($sql);
    if ($types !== '') $statement->bind_param($types, ...$values);
    $statement->execute();
    return $statement;
}
function redirect(string $path): never {
    header('Location: ' . $path, true, 303);
    exit;
}
function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}
function user(): ?array { return $_SESSION['usuario'] ?? null; }
function require_user(): array {
    if (!user()) redirect('login.php');
    return user();
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . h($_SESSION['csrf']) . '">';
}
function check_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($_POST['csrf'] ?? null)
        || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403);
        render_error('No se pudo validar la solicitud', 'Recarga la página e inténtalo de nuevo.');
        exit;
    }
}
function positive_id(mixed $value): int {
    return (int) (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
}
function post_text(string $key): string {
    return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
}
function get_text(string $key, string $fallback = ''): string {
    return is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : $fallback;
}
function categories(): array {
    return query('SELECT id, nombre FROM categorias ORDER BY id')->get_result()->fetch_all(MYSQLI_ASSOC);
}
function owned_contact(int $id): ?array {
    $owner = require_user();
    return query('SELECT * FROM contactos WHERE id = ? AND id_usuario = ?', 'ii',
        [$id, (int) $owner['id']])->get_result()->fetch_assoc();
}
function not_found(): never {
    http_response_code(404);
    render_error('Contacto no disponible', 'No encontramos ese contacto en tu agenda.');
    exit;
}
function initials(string $name): string {
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $result = mb_substr($parts[0] ?? '', 0, 1);
    if (count($parts) > 1) $result .= mb_substr($parts[count($parts) - 1], 0, 1);
    return mb_strtoupper($result);
}
function like_value(string $value): string {
    return '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value) . '%';
}
function selected(mixed $value, mixed $expected): string {
    return (string) $value === (string) $expected ? ' selected' : '';
}
function contact_errors(array $data): array {
    $errors = [];
    if (mb_strlen($data['nombre']) < 2 || mb_strlen($data['nombre']) > 120)
        $errors['nombre'] = 'Escribe un nombre de 2 a 120 caracteres.';
    if (!preg_match('/^[+0-9() .-]{5,30}$/D', $data['telefono']))
        $errors['telefono'] = 'Escribe un teléfono de 5 a 30 caracteres; admite números, +, espacios, puntos, paréntesis y guiones.';
    if (mb_strlen($data['correo']) > 190 || !filter_var($data['correo'], FILTER_VALIDATE_EMAIL))
        $errors['correo'] = 'Escribe un correo electrónico válido, de hasta 190 caracteres.';
    if (mb_strlen($data['ciudad']) < 2 || mb_strlen($data['ciudad']) > 100)
        $errors['ciudad'] = 'Escribe una ciudad de 2 a 100 caracteres.';
    if (!query('SELECT id FROM categorias WHERE id = ?', 'i', [$data['id_categoria']])->get_result()->fetch_assoc())
        $errors['id_categoria'] = 'Selecciona una de las categorías disponibles.';
    if (mb_strlen($data['notas']) > 1000) $errors['notas'] = 'Las notas admiten hasta 1000 caracteres.';
    return $errors;
}
