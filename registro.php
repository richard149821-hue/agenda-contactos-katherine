<?php
require_once __DIR__ . '/app/bootstrap.php';
if (user()) redirect('index.php');
$errors = []; $nombre = ''; $correo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $nombre = post_text('nombre'); $correo = mb_strtolower(post_text('correo'));
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirmation = is_string($_POST['confirmacion'] ?? null) ? $_POST['confirmacion'] : '';
    if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 120) $errors[] = 'El nombre debe tener entre 2 y 120 caracteres.';
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 190) $errors[] = 'Escribe un correo electrónico válido.';
    if (mb_strlen($password) < 10 || strlen($password) > 72) $errors[] = 'La contraseña debe tener al menos 10 caracteres y un máximo de 72 bytes.';
    if ($password !== $confirmation) $errors[] = 'Las contraseñas no coinciden.';
    if (!$errors) {
        try {
            query('INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)', 'sss',
                [$nombre, $correo, password_hash($password, PASSWORD_DEFAULT)]);
            flash('Tu cuenta está lista. Ya puedes iniciar sesión.');
            redirect('login.php');
        } catch (mysqli_sql_exception $error) {
            if ($error->getCode() !== 1062) throw $error;
            $errors[] = 'Ese correo ya está registrado. Inicia sesión o utiliza otro.';
        }
    }
}
auth_start('Crea tu cuenta', 'Comienza una agenda que solo tú puedes gestionar.');
errors_box($errors);
?>
<form method="post" class="auth-form">
    <?= csrf_field() ?>
    <label for="nombre">Nombre completo</label>
    <input id="nombre" name="nombre" value="<?= h($nombre) ?>" minlength="2" maxlength="120" autocomplete="name" required>
    <label for="correo">Correo electrónico</label>
    <input id="correo" name="correo" type="email" value="<?= h($correo) ?>" maxlength="190" autocomplete="email" required>
    <label for="password">Contraseña</label>
    <div class="password-wrap"><input id="password" name="password" type="password" minlength="10" maxlength="72" autocomplete="new-password" aria-describedby="clave-ayuda" required><button type="button" class="password-toggle" data-toggle-password="password" aria-pressed="false">Mostrar</button></div>
    <small id="clave-ayuda" class="field-help">Utiliza al menos 10 caracteres. No uses contraseñas de otras cuentas.</small>
    <label for="confirmacion">Repite la contraseña</label>
    <input id="confirmacion" name="confirmacion" type="password" minlength="10" maxlength="72" autocomplete="new-password" required>
    <button class="button primary wide" type="submit">Crear cuenta <?= icon('arrow') ?></button>
</form>
<p class="switch-auth">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
<?php auth_end(); ?>
