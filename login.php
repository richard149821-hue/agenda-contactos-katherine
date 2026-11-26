<?php
require_once __DIR__ . '/app/bootstrap.php';
if (user()) redirect('index.php');
$errors = []; $correo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $correo = mb_strtolower(post_text('correo'));
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if ((int) ($_SESSION['login_pausa'] ?? 0) > time()) {
        $errors[] = 'Espera unos minutos antes de volver a intentarlo.';
    } else {
        $row = filter_var($correo, FILTER_VALIDATE_EMAIL) && mb_strlen($correo) <= 190
            ? query('SELECT id, nombre, correo, password FROM usuarios WHERE correo = ?', 's', [$correo])->get_result()->fetch_assoc() : null;
        // Se verifica también un hash ficticio cuando no existe el correo.
        $hash = $row['password'] ?? '$2y$12$RBIDJxO.e3NjQz5fI5fui.3kcZ20Xzoz6pKCSNb2X5GKHi/Kt48pS';
        $valid = strlen($password) <= 72 && password_verify($password, $hash);
        if ($row && $valid) {
            session_regenerate_id(true);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $_SESSION['usuario'] = ['id' => (int) $row['id'], 'nombre' => $row['nombre'], 'correo' => $row['correo']];
            $_SESSION['ultima_actividad'] = time();
            unset($_SESSION['login_fallos'], $_SESSION['login_pausa']);
            redirect('index.php');
        }
        $_SESSION['login_fallos'] = (int) ($_SESSION['login_fallos'] ?? 0) + 1;
        if ($_SESSION['login_fallos'] >= 5) { $_SESSION['login_pausa'] = time() + 300; $_SESSION['login_fallos'] = 0; }
        $errors[] = 'El correo o la contraseña no son correctos.';
    }
}
auth_start('Qué bueno verte', 'Inicia sesión y ten tus contactos a mano.');
errors_box($errors);
?>
<form method="post" class="auth-form">
    <?= csrf_field() ?>
    <label for="correo">Correo electrónico</label>
    <input id="correo" type="email" name="correo" value="<?= h($correo) ?>" maxlength="190" autocomplete="username" placeholder="tu.nombre@correo.com" required>
    <label for="password">Contraseña</label>
    <div class="password-wrap"><input id="password" type="password" name="password" autocomplete="current-password" maxlength="72" required><button type="button" class="password-toggle" data-toggle-password="password" aria-pressed="false">Mostrar</button></div>
    <button class="button primary wide" type="submit">Entrar a mi agenda <?= icon('arrow') ?></button>
</form>
<p class="switch-auth">¿Primera vez por aquí? <a href="registro.php">Crea tu cuenta</a></p>
<?php auth_end(); ?>
