<?php
require_once __DIR__ . '/app/bootstrap.php';
$u = require_user();
$id = positive_id($_GET['id'] ?? 0);
$data = ['nombre'=>'','telefono'=>'','correo'=>'','ciudad'=>'','id_categoria'=>'','favorito'=>0,'notas'=>''];
if ($id) { $data = owned_contact($id) ?? not_found(); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $data = ['nombre'=>post_text('nombre'),'telefono'=>post_text('telefono'), 'correo'=>mb_strtolower(post_text('correo')),
        'ciudad'=>post_text('ciudad'),'id_categoria'=>positive_id($_POST['id_categoria']??0),
        'favorito'=>post_text('favorito')==='1'?1:0,'notas'=>post_text('notas')];
    $errors = contact_errors($data);
    if (!$errors) {
        $values = [$data['nombre'],$data['telefono'],$data['correo'],$data['ciudad'],$data['id_categoria'],$data['favorito'],$data['notas']];
        if ($id) {
            query('UPDATE contactos SET nombre=?, telefono=?, correo=?, ciudad=?, id_categoria=?, favorito=?, notas=? WHERE id=? AND id_usuario=?', 'ssssiisii', [...$values,$id,(int)$u['id']]);
        } else {
            query('INSERT INTO contactos (nombre, telefono, correo, ciudad, id_categoria, favorito, notas, id_usuario) VALUES (?,?,?,?,?,?,?,?)', 'ssssiisi', [...$values,(int)$u['id']]);
        }
        flash($id?'El contacto se actualizó correctamente.':'El contacto se guardó en tu agenda.');
        redirect('index.php');
    }
}
$cats = categories();
layout_start($id?'Editar contacto':'Nuevo contacto', $id?'contactos':'nuevo');
?>
<a class="back-link" href="index.php">← Volver a mis contactos</a>
<section class="page-heading"><div><span class="eyebrow">UN ESPACIO PARA CADA PERSONA</span><h1><?= $id?'Editar contacto':'Nuevo contacto' ?></h1><p class="muted">Guarda sus datos y encuentra la mejor forma de mantener el contacto.</p></div></section>
<div class="form-layout"><section class="form-card"><?php errors_box($errors) ?><form method="post" class="contact-form">
<?= csrf_field() ?>
<h2>Información del contacto</h2><p class="muted form-intro">Los campos marcados con * son obligatorios.</p>
<div class="form-grid">
<?php foreach(['nombre'=>['Nombre completo','text','120','Ej. Ana Cueva'],'correo'=>['Correo electrónico','email','190','Ej. ana.cueva@example.com'],'telefono'=>['Teléfono','tel','30','Ej. 099 000 0000'],'ciudad'=>['Ciudad','text','100','Ej. Loja']] as $key=>$spec): ?>
<div class="form-field"><label for="<?= $key ?>"><?= h($spec[0]) ?> *</label><input id="<?= $key ?>" name="<?= $key ?>" type="<?= $spec[1] ?>" maxlength="<?= $spec[2] ?>" value="<?= h($data[$key]) ?>" placeholder="<?= h($spec[3]) ?>" required<?= isset($errors[$key])?' aria-invalid="true" aria-describedby="error-'.$key.'"':'' ?>><?php if(isset($errors[$key])): ?><small class="field-error" id="error-<?= $key ?>"><?= h($errors[$key]) ?></small><?php endif ?></div>
<?php endforeach ?>
<div class="form-field"><label for="id_categoria">Categoría *</label><select id="id_categoria" name="id_categoria" required><option value="">Selecciona una categoría</option><?php foreach($cats as $cat): ?><option value="<?= (int)$cat['id'] ?>"<?= selected($data['id_categoria'],$cat['id']) ?>><?= h($cat['nombre']) ?></option><?php endforeach ?></select></div>
<div class="form-field favorite-choice"><label class="checkbox-label"><input type="checkbox" name="favorito" value="1"<?= $data['favorito']?' checked':'' ?>> <?= icon('star') ?> Marcar como favorito</label><small class="field-help">Ten a mano los contactos que más utilizas.</small></div>
<div class="form-field full-width"><label for="notas">Notas <span class="optional">opcional</span></label><textarea id="notas" name="notas" rows="4" maxlength="1000" placeholder="Un detalle útil para recordar…"><?= h($data['notas']) ?></textarea><small class="field-help">Hasta 1000 caracteres.</small></div>
</div><div class="form-buttons"><a class="button secondary" href="index.php">Cancelar</a><button class="button primary" type="submit"><?= icon('plus') ?> <?= $id?'Guardar cambios':'Guardar contacto' ?></button></div>
</form></section><aside class="form-aside"><span class="form-aside-icon"><?= icon('tag') ?></span><h3>Cada persona, en su lugar.</h3><p>Organiza tus contactos en Familia, Amigos, Trabajo, Universidad u Otro.</p><p>Después podrás combinar categorías, búsqueda y favoritos para encontrar a quien necesitas.</p><div class="aside-tip"><?= icon('shield') ?><span>Este contacto quedará asociado únicamente a tu cuenta.</span></div></aside></div>
<?php layout_end(); ?>
