<?php
require_once __DIR__ . '/app/bootstrap.php';
require_user();
$id = positive_id($_GET['id'] ?? 0);
$contact = owned_contact($id) ?? not_found();
layout_start('Eliminar contacto');
?>
<a class="back-link" href="index.php">← Volver a mis contactos</a>
<section class="delete-card"><span class="delete-icon"><?= icon('trash') ?></span><h1>¿Eliminar este contacto?</h1><p>Vas a eliminar a <strong><?= h($contact['nombre']) ?></strong> de tu agenda.</p><p class="muted"><?= h($contact['correo']) ?><br>Esta acción no se puede deshacer.</p><form method="post" action="acciones.php" class="delete-actions"><?= csrf_field() ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= $id ?>"><a class="button secondary" href="index.php">Conservar contacto</a><button class="button danger" type="submit">Sí, eliminar contacto</button></form></section>
<?php layout_end(); ?>
