<?php
// Incluido desde admin/index.php

$mensaje = '';
$tipo = '';

// NUEVO: Verificar el Token CSRF en cualquier acción POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Acceso denegado: Token de seguridad inválido.");
    }
}

// ============================================
// CONFIGURACIÓN DE SUBIDA
// ============================================
$uploads_dir = __DIR__ . '/../uploads/tienda/';
$uploads_url = 'uploads/tienda/';

if (!is_dir($uploads_dir)) {
    @mkdir($uploads_dir, 0775, true);
}

/**
 * Sube una imagen, la redimensiona a 500x500 (centrada, sin deformar)
 * y la guarda como JPG calidad 85.
 * Devuelve [ok, nombre_archivo|null, error|null]
 */
function subirImagenTienda($file, $uploads_dir, $tamano = 500) {
    if (empty($file) || !isset($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE || $file['name'] === '') {
        return [true, null, null]; // sin imagen
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, null, 'Error al subir el archivo (código ' . $file['error'] . ')'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return [false, null, 'La imagen no puede superar 5 MB'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $permitidas, true)) {
        return [false, null, 'Formato no permitido (solo jpg, png, gif, webp)'];
    }

    // Validar que sea imagen real
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return [false, null, 'El archivo no es una imagen válida'];
    }

    // Comprobar GD
    if (!function_exists('imagecreatetruecolor')) {
        return [false, null, 'La extensión GD de PHP no está activada. Actívala en php.ini'];
    }

    // Cargar la imagen original según su tipo
    $origen = null;
    switch ($info[2]) {
        case IMAGETYPE_JPEG: $origen = @imagecreatefromjpeg($file['tmp_name']); break;
        case IMAGETYPE_PNG:  $origen = @imagecreatefrompng($file['tmp_name']);  break;
        case IMAGETYPE_GIF:  $origen = @imagecreatefromgif($file['tmp_name']);  break;
        case IMAGETYPE_WEBP: $origen = @imagecreatefromwebp($file['tmp_name']); break;
    }
    if (!$origen) {
        return [false, null, 'No se pudo procesar la imagen'];
    }

    $ancho_orig = imagesx($origen);
    $alto_orig  = imagesy($origen);

    // Crear lienzo cuadrado de 500x500 con fondo blanco
    $lienzo = imagecreatetruecolor($tamano, $tamano);
    $blanco = imagecolorallocate($lienzo, 255, 255, 255);
    imagefill($lienzo, 0, 0, $blanco);

    // Calcular escala para que quepa dentro de 500x500 (sin deformar)
    $escala = min($tamano / $ancho_orig, $tamano / $alto_orig);
    $nuevo_ancho = (int)round($ancho_orig * $escala);
    $nuevo_alto  = (int)round($alto_orig * $escala);

    // Centrar la imagen en el lienzo
    $x = (int)round(($tamano - $nuevo_ancho) / 2);
    $y = (int)round(($tamano - $nuevo_alto) / 2);

    imagecopyresampled($lienzo, $origen, $x, $y, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho_orig, $alto_orig);
    imagedestroy($origen);

    // Guardar siempre como JPG (calidad 85) para que pese poco
    if (!is_dir($uploads_dir)) {
        @mkdir($uploads_dir, 0775, true);
    }
    $nombre = 'item_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    $destino = $uploads_dir . $nombre;

    if (!imagejpeg($lienzo, $destino, 85)) {
        imagedestroy($lienzo);
        return [false, null, 'No se pudo guardar la imagen en el servidor'];
    }
    imagedestroy($lienzo);

    return [true, $nombre, null];
}

