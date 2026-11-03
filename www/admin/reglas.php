<?php
// Incluido desde admin/index.php

$reglas_es_file = __DIR__ . '/../reglas_es.json';
$reglas_en_file = __DIR__ . '/../reglas_en.json';

$mensaje = '';
$tipo = '';

// Cargar reglas ES
$reglas = [];
if (file_exists($reglas_es_file)) {
    $json = file_get_contents($reglas_es_file);
    $datos = json_decode($json, true);
    if (is_array($datos)) $reglas = $datos;
}

// Cargar reglas EN
$reglas_en = [];
if (file_exists($reglas_en_file)) {
    $json = file_get_contents($reglas_en_file);
    $datos = json_decode($json, true);
    if (is_array($datos)) $reglas_en = $datos;
}

// AÑADIR REGLA (en ambos idiomas)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_regla'])) {
    $titulo_es = trim($_POST['regla_titulo_es'] ?? '');
    $titulo_en = trim($_POST['regla_titulo_en'] ?? '');
    $contenido_es = trim($_POST['regla_contenido_es'] ?? '');
    $contenido_en = trim($_POST['regla_contenido_en'] ?? '');

    if (empty($titulo_es) || empty($contenido_es) || empty($titulo_en) || empty($contenido_en)) {
        $mensaje = t('required_fields'); $tipo = 'error';
    } else {
        $nuevo_id = 1;
        foreach ($reglas as $r) {
            if ((int)$r['id'] >= $nuevo_id) $nuevo_id = (int)$r['id'] + 1;
        }
        
        $reglas[] = ['id' => $nuevo_id, 'titulo' => $titulo_es, 'contenido' => $contenido_es];
        file_put_contents($reglas_es_file, json_encode($reglas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $reglas_en[] = ['id' => $nuevo_id, 'titulo' => $titulo_en, 'contenido' => $contenido_en];
        file_put_contents($reglas_en_file, json_encode($reglas_en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header('Location: index.php?tab=reglas&msg=created');
        exit;
    }
}

// EDITAR REGLA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_regla'])) {
    $regla_id = (int)($_POST['regla_id'] ?? 0);
    $titulo_es = trim($_POST['edit_titulo_es'] ?? '');
    $titulo_en = trim($_POST['edit_titulo_en'] ?? '');
    $contenido_es = trim($_POST['edit_contenido_es'] ?? '');
    $contenido_en = trim($_POST['edit_contenido_en'] ?? '');

    if ($regla_id <= 0 || empty($titulo_es) || empty($contenido_es)) {
        $mensaje = t('invalid_data'); $tipo = 'error';
    } else {
        foreach ($reglas as &$r) {
            if ((int)$r['id'] === $regla_id) {
                $r['titulo'] = $titulo_es;
                $r['contenido'] = $contenido_es;
                break;
            }
        }
        unset($r);
        file_put_contents($reglas_es_file, json_encode($reglas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        foreach ($reglas_en as &$r) {
            if ((int)$r['id'] === $regla_id) {
                $r['titulo'] = $titulo_en;
                $r['contenido'] = $contenido_en;
                break;
            }
        }
        unset($r);
        file_put_contents($reglas_en_file, json_encode($reglas_en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header('Location: index.php?tab=reglas&msg=updated');
        exit;
    }
}

// BORRAR REGLA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_regla'])) {
    $regla_id = (int)($_POST['delete_id'] ?? 0);
    if ($regla_id > 0) {
        $reglas = array_filter($reglas, function($r) use ($regla_id) {
            return (int)$r['id'] !== $regla_id;
        });
        file_put_contents($reglas_es_file, json_encode(array_values($reglas), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $reglas_en = array_filter($reglas_en, function($r) use ($regla_id) {
            return (int)$r['id'] !== $regla_id;
        });
        file_put_contents($reglas_en_file, json_encode(array_values($reglas_en), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header('Location: index.php?tab=reglas&msg=deleted');
        exit;
    }
}

// Mensajes
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'created') { $mensaje = t('rule_added'); $tipo = 'exito'; }
    elseif ($_GET['msg'] === 'updated') { $mensaje = t('rule_updated'); $tipo = 'exito'; }
    elseif ($_GET['msg'] === 'deleted') { $mensaje = t('rule_deleted'); $tipo = 'exito'; }
}

usort($reglas, function($a, $b) { return (int)$a['id'] - (int)$b['id']; });
?>

<?php if ($mensaje): ?>
    <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="admin-section">
    <h3>📖 <?= t('add_rule') ?></h3>
    <form method="POST">
        <label for="regla_titulo_es"><?= t('rule_title') ?> (ES)</label>
        <input type="text" id="regla_titulo_es" name="regla_titulo_es" required>

        <label for="regla_titulo_en"><?= t('rule_title') ?> (EN)</label>
        <input type="text" id="regla_titulo_en" name="regla_titulo_en" required>
        
        <label for="regla_contenido_es"><?= t('rule_content') ?> (ES)</label>
        <textarea id="regla_contenido_es" name="regla_contenido_es" required></textarea>

        <label for="regla_contenido_en"><?= t('rule_content') ?> (EN)</label>
        <textarea id="regla_contenido_en" name="regla_contenido_en" required></textarea>
        
        <button type="submit" name="add_regla"><?= t('add_rule') ?></button>
    </form>
</div>

<div class="admin-section">
    <h3>📋 <?= t('rules_title') ?> (<?= count($reglas) ?>)</h3>
    
    <?php if (empty($reglas)): ?>
        <p style="color:#9db4d8; text-align:center; font-size:13px;"><?= t('rules_empty') ?></p>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:15px;">
            <?php foreach ($reglas as $r): ?>
                <?php
                    $r_en = null;
                    foreach ($reglas_en as $r2) {
                        if ((int)$r2['id'] === (int)$r['id']) { $r_en = $r2; break; }
                    }
                ?>
                <div style="background: rgba(0,10,30,0.6); border: 1px solid rgba(212,175,55,0.3); border-radius: 10px; padding: 15px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
                        <div style="flex:1; min-width:200px;">
                            <h4 style="color:#f0c94a; font-size:15px; margin-bottom:5px;"><?= htmlspecialchars($r['titulo']) ?> <small style="color:#7a8590;">(ES)</small></h4>
                            <h4 style="color:#9db4d8; font-size:13px; margin-bottom:5px;"><?= htmlspecialchars($r_en['titulo'] ?? '') ?> <small style="color:#7a8590;">(EN)</small></h4>
                        </div>
                        <div style="display:flex; gap:5px; flex-shrink:0;">
                            <button type="button" class="btn-borrar" style="background:rgba(30,91,198,0.3); border-color:#1e5bc6; color:#9dd8ff;" onclick="mostrarEditRegla(<?= $r['id'] ?>)"><?= t('edit') ?></button>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('<?= t('are_you_sure') ?>');">
                                <input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
                                <button type="submit" name="delete_regla" class="btn-borrar"><?= t('delete') ?></button>
                            </form>
                        </div>
                    </div>
                    
                    <div id="edit-regla-<?= $r['id'] ?>" style="display:none; margin-top:15px; padding:15px; background:rgba(0,0,0,0.4); border-radius:8px; border:1px solid #444;">
                        <form method="POST">
                            <input type="hidden" name="regla_id" value="<?= $r['id'] ?>">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('rule_title') ?> (ES)</label>
                            <input type="text" name="edit_titulo_es" value="<?= htmlspecialchars($r['titulo']) ?>" required style="margin-bottom:8px; padding:8px; font-size:13px;">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('rule_title') ?> (EN)</label>
                            <input type="text" name="edit_titulo_en" value="<?= htmlspecialchars($r_en['titulo'] ?? '') ?>" required style="margin-bottom:8px; padding:8px; font-size:13px;">
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('rule_content') ?> (ES)</label>
                            <textarea name="edit_contenido_es" required style="margin-bottom:8px; padding:8px; font-size:13px; min-height:80px;"><?= htmlspecialchars($r['contenido']) ?></textarea>
                            <label style="font-size:11px; margin-bottom:3px; display:block;"><?= t('rule_content') ?> (EN)</label>
                            <textarea name="edit_contenido_en" required style="margin-bottom:8px; padding:8px; font-size:13px; min-height:80px;"><?= htmlspecialchars($r_en['contenido'] ?? '') ?></textarea>
                            <div style="display:flex; gap:8px;">
                                <button type="submit" name="edit_regla" style="padding:6px 12px; font-size:11px; width:auto; margin:0;"><?= t('save') ?></button>
                                <button type="button" onclick="ocultarEditRegla(<?= $r['id'] ?>)" style="padding:6px 12px; font-size:11px; width:auto; margin:0; background:#444; border-color:#666; color:#ccc;"><?= t('cancel') ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function mostrarEditRegla(id) { document.getElementById('edit-regla-' + id).style.display = 'block'; }
function ocultarEditRegla(id) { document.getElementById('edit-regla-' + id).style.display = 'none'; }
</script>