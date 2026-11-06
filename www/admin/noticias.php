<?php
// Incluido desde admin/index.php

$mensaje = '';
$tipo = '';

// ============================================
// NUEVO: VERIFICAR TOKEN CSRF
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Acceso denegado: Token de seguridad inválido.");
    }
}

$noticias_es_file = __DIR__ . '/../noticias_es.json';
$noticias_en_file = __DIR__ . '/../noticias_en.json';

// Cargar noticias (usamos el archivo ES como referencia)
$noticias = [];
if (file_exists($noticias_es_file)) {
    $json = file_get_contents($noticias_es_file);
    $datos = json_decode($json, true);
    if (is_array($datos)) $noticias = $datos;
}

// Cargar noticias EN (para emparejar por id)
$noticias_en = [];
if (file_exists($noticias_en_file)) {
    $json = file_get_contents($noticias_en_file);
    $datos = json_decode($json, true);
    if (is_array($datos)) $noticias_en = $datos;
}

// CREAR NOTICIA (en ambos idiomas)
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
        $nuevo_id = time();

        // Guardar ES
        $noticias[] = ['id' => $nuevo_id, 'titulo' => $titulo_es, 'fecha' => $fecha, 'autor' => $autor, 'contenido' => $contenido_es];
        file_put_contents($noticias_es_file, json_encode($noticias, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Guardar EN
        $noticias_en[] = ['id' => $nuevo_id, 'titulo' => $titulo_en, 'fecha' => $fecha, 'autor' => $autor, 'contenido' => $contenido_en];
        file_put_contents($noticias_en_file, json_encode($noticias_en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header('Location: index.php?tab=noticias&msg=created');
        exit;
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
    
    if ($noticia_id <= 0 || empty($titulo_es) || empty($contenido_es)) {
        $mensaje = t('news_required'); $tipo = 'error';
    } else {
        // Editar ES
        foreach ($noticias as &$n) {
            if ((int)$n['id'] === $noticia_id) {
                $n['titulo']    = $titulo_es;
                $n['fecha']     = $fecha;
                $n['autor']     = $autor;
                $n['contenido'] = $contenido_es;
                break;
            }
        }
        unset($n);
        file_put_contents($noticias_es_file, json_encode($noticias, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Editar EN
        foreach ($noticias_en as &$n) {
            if ((int)$n['id'] === $noticia_id) {
                $n['titulo']    = $titulo_en;
                $n['fecha']     = $fecha;
                $n['autor']     = $autor;
                $n['contenido'] = $contenido_en;
                break;
            }
        }
        unset($n);
        file_put_contents($noticias_en_file, json_encode($noticias_en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header('Location: index.php?tab=noticias&msg=updated');
        exit;
    }
}

// BORRAR NOTICIA (en ambos idiomas)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_noticia'])) {
    $noticia_id = (int)($_POST['borrar_id'] ?? 0);
    if ($noticia_id > 0) {
        $noticias = array_filter($noticias, function($n) use ($noticia_id) {
            return (int)$n['id'] !== $noticia_id;
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

// Mensajes por redirección
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
    <form method="POST">
        <!-- NUEVO: Token CSRF -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label for="titulo_es"><?= t('title') ?> (ES)</label>
        <input type="text" id="titulo_es" name="titulo_es" required>

        <label for="titulo_en"><?= t('title') ?> (EN)</label>
        <input type="text" id="titulo_en" name="titulo_en" required>

        <label for="fecha"><?= t('date') ?></label>
        <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>">
        
        <label for="autor"><?= t('author') ?></label>
        <input type="text" id="autor" name="autor" value="<?= htmlspecialchars($web_user) ?>">
        
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
                    // Buscar la traducción EN
                    $n_en = null;
                    foreach ($noticias_en as $n2) {
                        if ((int)$n2['id'] === (int)$n['id']) { $n_en = $n2; break; }
                    }
                ?>
                <div style="background: rgba(0,10,30,0.6); border: 1px solid rgba(212,175,55,0.3); border-radius: 10px; padding: 15px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
                        <div style="flex:1; min-width:200px;">
                            <h4 style="color:#f0c94a; font-size:15px; margin-bottom:5px;"><?= htmlspecialchars($n['titulo'] ?? '') ?> <small style="color:#7a8590;">(ES)</small></h4>
                            <h4 style="color:#9db4d8; font-size:13px; margin-bottom:5px;"><?= htmlspecialchars($n_en['titulo'] ?? '') ?> <small style="color:#7a8590;">(EN)</small></h4>
                            <div style="font-size:11px; color:#9db4d8; margin-bottom:8px;">
                                📅 <?= htmlspecialchars($n['fecha'] ?? '') ?> · 👤 <?= htmlspecialchars($n['autor'] ?? '') ?>
                            </div>
                        </div>
                        <div style="display:flex; gap:5px; flex-shrink:0;">
                            <button type="button" class="btn-borrar" style="background:rgba(30,91,198,0.3); border-color:#1e5bc6; color:#9dd8ff;" onclick="mostrarEditNoticia(<?= $n['id'] ?>)"><?= t('edit') ?></button>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('<?= t('are_you_sure') ?>');">
                                <!-- NUEVO: Token CSRF -->
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="borrar_id" value="<?= $n['id'] ?>">
                                <button type="submit" name="borrar_noticia" class="btn-borrar"><?= t('delete') ?></button>
                            </form>
                        </div>
                    </div>
                    
                    <div id="edit-noticia-<?= $n['id'] ?>" style="display:none; margin-top:15px; padding:15px; background:rgba(0,0,0,0.4); border-radius:8px; border:1px solid #444;">
                        <form method="POST">
                            <!-- NUEVO: Token CSRF -->
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <input type="hidden" name="noticia_id" value="<?= $n['id'] ?>">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('title') ?> (ES)</label>
                            <input type="text" name="edit_titulo_es" value="<?= htmlspecialchars($n['titulo'] ?? '') ?>" required style="margin-bottom:8px; padding:8px; font-size:13px;">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('title') ?> (EN)</label>
                            <input type="text" name="edit_titulo_en" value="<?= htmlspecialchars($n_en['titulo'] ?? '') ?>" required style="margin-bottom:8px; padding:8px; font-size:13px;">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('date') ?></label>
                            <input type="date" name="edit_fecha" value="<?= htmlspecialchars($n['fecha'] ?? date('Y-m-d')) ?>" style="margin-bottom:8px; padding:8px; font-size:13px;">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('author') ?></label>
                            <input type="text" name="edit_autor" value="<?= htmlspecialchars($n['autor'] ?? '') ?>" style="margin-bottom:8px; padding:8px; font-size:13px;">
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