// ============================================
// CARGAR CATEGORÍAS
// ============================================
$categorias_admin = [];
try {
    $pdo_shop_cat = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt_cat = $pdo_shop_cat->query("SELECT id, nombre, nombre_en FROM web_shop_categories ORDER BY id ASC");
    $categorias_admin = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// ============================================
// AÑADIR ITEM
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_shop_item'])) {
    $item_entry   = (int)($_POST['item_entry'] ?? 0);
    $nombre       = trim($_POST['item_nombre'] ?? '');
    $nombre_en    = trim($_POST['item_nombre_en'] ?? '');
    $precio_oro   = (float)($_POST['item_precio'] ?? 0);
    $activo       = isset($_POST['item_activo']) ? 1 : 0;
    $categoria_id = (int)($_POST['item_categoria'] ?? 1);

    if ($item_entry <= 0 || empty($nombre) || $precio_oro <= 0) {
        $mensaje = t('invalid_data'); $tipo = 'error';
    } else {
        list($ok_img, $img_nombre, $img_error) = subirImagenTienda($_FILES['item_imagen'] ?? [], $uploads_dir);

        if (!$ok_img) {
            $mensaje = $img_error; $tipo = 'error';
        } else {
            try {
                $pdo_world = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . WORLD_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $stmt_w = $pdo_world->prepare("SELECT name FROM item_template WHERE entry = ?");
                $stmt_w->execute([$item_entry]);
                $item_world = $stmt_w->fetch(PDO::FETCH_ASSOC);

                if (!$item_world) {
                    if ($img_nombre && file_exists($uploads_dir . $img_nombre)) @unlink($uploads_dir . $img_nombre);
                    $mensaje = t('item_not_exist') . ' ' . $item_entry; $tipo = 'error';
                } else {
                    $precio_cobre = (int)round($precio_oro * COBRE_POR_ORO);
                    $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    $stmt = $pdo_shop->prepare("INSERT INTO web_shop_items (item_entry, nombre, nombre_en, precio, activo, categoria_id, imagen) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$item_entry, $nombre, $nombre_en, $precio_cobre, $activo, $categoria_id, $img_nombre]);
                    $mensaje = t('item_added') . ' (' . htmlspecialchars($item_world['name']) . ' — ' . $precio_oro . ' ' . t('gold') . ')'; $tipo = 'exito';
                }
            } catch (PDOException $e) {
                if ($img_nombre && file_exists($uploads_dir . $img_nombre)) @unlink($uploads_dir . $img_nombre);
                $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
            }
        }
    }
}

// ============================================
// EDITAR ITEM
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_shop_item'])) {
    $edit_id      = (int)($_POST['edit_id'] ?? 0);
    $nombre       = trim($_POST['edit_nombre'] ?? '');
    $nombre_en    = trim($_POST['edit_nombre_en'] ?? '');
    $precio_oro   = (float)($_POST['edit_precio'] ?? 0);
    $activo       = isset($_POST['edit_activo']) ? 1 : 0;
    $categoria_id = (int)($_POST['edit_categoria'] ?? 1);
    $quitar_img   = isset($_POST['edit_quitar_imagen']) ? 1 : 0;

    if ($edit_id <= 0 || empty($nombre) || $precio_oro <= 0) {
        $mensaje = t('invalid_data'); $tipo = 'error';
    } else {
        list($ok_img, $img_nombre, $img_error) = subirImagenTienda($_FILES['edit_imagen'] ?? [], $uploads_dir);

        if (!$ok_img) {
            $mensaje = $img_error; $tipo = 'error';
        } else {
            try {
                $precio_cobre = (int)round($precio_oro * COBRE_POR_ORO);
                $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                $stmt_cur = $pdo_shop->prepare("SELECT imagen FROM web_shop_items WHERE id = ?");
                $stmt_cur->execute([$edit_id]);
                $img_actual = $stmt_cur->fetchColumn();

                if ($img_nombre) {
                    if ($img_actual && file_exists($uploads_dir . $img_actual)) @unlink($uploads_dir . $img_actual);
                    $img_final = $img_nombre;
                } elseif ($quitar_img) {
                    if ($img_actual && file_exists($uploads_dir . $img_actual)) @unlink($uploads_dir . $img_actual);
                    $img_final = null;
                } else {
                    $img_final = $img_actual;
                }

                $stmt = $pdo_shop->prepare("UPDATE web_shop_items SET nombre = ?, nombre_en = ?, precio = ?, activo = ?, categoria_id = ?, imagen = ? WHERE id = ?");
                $stmt->execute([$nombre, $nombre_en, $precio_cobre, $activo, $categoria_id, $img_final, $edit_id]);
                $mensaje = t('saved_successfully'); $tipo = 'exito';
            } catch (PDOException $e) {
                if ($img_nombre && file_exists($uploads_dir . $img_nombre)) @unlink($uploads_dir . $img_nombre);
                $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
            }
        }
    }
}

