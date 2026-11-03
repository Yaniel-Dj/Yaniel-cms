<?php
// Incluido desde admin/index.php

$mensaje = '';
$tipo = '';

// Cargar categorías
$categorias_admin = [];
try {
    $pdo_shop_cat = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt_cat = $pdo_shop_cat->query("SELECT id, nombre, nombre_en FROM web_shop_categories ORDER BY id ASC");
    $categorias_admin = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// AÑADIR ITEM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_shop_item'])) {
    $item_entry  = (int)($_POST['item_entry'] ?? 0);
    $nombre      = trim($_POST['item_nombre'] ?? '');
    $nombre_en   = trim($_POST['item_nombre_en'] ?? '');
    $precio_oro  = (float)($_POST['item_precio'] ?? 0);
    $activo      = isset($_POST['item_activo']) ? 1 : 0;
    $categoria_id = (int)($_POST['item_categoria'] ?? 1);

    if ($item_entry <= 0 || empty($nombre) || $precio_oro <= 0) {
        $mensaje = t('invalid_data'); $tipo = 'error';
    } else {
        try {
            $pdo_world = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . WORLD_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt_w = $pdo_world->prepare("SELECT name FROM item_template WHERE entry = ?");
            $stmt_w->execute([$item_entry]);
            $item_world = $stmt_w->fetch(PDO::FETCH_ASSOC);
            if (!$item_world) {
                $mensaje = t('item_not_exist') . ' ' . $item_entry; $tipo = 'error';
            } else {
                $precio_cobre = (int)round($precio_oro * COBRE_POR_ORO);
                $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $stmt = $pdo_shop->prepare("INSERT INTO web_shop_items (item_entry, nombre, nombre_en, precio, activo, categoria_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$item_entry, $nombre, $nombre_en, $precio_cobre, $activo, $categoria_id]);
                $mensaje = t('item_added') . ' (' . htmlspecialchars($item_world['name']) . ' — ' . $precio_oro . ' ' . t('gold') . ')'; $tipo = 'exito';
            }
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// EDITAR ITEM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_shop_item'])) {
    $edit_id     = (int)($_POST['edit_id'] ?? 0);
    $nombre      = trim($_POST['edit_nombre'] ?? '');
    $nombre_en   = trim($_POST['edit_nombre_en'] ?? '');
    $precio_oro  = (float)($_POST['edit_precio'] ?? 0);
    $activo      = isset($_POST['edit_activo']) ? 1 : 0;
    $categoria_id = (int)($_POST['edit_categoria'] ?? 1);

    if ($edit_id <= 0 || empty($nombre) || $precio_oro <= 0) {
        $mensaje = t('invalid_data'); $tipo = 'error';
    } else {
        try {
            $precio_cobre = (int)round($precio_oro * COBRE_POR_ORO);
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo_shop->prepare("UPDATE web_shop_items SET nombre = ?, nombre_en = ?, precio = ?, activo = ?, categoria_id = ? WHERE id = ?");
            $stmt->execute([$nombre, $nombre_en, $precio_cobre, $activo, $categoria_id, $edit_id]);
            $mensaje = t('saved_successfully'); $tipo = 'exito';
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// BORRAR ITEM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_shop_item'])) {
    $del_id = (int)($_POST['delete_id'] ?? 0);
    if ($del_id > 0) {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo_shop->prepare("DELETE FROM web_shop_items WHERE id = ?");
            $stmt->execute([$del_id]);
            $mensaje = t('item_deleted'); $tipo = 'exito';
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// ACTIVAR / DESACTIVAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_shop_item'])) {
    $tog_id = (int)($_POST['toggle_id'] ?? 0);
    if ($tog_id > 0) {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo_shop->prepare("UPDATE web_shop_items SET activo = 1 - activo WHERE id = ?");
            $stmt->execute([$tog_id]);
        } catch (PDOException $e) {}
    }
}

// CARGAR ITEMS
$productos_admin = [];
try {
    $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt_all = $pdo_shop->query("SELECT i.*, c.nombre AS categoria_nombre, c.nombre_en AS categoria_nombre_en FROM web_shop_items i LEFT JOIN web_shop_categories c ON i.categoria_id = c.id ORDER BY i.id DESC");
    $productos_admin = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<?php if ($mensaje): ?>
    <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="admin-section">
    <h3><?= t('add_item') ?></h3>
    <form method="POST">
        <label for="item_entry"><?= t('item_id') ?></label>
        <input type="number" id="item_entry" name="item_entry" placeholder="Ej: 50818" required min="1">
        
        <label for="item_nombre"><?= t('item_name') ?> (ES)</label>
        <input type="text" id="item_nombre" name="item_nombre" placeholder="Ej: Riendas de Invencible" required maxlength="100">

        <label for="item_nombre_en"><?= t('item_name') ?> (EN)</label>
        <input type="text" id="item_nombre_en" name="item_nombre_en" placeholder="Ej: Reins of the Invincible" maxlength="100">
        
        <label for="item_precio"><?= t('item_price') ?></label>
        <input type="number" id="item_precio" name="item_precio" placeholder="Ej: 1000" required min="1" step="0.01">

        <label for="item_categoria"><?= t('category') ?></label>
        <select id="item_categoria" name="item_categoria" required>
            <?php if (empty($categorias_admin)): ?>
                <option value="1"><?= t('all_categories') ?></option>
            <?php else: ?>
                <?php foreach ($categorias_admin as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?> / <?= htmlspecialchars($c['nombre_en'] ?? '') ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <div class="checkbox-row">
            <input type="checkbox" id="item_activo" name="item_activo" checked>
            <label for="item_activo"><?= t('item_active') ?></label>
        </div>
        <button type="submit" name="add_shop_item"><?= t('add_btn') ?></button>
    </form>
</div>

<div class="admin-section">
    <h3><?= t('manage_items') ?></h3>
    <?php if (empty($productos_admin)): ?>
        <p style="color:#9db4d8; text-align:center; font-size:13px;"><?= t('no_items_shop') ?></p>
    <?php else: ?>
        <table class="admin-tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= t('item_id') ?></th>
                    <th><?= t('item_name') ?> (ES)</th>
                    <th><?= t('item_name') ?> (EN)</th>
                    <th><?= t('category') ?></th>
                    <th><?= t('price') ?></th>
                    <th><?= t('active') ?></th>
                    <th><?= t('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos_admin as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= $p['item_entry'] ?></td>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['nombre_en'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['categoria_nombre'] ?? '-') ?></td>
                        <td><?= number_format($p['precio'] / COBRE_POR_ORO) ?> <?= t('gold') ?></td>
                        <td>
                            <form method="POST" style="margin:0; display:inline;">
                                <input type="hidden" name="toggle_id" value="<?= $p['id'] ?>">
                                <button type="submit" name="toggle_shop_item" class="btn-borrar" style="opacity:.85;" title="<?= t('active') ?>">
                                    <?= $p['activo'] ? '✅' : '❌' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div style="display:flex; gap:5px; flex-wrap:wrap;">
                                <button type="button" class="btn-borrar" style="background:rgba(30,91,198,0.3); border-color:#1e5bc6; color:#9dd8ff;" onclick="mostrarEdicion(<?= $p['id'] ?>)"><?= t('edit') ?></button>
                                <form method="POST" style="margin:0;" onsubmit="return confirm(<?= json_encode(t('confirm_delete')) ?>);">
                                    <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                                    <button type="submit" name="delete_shop_item" class="btn-borrar"><?= t('delete') ?></button>
                                </form>
                            </div>

                            <div id="edit-form-<?= $p['id'] ?>" style="display:none; margin-top:15px; padding:15px; background:rgba(0,0,0,0.4); border-radius:8px; border:1px solid #444; text-align:left;">
                                <form method="POST">
                                    <input type="hidden" name="edit_id" value="<?= $p['id'] ?>">
                                    
                                    <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('item_name') ?> (ES)</label>
                                    <input type="text" name="edit_nombre" value="<?= htmlspecialchars($p['nombre']) ?>" required style="margin-bottom:8px; padding:6px; font-size:12px;">

                                    <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('item_name') ?> (EN)</label>
                                    <input type="text" name="edit_nombre_en" value="<?= htmlspecialchars($p['nombre_en'] ?? '') ?>" style="margin-bottom:8px; padding:6px; font-size:12px;">
                                    
                                    <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('item_price') ?></label>
                                    <input type="number" name="edit_precio" value="<?= $p['precio'] / COBRE_POR_ORO ?>" required min="1" step="0.01" style="margin-bottom:8px; padding:6px; font-size:12px;">
                                    
                                    <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('category') ?></label>
                                    <select name="edit_categoria" style="margin-bottom:8px; padding:6px; font-size:12px;">
                                        <?php foreach ($categorias_admin as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= $p['categoria_id'] == $c['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['nombre']) ?> / <?= htmlspecialchars($c['nombre_en'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                                        <input type="checkbox" name="edit_activo" id="edit_activo_<?= $p['id'] ?>" <?= $p['activo'] ? 'checked' : '' ?> style="width:auto; margin:0;">
                                        <label for="edit_activo_<?= $p['id'] ?>" style="font-size:11px; margin:0;"><?= t('active') ?></label>
                                    </div>

                                    <div style="display:flex; gap:8px;">
                                        <button type="submit" name="edit_shop_item" style="padding:6px 12px; font-size:11px; width:auto; margin:0;"><?= t('save') ?></button>
                                        <button type="button" onclick="ocultarEdicion(<?= $p['id'] ?>)" style="padding:6px 12px; font-size:11px; width:auto; margin:0; background:#444; border-color:#666; color:#ccc;"><?= t('cancel') ?></button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function mostrarEdicion(id) { document.getElementById('edit-form-' + id).style.display = 'block'; }
function ocultarEdicion(id) { document.getElementById('edit-form-' + id).style.display = 'none'; }
</script>