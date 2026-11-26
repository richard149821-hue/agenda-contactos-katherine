<?php
declare(strict_types=1);

function icon(string $name, string $class = ''): string {
    $paths = [
        'agenda' => '<path d="M8 3h12v18H8a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3Z"/><path d="M9 3v18M3 8h4M3 15h4M13 8h4M13 12h4"/>',
        'people' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/><circle cx="9" cy="7" r="4"/>',
        'star' => '<path d="m12 3 2.78 5.63L21 9.54l-4.5 4.39 1.06 6.2L12 17.2l-5.56 2.93 1.06-6.2L3 9.54l6.22-.91L12 3Z"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="7.5"/><path d="m16 16 5 5"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="m16 3 5 5-12 12-6 1 1-6L16 3ZM14 5l5 5"/>',
        'trash' => '<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7"/>',
        'arrow' => '<path d="M5 12h14m-5-5 5 5-5 5"/>',
        'logout' => '<path d="M10 3H4v18h6M10 12h11m-5-5 5 5-5 5"/>',
        'tag' => '<path d="m3 3 8 0 10 10-8 8L3 11V3Z"/><circle cx="7.5" cy="7.5" r="1"/>',
        'city' => '<path d="M3 21V9h7V3h11v18H3ZM6 12h1M6 16h1M14 7h3M14 11h3M14 15h3M10 21V9"/>',
        'shield' => '<path d="M12 3 3 7v6c0 5 9 9 9 9s9-4 9-9V7l-9-4Z"/><path d="m8 12 3 3 5-5"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>',
    ];
    return '<svg class="icon ' . h($class) . '" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['agenda']) . '</svg>';
}
function page_head(string $title): void {
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="Agenda personal de contactos con búsqueda, categorías y favoritos"><title>' . h($title) . ' · Mi agenda</title><link rel="icon" href="assets/favicon.svg" type="image/svg+xml"><link rel="stylesheet" href="assets/app.css"><script src="assets/app.js" defer></script></head><body><a href="#contenido" class="skip-link">Saltar al contenido</a>';
}
function notice(): void {
    if (isset($_SESSION['flash'])) {
        $item = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="notice ' . h($item['type']) . '" role="status">' . h($item['message']) . '</div>';
    }
}
function layout_start(string $title, string $active = 'contactos'): void {
    $u = require_user();
    page_head($title);
    echo '<div class="app-shell"><aside class="sidebar"><a class="brand" href="index.php">' . icon('agenda') . '<span>Mi agenda<small>PERSONAS QUE IMPORTAN</small></span></a><nav aria-label="Navegación principal"><span class="nav-label">MI ESPACIO</span><a class="nav-link ' . ($active === 'contactos' ? 'active' : '') . '" href="index.php">' . icon('people') . 'Todos los contactos</a><a class="nav-link ' . ($active === 'favoritos' ? 'active' : '') . '" href="index.php?favoritos=1">' . icon('star') . 'Favoritos</a><a class="nav-link ' . ($active === 'nuevo' ? 'active' : '') . '" href="contacto.php">' . icon('plus') . 'Nuevo contacto</a></nav><div class="sidebar-note">' . icon('shield') . '<p>Tu agenda es personal.<br>Solo tú puedes gestionar tus contactos.</p></div><div class="profile"><span class="avatar profile-avatar">' . h(initials($u['nombre'])) . '</span><div><strong>' . h($u['nombre']) . '</strong><small>' . h($u['correo']) . '</small></div></div><form method="post" action="salir.php" class="logout-form">' . csrf_field() . '<button class="logout">' . icon('logout') . 'Cerrar sesión</button></form></aside><div class="workspace"><header class="topbar"><span>Agenda personal de contactos</span><span class="topbar-badge">Mi espacio privado</span></header><main id="contenido" class="main">';
    notice();
}
function layout_end(): void {
    echo '</main><footer class="footer">Mi agenda · Desarrollo Web <span>Organiza tus contactos, a tu manera.</span></footer></div></div></body></html>';
}
function auth_start(string $title, string $intro): void {
    page_head($title);
    echo '<div class="auth-shell"><aside class="auth-story"><a class="brand" href="login.php">' . icon('agenda') . '<span>Mi agenda<small>PERSONAS QUE IMPORTAN</small></span></a><div class="story-copy"><span class="eyebrow">MENOS DESORDEN, MÁS CONEXIONES</span><h1>Un lugar para<br>tus personas.</h1><p>Familia, amistades, trabajo y universidad.<br>Encuentra cada contacto cuando lo necesitas.</p><div class="story-card"><span class="avatar">AC</span><div><strong>Ana Cueva</strong><small>ana.cueva@example.com</small></div>' . icon('star', 'filled') . '</div><div class="story-tags"><span>Familia</span><span>Amigos</span><span>Trabajo</span></div></div><small class="story-bottom">Contactos de ejemplo · Datos ficticios</small></aside><main id="contenido" class="auth-main"><div class="auth-card"><span class="eyebrow">TU ESPACIO PERSONAL</span><h2>' . h($title) . '</h2><p class="muted">' . h($intro) . '</p>';
    notice();
}
function auth_end(): void {
    echo '<p class="auth-foot">Proyecto académico · Desarrollo Web · UTPL</p></div></main></div></body></html>';
}
function render_error(string $title, string $message): void {
    page_head($title);
    echo '<main id="contenido" class="error-page"><div class="error-symbol">' . icon('agenda') . '</div><h1>' . h($title) . '</h1><p>' . h($message) . '</p><a class="button primary" href="index.php">Volver a mi agenda</a></main></body></html>';
}
function errors_box(array $errors): void {
    if (!$errors) return;
    echo '<div class="notice error" role="alert"><strong>Revisa los datos del formulario.</strong><ul>';
    foreach ($errors as $error) echo '<li>' . h($error) . '</li>';
    echo '</ul></div>';
}