// ============================================
// BORRAR ITEM
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_shop_item'])) {
    $del_id = (int)($_POST['delete_id'] ?? 0);
    if ($del_id > 0) {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $stmt_img = $pdo_shop->prepare("SELECT imagen FROM web_shop_items WHERE id = ?");
            $stmt_img->execute([$del_id]);
            $img = $stmt_img->fetchColumn();
            if ($img && file_exists($uploads_dir . $img)) @unlink($uploads_dir . $img);

            $stmt = $pdo_shop->prepare("DELETE FROM web_shop_items WHERE id = ?");
            $stmt->execute([$del_id]);
            $mensaje = t('item_deleted'); $tipo = 'exito';
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// ============================================
// ACTIVAR / DESACTIVAR
// ============================================
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

// ============================================
// CARGAR ITEMS
// ============================================
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

<!-- ============================================
     FORM AÑADIR ITEM
     ============================================ -->
<div class="admin-section">
    <h3><?= t('add_item') ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <!-- NUEVO: Token CSRF -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
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

        <label for="item_imagen">Imagen del producto (opcional)</label>
        <input type="file" id="item_imagen" name="item_imagen" accept="image/png,image/jpeg,image/gif,image/webp">
        <small style="color:#9db4d8; font-size:11px; display:block; margin-bottom:15px;">
            Cualquier tamaño · se redimensiona automáticamente a 500×500 JPG · máximo 5 MB
        </small>

        <div class="checkbox-row">
            <input type="checkbox" id="item_activo" name="item_activo" checked>
            <label for="item_activo"><?= t('item_active') ?></label>
        </div>
        <button type="submit" name="add_shop_item"><?= t('add_btn') ?></button>
    </form>
</div>

<!-- ============================================
     LISTA DE ITEMS
     ============================================ -->
<div class="admin-section">
    <h3><?= t('manage_items') ?></h3>
    <?php if (empty($productos_admin)): ?>
        <p style="color:#9db4d8; text-align:center; font-size:13px;"><?= t('no_items_shop') ?></p>
    <?php else: ?>
        <table class="admin-tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
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
                        <td>
                            <?php if (!empty($p['imagen']) && file_exists($uploads_dir . $p['imagen'])): ?>
                                <img src="../<?= htmlspecialchars($uploads_url . $p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" style="width:48px; height:48px; object-fit:cover; border-radius:6px; border:1px solid rgba(212,175,55,0.4);">
                            <?php else: ?>
                                <div style="width:48px; height:48px; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.3); border-radius:6px; border:1px solid rgba(212,175,55,0.2); color:#7a8590; font-size:18px;">🛒</div>
                            <?php endif; ?>
                        </td>
                        <td><?= $p['item_entry'] ?></td>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['nombre_en'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['categoria_nombre'] ?? '-') ?></td>
                        <td><?= number_format($p['precio'] / COBRE_POR_ORO) ?> <?= t('gold') ?></td>
                        <td>
                            <form method="POST" style="margin:0; display:inline;">
                                <!-- NUEVO: Token CSRF -->
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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
                                    <!-- NUEVO: Token CSRF -->
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                                    <button type="submit" name="delete_shop_item" class="btn-borrar"><?= t('delete') ?></button>
                                </form>
                            </div>

                            <!-- Formulario de edición oculto -->
                            <div id="edit-form-<?= $p['id'] ?>" style="display:none; margin-top:15px; padding:15px; background:rgba(0,0,0,0.4); border-radius:8px; border:1px solid #444; text-align:left;">
                                <form method="POST" enctype="multipart/form-data">
                                    <!-- NUEVO: Token CSRF -->
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
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

                                    <?php if (!empty($p['imagen']) && file_exists($uploads_dir . $p['imagen'])): ?>
                                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                            <img src="../<?= htmlspecialchars($uploads_url . $p['imagen']) ?>" style="width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid rgba(212,175,55,0.4);">
                                            <label style="font-size:11px; margin:0; display:flex; align-items:center; gap:5px;">
                                                <input type="checkbox" name="edit_quitar_imagen" value="1" style="width:auto; margin:0;">
                                                Quitar imagen
                                            </label>
                                        </div>
                                    <?php endif; ?>

                                    <label style="font-size:11px; margin-bottom:3px; display:block;">Cambiar imagen (opcional)</label>
                                    <input type="file" name="edit_imagen" accept="image/png,image/jpeg,image/gif,image/webp" style="margin-bottom:8px; padding:4px; font-size:12px;">
                                    <small style="color:#9db4d8; font-size:10px; display:block; margin-bottom:8px;">
                                        Si no subes nada, se conserva la imagen actual. Se redimensiona a 500×500 JPG.
                                    </small>

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
