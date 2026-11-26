<?php
require_once __DIR__ . '/app/bootstrap.php';
$u = require_user(); $uid = (int) $u['id'];
$q = mb_substr(get_text('q'), 0, 100);
$field = get_text('campo', 'todos');
if (!in_array($field, ['todos', 'nombre', 'correo', 'ciudad'], true)) $field = 'todos';
$sorts = ['nombre_asc' => 'c.nombre ASC, c.id ASC', 'nombre_desc' => 'c.nombre DESC, c.id DESC',
    'ciudad_asc' => 'c.ciudad ASC, c.nombre ASC, c.id ASC', 'ciudad_desc' => 'c.ciudad DESC, c.nombre ASC, c.id ASC',
    'fecha_desc' => 'c.fecha_creacion DESC, c.id DESC', 'fecha_asc' => 'c.fecha_creacion ASC, c.id ASC'];
$order = get_text('orden', 'nombre_asc');
if (!isset($sorts[$order])) $order = 'nombre_asc';
$category = positive_id($_GET['categoria'] ?? 0);
$favorites = get_text('favoritos') === '1';
$where = ['c.id_usuario = ?']; $types = 'i'; $values = [$uid];
if ($q !== '') {
    $columns = $field === 'todos' ? ['nombre', 'correo', 'ciudad'] : [$field];
    $conditions = [];
    foreach ($columns as $column) { $conditions[] = "c.$column LIKE ? ESCAPE '!'"; $types .= 's'; $values[] = like_value($q); }
    $where[] = '(' . implode(' OR ', $conditions) . ')';
}
if ($category) { $where[] = 'c.id_categoria = ?'; $types .= 'i'; $values[] = $category; }
if ($favorites) $where[] = 'c.favorito = 1';
$filter = implode(' AND ', $where);
$total = (int) query("SELECT COUNT(*) AS n FROM contactos c WHERE $filter", $types, $values)->get_result()->fetch_assoc()['n'];
$pages = max(1, (int) ceil($total / 10));
$page = min(max(1, positive_id($_GET['pagina'] ?? 1)), $pages); $offset = ($page - 1) * 10;
$contacts = query("SELECT c.*, cat.nombre AS categoria_nombre FROM contactos c JOIN categorias cat ON cat.id = c.id_categoria WHERE $filter ORDER BY " . $sorts[$order] . ' LIMIT ? OFFSET ?', $types . 'ii', [...$values, 10, $offset])->get_result()->fetch_all(MYSQLI_ASSOC);
$stats = query('SELECT COUNT(*) AS total, COALESCE(SUM(favorito), 0) AS favoritos, COUNT(DISTINCT ciudad) AS ciudades FROM contactos WHERE id_usuario = ?', 'i', [$uid])->get_result()->fetch_assoc();
$cats = categories();
layout_start('Mis contactos', $favorites ? 'favoritos' : 'contactos');
?>
<section class="page-heading"><div><span class="eyebrow">TODO EMPIEZA CON UNA CONEXIÓN</span><h1><?= $favorites ? 'Mis favoritos' : 'Mis contactos' ?></h1><p class="muted">Encuentra, organiza y mantén cerca a tus personas.</p></div><a class="button primary" href="contacto.php"><?= icon('plus') ?> Nuevo contacto</a></section>
<section class="stats-grid" aria-label="Resumen de tu agenda">
    <div class="stat-card"><span class="stat-icon"><?= icon('people') ?></span><div><small>Contactos</small><strong><?= (int) $stats['total'] ?></strong></div><span class="stat-note">en tu agenda</span></div>
    <div class="stat-card"><span class="stat-icon amber"><?= icon('star') ?></span><div><small>Favoritos</small><strong><?= (int) $stats['favoritos'] ?></strong></div><a class="stat-note" href="index.php?favoritos=1">Ver favoritos <?= icon('arrow') ?></a></div>
    <div class="stat-card"><span class="stat-icon blue"><?= icon('city') ?></span><div><small>Ciudades</small><strong><?= (int) $stats['ciudades'] ?></strong></div><span class="stat-note">con conexiones</span></div>
</section>
<section class="filter-panel" aria-label="Buscar y ordenar contactos"><form method="get" class="filters">
    <div class="search-field"><label for="q">Buscar contactos</label><div class="input-icon"><?= icon('search') ?><input id="q" name="q" type="search" value="<?= h($q) ?>" maxlength="100" placeholder="Nombre, correo o ciudad"></div></div>
    <div><label for="campo">Buscar por</label><select id="campo" name="campo"><?php foreach (['todos'=>'Todos los campos','nombre'=>'Nombre','correo'=>'Correo','ciudad'=>'Ciudad'] as $value=>$label): ?><option value="<?= h($value) ?>"<?= selected($field,$value) ?>><?= h($label) ?></option><?php endforeach ?></select></div>
    <div><label for="categoria">Categoría</label><select id="categoria" name="categoria"><option value="">Todas las categorías</option><?php foreach($cats as $cat): ?><option value="<?= (int) $cat['id'] ?>"<?= selected($category,$cat['id']) ?>><?= h($cat['nombre']) ?></option><?php endforeach ?></select></div>
    <div class="order-field"><label for="orden">Ordenar por</label><select id="orden" name="orden"><?php foreach(['nombre_asc'=>'Nombre · A a Z','nombre_desc'=>'Nombre · Z a A','ciudad_asc'=>'Ciudad · A a Z','ciudad_desc'=>'Ciudad · Z a A','fecha_desc'=>'Más recientes','fecha_asc'=>'Más antiguos'] as $value=>$label): ?><option value="<?= h($value) ?>"<?= selected($order,$value) ?>><?= h($label) ?></option><?php endforeach ?></select></div>
    <div class="filter-bottom"><label class="checkbox-label"><input type="checkbox" name="favoritos" value="1"<?= $favorites?' checked':'' ?>> <?= icon('star') ?> Solo favoritos</label><div class="filter-actions"><a href="index.php" class="text-link">Limpiar filtros</a><button type="submit" class="button secondary"><?= icon('search') ?> Aplicar</button></div></div>
