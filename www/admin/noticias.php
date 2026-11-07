<?php
// Incluido desde admin/index.php

$mensaje = '';
$tipo = '';

$noticias_es_file = __DIR__ . '/../noticias_es.json';
$noticias_en_file = __DIR__ . '/../noticias_en.json';

$uploads_dir = __DIR__ . '/../uploads/tienda/';
$uploads_url = 'uploads/tienda/';

if (!is_dir($uploads_dir)) {
    @mkdir($uploads_dir, 0775, true);
}

function subirImagenNoticia($file, $uploads_dir) {
    if (empty($file) || !isset($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE || $file['name'] === '') {
        return [true, null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, null, 'Error al subir la imagen (código ' . $file['error'] . ')'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return [false, null, 'La imagen no puede superar 5 MB'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $permitidas, true)) {
        return [false, null, 'Formato no permitido (solo jpg, png, gif, webp)'];
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return [false, null, 'El archivo no es una imagen válida'];
    }

    if (!function_exists('imagecreatetruecolor')) {
        return [false, null, 'La extensión GD de PHP no está activada.'];
    }

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

    $max_ancho = 1200;
    $max_alto = 1200;

    if ($ancho_orig > $max_ancho || $alto_orig > $max_alto) {
        $escala = min($max_ancho / $ancho_orig, $max_alto / $alto_orig);
        $nuevo_ancho = (int)round($ancho_orig * $escala);
        $nuevo_alto  = (int)round($alto_orig * $escala);

        $lienzo = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
        $blanco = imagecolorallocate($lienzo, 255, 255, 255);
        imagefill($lienzo, 0, 0, $blanco);

        imagecopyresampled($lienzo, $origen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho_orig, $alto_orig);
        
        imagedestroy($origen);
        $origen = $lienzo;
    }

    $nombre = 'noticia_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    $destino = $uploads_dir . $nombre;

    if (!imagejpeg($origen, $destino, 85)) {
        imagedestroy($origen);
        return [false, null, 'No se pudo guardar la imagen en el servidor'];
    }
    imagedestroy($origen);

    return [true, $nombre, null];
}

$noticias = [];
if (file_exists($noticias_es_file)) {
    $json = file_get_contents($noticias_es_file);
    $datos = json_decode($json, true);
    if (is_array($datos)) $noticias = $datos;
}
$noticias_en = [];
if (file_exists($noticias_en_file)) {
    $json = file_get_contents($noticias_en_file);
    $datos = json_decode($json, true);
    if (is_array($datos)) $noticias_en = $datos;
}

// CREAR NOTICIA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_noticia'])) {
    $titulo_es    = trim($_POST['titulo_es'] ?? '');
    $titulo_en    = trim($_POST['titulo_en'] ?? '');
    $fecha        = trim($_POST['fecha'] ?? date('Y-m-d'));
    $autor        = trim($_POST['autor'] ?? $web_user);
    $contenido_es = trim($_POST['contenido_es'] ?? '');
    $contenido_en = trim($_POST['contenido_en'] ?? '');
    
    if (empty($titulo_es) || empty($contenido_es) || empty($titulo_en) || empty($contenido_en)) {
        $mensaje = t('news_required'); $tipo = 'error';
    } else {
        list($ok_img, $img_nombre, $img_error) = subirImagenNoticia($_FILES['noticia_imagen'] ?? [], $uploads_dir);

        if (!$ok_img) {
            $mensaje = $img_error; $tipo = 'error';
        } else {
            $nuevo_id = time();

            $noticias[] = ['id' => $nuevo_id, 'titulo' => $titulo_es, 'fecha' => $fecha, 'autor' => $autor, 'contenido' => $contenido_es, 'imagen' => $img_nombre];
            file_put_contents($noticias_es_file, json_encode($noticias, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $noticias_en[] = ['id' => $nuevo_id, 'titulo' => $titulo_en, 'fecha' => $fecha, 'autor' => $autor, 'contenido' => $contenido_en, 'imagen' => $img_nombre];
            file_put_contents($noticias_en_file, json_encode($noticias_en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            header('Location: index.php?tab=noticias&msg=created');
            exit;
        }
    }
}

// EDITAR NOTICIA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_noticia'])) {
    $noticia_id   = (int)($_POST['noticia_id'] ?? 0);
    $titulo_es    = trim($_POST['edit_titulo_es'] ?? '');
    $titulo_en    = trim($_POST['edit_titulo_en'] ?? '');
    $fecha        = trim($_POST['edit_fecha'] ?? date('Y-m-d'));
    $autor        = trim($_POST['edit_autor'] ?? $web_user);
    $contenido_es = trim($_POST['edit_contenido_es'] ?? '');
    $contenido_en = trim($_POST['edit_contenido_en'] ?? '');
    $quitar_img   = isset($_POST['edit_quitar_imagen']) ? 1 : 0;
    
    if ($noticia_id <= 0 || empty($titulo_es) || empty($contenido_es)) {
        $mensaje = t('news_required'); $tipo = 'error';
    } else {
        list($ok_img, $img_nombre, $img_error) = subirImagenNoticia($_FILES['edit_noticia_imagen'] ?? [], $uploads_dir);

        if (!$ok_img) {
            $mensaje = $img_error; $tipo = 'error';
        } else {
            foreach ($noticias as &$n) {
                if ((int)$n['id'] === $noticia_id) {
                    $n['titulo']    = $titulo_es;
                    $n['fecha']     = $fecha;
                    $n['autor']     = $autor;
                    $n['contenido'] = $contenido_es;
                    
                    if ($img_nombre) {
                        if (!empty($n['imagen']) && file_exists($uploads_dir . $n['imagen'])) @unlink($uploads_dir . $n['imagen']);
                        $n['imagen'] = $img_nombre;
                    } elseif ($quitar_img) {
                        if (!empty($n['imagen']) && file_exists($uploads_dir . $n['imagen'])) @unlink($uploads_dir . $n['imagen']);
                        $n['imagen'] = null;
                    }
                    break;
                }
            }
            unset($n);
            file_put_contents($noticias_es_file, json_encode($noticias, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            foreach ($noticias_en as &$n) {
                if ((int)$n['id'] === $noticia_id) {
                    $n['titulo']    = $titulo_en;
                    $n['fecha']     = $fecha;
                    $n['autor']     = $autor;
                    $n['contenido'] = $contenido_en;
                    $idx = array_search($noticia_id, array_column($noticias, 'id'));
                    $n['imagen'] = ($idx !== false) ? ($noticias[$idx]['imagen'] ?? null) : null;
                    break;
                }
            }
            unset($n);
            file_put_contents($noticias_en_file, json_encode($noticias_en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            header('Location: index.php?tab=noticias&msg=updated');
            exit;
        }
    }
}

// BORRAR NOTICIA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_noticia'])) {
    $noticia_id = (int)($_POST['borrar_id'] ?? 0);
    if ($noticia_id > 0) {
        $noticias = array_filter($noticias, function($n) use ($noticia_id, $uploads_dir) {
            if ((int)$n['id'] === $noticia_id) {
                if (!empty($n['imagen']) && file_exists($uploads_dir . $n['imagen'])) @unlink($uploads_dir . $n['imagen']);
                return false;
            }
            return true;
        });
        file_put_contents($noticias_es_file, json_encode(array_values($noticias), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $noticias_en = array_filter($noticias_en, function($n) use ($noticia_id) {
            return (int)$n['id'] !== $noticia_id;
        });
        file_put_contents($noticias_en_file, json_encode(array_values($noticias_en), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header('Location: index.php?tab=noticias&msg=deleted');
        exit;
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') { $mensaje = t('news_published'); $tipo = 'exito'; }
    elseif ($_GET['msg'] === 'updated') { $mensaje = t('saved_successfully'); $tipo = 'exito'; }
    elseif ($_GET['msg'] === 'deleted') { $mensaje = t('deleted_successfully'); $tipo = 'exito'; }
}

usort($noticias, function($a, $b) {
    return strtotime($b['fecha'] ?? '1970-01-01') - strtotime($a['fecha'] ?? '1970-01-01');
});
?>

<?php if ($mensaje): ?>
    <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="admin-section">
    <h3><?= t('create_news') ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <label for="titulo_es"><?= t('title') ?> (ES)</label>
        <input type="text" id="titulo_es" name="titulo_es" required>

        <label for="titulo_en"><?= t('title') ?> (EN)</label>
        <input type="text" id="titulo_en" name="titulo_en" required>

        <label for="fecha"><?= t('date') ?></label>
        <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>">
        
        <label for="autor"><?= t('author') ?></label>
        <input type="text" id="autor" name="autor" value="<?= htmlspecialchars($web_user) ?>">

        <label for="noticia_imagen">Imagen de portada (Opcional)</label>
        <input type="file" id="noticia_imagen" name="noticia_imagen" accept="image/png,image/jpeg,image/gif,image/webp">
        <small style="color:#9db4d8; font-size:11px; display:block; margin-bottom:15px;">
            Cualquier tamaño · máximo 5 MB · Se guarda en la misma carpeta que la tienda.
        </small>
        
        <label for="contenido_es"><?= t('content') ?> (ES)</label>
        <textarea id="contenido_es" name="contenido_es" required></textarea>

        <label for="contenido_en"><?= t('content') ?> (EN)</label>
        <textarea id="contenido_en" name="contenido_en" required></textarea>
        
        <button type="submit" name="crear_noticia"><?= t('publish') ?></button>
    </form>
</div>

<div class="admin-section">
    <h3>📰 <?= t('news_titulo') ?> (<?= count($noticias) ?>)</h3>
    
    <?php if (empty($noticias)): ?>
        <p style="color:#9db4d8; text-align:center; font-size:13px;"><?= t('no_news') ?></p>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:15px;">
            <?php foreach ($noticias as $n): ?>
                <?php
                    $n_en = null;
                    foreach ($noticias_en as $n2) {
                        if ((int)$n2['id'] === (int)$n['id']) { $n_en = $n2; break; }
                    }
                ?>
                <div style="background: rgba(0,10,30,0.6); border: 1px solid rgba(212,175,55,0.3); border-radius: 10px; padding: 15px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; gap:15px; align-items:center; flex:1; min-width:200px;">
                            
                            <?php if (!empty($n['imagen']) && file_exists($uploads_dir . $n['imagen'])): ?>
                                <img src="../<?= $uploads_url . htmlspecialchars($n['imagen']) ?>" style="width: 100px; height: 80px; object-fit: contain; background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px solid #f0c94a;">
                            <?php else: ?>
                                <div style="width: 100px; height: 80px; background: rgba(0,0,0,0.3); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px dashed #555;">📰</div>
                            <?php endif; ?>

                            <div>
                                <h4 style="color:#f0c94a; font-size:15px; margin-bottom:5px;"><?= htmlspecialchars($n['titulo'] ?? '') ?> <small style="color:#7a8590;">(ES)</small></h4>
                                <h4 style="color:#9db4d8; font-size:13px; margin-bottom:5px;"><?= htmlspecialchars($n_en['titulo'] ?? '') ?> <small style="color:#7a8590;">(EN)</small></h4>
                                <div style="font-size:11px; color:#9db4d8;">
                                    📅 <?= htmlspecialchars($n['fecha'] ?? '') ?> · 👤 <?= htmlspecialchars($n['autor'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex; gap:5px; flex-shrink:0;">
                            <button type="button" class="btn-borrar" style="background:rgba(30,91,198,0.3); border-color:#1e5bc6; color:#9dd8ff;" onclick="mostrarEditNoticia(<?= $n['id'] ?>)"><?= t('edit') ?></button>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('<?= t('are_you_sure') ?>');">
                                <input type="hidden" name="borrar_id" value="<?= $n['id'] ?>">
                                <button type="submit" name="borrar_noticia" class="btn-borrar"><?= t('delete') ?></button>
                            </form>
                        </div>
                    </div>
                    
                    <div id="edit-noticia-<?= $n['id'] ?>" style="display:none; margin-top:15px; padding:15px; background:rgba(0,0,0,0.4); border-radius:8px; border:1px solid #444;">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="noticia_id" value="<?= $n['id'] ?>">
                            
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('title') ?> (ES)</label>
                            <input type="text" name="edit_titulo_es" value="<?= htmlspecialchars($n['titulo'] ?? '') ?>" required style="margin-bottom:8px; padding:8px; font-size:13px;">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('title') ?> (EN)</label>
                            <input type="text" name="edit_titulo_en" value="<?= htmlspecialchars($n_en['titulo'] ?? '') ?>" required style="margin-bottom:8px; padding:8px; font-size:13px;">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('date') ?></label>
                            <input type="date" name="edit_fecha" value="<?= htmlspecialchars($n['fecha'] ?? date('Y-m-d')) ?>" style="margin-bottom:8px; padding:8px; font-size:13px;">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('author') ?></label>
                            <input type="text" name="edit_autor" value="<?= htmlspecialchars($n['autor'] ?? '') ?>" style="margin-bottom:8px; padding:8px; font-size:13px;">

                            <?php if (!empty($n['imagen']) && file_exists($uploads_dir . $n['imagen'])): ?>
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                    <img src="../<?= $uploads_url . htmlspecialchars($n['imagen']) ?>" style="width: 100px; height: 80px; object-fit: contain; background: rgba(0,0,0,0.2); border-radius: 6px;">
                                    <label style="font-size:11px; margin:0; display:flex; align-items:center; gap:5px;">
                                        <input type="checkbox" name="edit_quitar_imagen" value="1" style="width:auto; margin:0;">
                                        Quitar imagen
                                    </label>
                                </div>
                            <?php endif; ?>

                            <label style="font-size:11px; margin-bottom:3px; display:block;">Cambiar imagen (Opcional)</label>
                            <input type="file" name="edit_noticia_imagen" accept="image/png,image/jpeg,image/gif,image/webp" style="margin-bottom:8px; padding:4px; font-size:12px;">

                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('content') ?> (ES)</label>
                            <textarea name="edit_contenido_es" required style="margin-bottom:8px; padding:8px; font-size:13px; min-height:80px;"><?= htmlspecialchars($n['contenido'] ?? '') ?></textarea>
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('content') ?> (EN)</label>
                            <textarea name="edit_contenido_en" required style="margin-bottom:8px; padding:8px; font-size:13px; min-height:80px;"><?= htmlspecialchars($n_en['contenido'] ?? '') ?></textarea>
                            <div style="display:flex; gap:8px;">
                                <button type="submit" name="editar_noticia" style="padding:6px 12px; font-size:11px; width:auto; margin:0;"><?= t('save') ?></button>
                                <button type="button" onclick="ocultarEditNoticia(<?= $n['id'] ?>)" style="padding:6px 12px; font-size:11px; width:auto; margin:0; background:#444; border-color:#666; color:#ccc;"><?= t('cancel') ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function mostrarEditNoticia(id) { document.getElementById('edit-noticia-' + id).style.display = 'block'; }
function ocultarEditNoticia(id) { document.getElementById('edit-noticia-' + id).style.display = 'none'; }
</script>
