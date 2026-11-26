<?php
require_once __DIR__ . '/app/bootstrap.php';
$u = require_user(); check_csrf();
$uid = (int)$u['id']; $action = post_text('accion');
if ($action === 'ejemplos') {
    // La fila del propietario se bloquea para evitar duplicados en solicitudes simultáneas.
    db()->begin_transaction();
    try {
        query('SELECT id FROM usuarios WHERE id = ? FOR UPDATE', 'i', [$uid])->get_result();
        $count = (int)query('SELECT COUNT(*) AS n FROM contactos WHERE id_usuario = ?', 'i', [$uid])->get_result()->fetch_assoc()['n'];
        if ($count === 0) {
            $examples = [
                ['Ana Cueva','0990000001','ana.cueva@example.com','Loja',1,1,'Contacto de ejemplo · datos ficticios'],
                ['Bruno Ríos','0990000002','bruno.rios@example.com','Quito',2,0,'Contacto de ejemplo · datos ficticios'],
                ['Carla Torres','0990000003','carla.torres@example.com','Cuenca',3,1,'Contacto de ejemplo · datos ficticios'],
                ['Diego Mora','0990000004','diego.mora@example.com','Loja',4,0,'Contacto de ejemplo · datos ficticios'],
                ['Elena Paz','0990000005','elena.paz@example.com','Zamora',1,0,'Contacto de ejemplo · datos ficticios'],
                ['Fabián León','0990000006','fabian.leon@example.com','Guayaquil',5,0,'Contacto de ejemplo · datos ficticios'],
                ['Gabriela Ruiz','0990000007','gabriela.ruiz@example.com','Quito',2,1,'Contacto de ejemplo · datos ficticios'],
                ['Hugo Vera','0990000008','hugo.vera@example.com','Cuenca',3,0,'Contacto de ejemplo · datos ficticios'],
            ];
            foreach($examples as $row) query('INSERT INTO contactos (nombre,telefono,correo,ciudad,id_categoria,favorito,notas,id_usuario) VALUES (?,?,?,?,?,?,?,?)', 'ssssiisi', [...$row,$uid]);
            flash('Se añadieron 8 contactos ficticios para explorar la agenda. Puedes editarlos o eliminarlos.');
        } else flash('Los ejemplos solo se cargan cuando tu agenda está vacía.', 'info');
        db()->commit();
    } catch (Throwable $error) { db()->rollback(); throw $error; }
    redirect('index.php');
}
if (!in_array($action, ['eliminar','favorito'], true)) {
    http_response_code(400); render_error('Acción no válida','Vuelve a tu agenda e inténtalo nuevamente.'); exit;
}
$id = positive_id($_POST['id'] ?? 0);
owned_contact($id) ?? not_found();
if ($action === 'eliminar') {
    query('DELETE FROM contactos WHERE id = ? AND id_usuario = ?', 'ii', [$id,$uid]);
    flash('El contacto se eliminó de tu agenda.');
} else {
    query('UPDATE contactos SET favorito = 1 - favorito WHERE id = ? AND id_usuario = ?', 'ii', [$id,$uid]);
    flash('Se actualizó el estado de favorito.');
}
redirect('index.php');