</form></section>
<section class="contacts-panel" aria-labelledby="lista-titulo"><div class="panel-heading"><h2 id="lista-titulo"><?= $favorites?'Contactos favoritos':'Tu directorio' ?> <span class="count-badge"><?= $total ?></span></h2><small><?= $total ? ($offset + 1) . '–' . min($offset + 10,$total) . ' de ' . $total : '0 resultados' ?></small></div>
<?php if ($contacts): ?>
<div class="table-scroll"><table class="contacts-table"><caption class="sr-only">Contactos de la cuenta activa</caption><thead><tr><th scope="col">Contacto</th><th scope="col">Teléfono</th><th scope="col">Ciudad</th><th scope="col">Categoría</th><th scope="col">Creado</th><th scope="col"><span class="sr-only">Favorito</span><?= icon('star') ?></th><th scope="col">Acciones</th></tr></thead><tbody>
<?php foreach($contacts as $contact): ?>
<tr data-contact-id="<?= (int) $contact['id'] ?>"><td><div class="contact-person"><span class="avatar color-<?= ((int) $contact['id_categoria']) % 5 ?>"><?= h(initials($contact['nombre'])) ?></span><div><strong><?= h($contact['nombre']) ?></strong><a href="mailto:<?= h($contact['correo']) ?>"><?= h($contact['correo']) ?></a></div></div></td><td class="phone-cell"><?= h($contact['telefono']) ?></td><td><?= h($contact['ciudad']) ?></td><td><span class="category category-<?= (int) $contact['id_categoria'] ?>"><?= h($contact['categoria_nombre']) ?></span></td><td class="date-cell"><?= h(date('d/m/Y', strtotime($contact['fecha_creacion']))) ?></td><td><form method="post" action="acciones.php"><?= csrf_field() ?><input type="hidden" name="accion" value="favorito"><input type="hidden" name="id" value="<?= (int) $contact['id'] ?>"><button class="icon-button favorite <?= $contact['favorito']?'is-favorite':'' ?>" type="submit" aria-pressed="<?= $contact['favorito']?'true':'false' ?>" aria-label="<?= h(($contact['favorito']?'Quitar de favoritos a ':'Añadir a favoritos a ') . $contact['nombre']) ?>" title="<?= $contact['favorito']?'Quitar de favoritos':'Añadir a favoritos' ?>"><?= icon('star') ?></button></form></td><td><div class="row-actions"><a class="icon-button" href="contacto.php?id=<?= (int) $contact['id'] ?>" aria-label="<?= h('Editar a ' . $contact['nombre']) ?>" title="Editar"><?= icon('edit') ?></a><a class="icon-button danger-text" href="eliminar.php?id=<?= (int) $contact['id'] ?>" aria-label="<?= h('Eliminar a ' . $contact['nombre']) ?>" title="Eliminar"><?= icon('trash') ?></a></div></td></tr>
<?php endforeach ?>
</tbody></table></div>
<?php else: ?>
<div class="empty-state"><span class="empty-icon"><?= icon($stats['total']?'search':'people') ?></span><h3><?= $stats['total']?'No encontramos coincidencias':'Tu agenda comienza aquí' ?></h3><p><?= $stats['total']?'Prueba otra búsqueda o cambia los filtros.':'Guarda tu primer contacto o explora la agenda con datos de ejemplo.' ?></p><?php if($stats['total']): ?><a class="button secondary" href="index.php">Ver todos los contactos</a><?php else: ?><div class="empty-actions"><a class="button primary" href="contacto.php"><?= icon('plus') ?> Crear contacto</a><form method="post" action="acciones.php"><?= csrf_field() ?><input type="hidden" name="accion" value="ejemplos"><button class="button secondary" type="submit">Cargar ejemplos</button></form></div><small>Los ejemplos son ficticios y se guardan solo en tu cuenta.</small><?php endif ?></div>
<?php endif ?>
<?php if($pages > 1): ?><nav class="pagination" aria-label="Páginas de contactos"><?php for($number = max(1,$page-2);$number <= min($pages,$page+2);$number++): $params=['q'=>$q,'campo'=>$field,'categoria'=>$category?:'','orden'=>$order,'favoritos'=>$favorites?'1':'','pagina'=>$number]; ?><a href="index.php?<?= h(http_build_query($params)) ?>"<?= $number===$page?' aria-current="page" class="current"':'' ?>><?= $number ?></a><?php endfor ?></nav><?php endif ?>
</section>
<p class="privacy-hint"><?= icon('shield') ?> Cada cuenta tiene su propia agenda. Tus contactos no se comparten con otros usuarios.</p>
<?php layout_end(); ?>
