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

// CREAR CÓDIGO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_codigo'])) {
    $codigo    = strtoupper(trim($_POST['codigo'] ?? ''));
    $tipo_r    = $_POST['tipo_recompensa'] ?? 'oro';
    $valor     = (int)($_POST['valor'] ?? 0);
    $usos_max  = max(1, (int)($_POST['usos_maximos'] ?? 1));
    $expiracion = !empty($_POST['fecha_expiracion']) ? $_POST['fecha_expiracion'] : null;

    if (empty($codigo) || $valor <= 0) {
        $mensaje = t('required_fields'); $tipo = 'error';
    } else {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo_shop->prepare("INSERT INTO web_gift_codes (codigo, tipo, valor, usos_maximos, fecha_expiracion) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$codigo, $tipo_r, $valor, $usos_max, $expiracion]);
            $mensaje = t('code_created'); $tipo = 'exito';
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $mensaje = t('code_exists'); $tipo = 'error';
            } else {
                $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
            }
        }
    }
}

// BORRAR CÓDIGO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_codigo'])) {
    $del_id = (int)($_POST['delete_id'] ?? 0);
    if ($del_id > 0) {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo_shop->prepare("DELETE FROM web_gift_codes WHERE id = ?");
            $stmt->execute([$del_id]);
            $mensaje = t('code_deleted'); $tipo = 'exito';
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// ACTIVAR / DESACTIVAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_codigo'])) {
    $tog_id = (int)($_POST['toggle_id'] ?? 0);
    if ($tog_id > 0) {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo_shop->prepare("UPDATE web_gift_codes SET activo = 1 - activo WHERE id = ?");
            $stmt->execute([$tog_id]);
        } catch (PDOException $e) {}
    }
}

// CARGAR CÓDIGOS
$codigos = [];
try {
    $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo_shop->query("SELECT * FROM web_gift_codes ORDER BY id DESC");
    $codigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<?php if ($mensaje): ?>
    <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="admin-section">
    <h3>🎁 <?= t('create_code') ?></h3>
    <form method="POST">
        <!-- NUEVO: Token CSRF -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label for="codigo"><?= t('code') ?></label>
        <input type="text" id="codigo" name="codigo" placeholder="<?= t('code_ph') ?>" required style="text-transform: uppercase;">

        <label for="tipo_recompensa"><?= t('code_type') ?></label>
        <select id="tipo_recompensa" name="tipo_recompensa" required>
            <option value="oro"><?= t('gold') ?></option>
            <option value="item">Item</option>
        </select>

        <label for="valor"><?= t('code_value') ?></label>
        <input type="number" id="valor" name="valor" required min="1">

        <label for="usos_maximos"><?= t('code_max_uses_label') ?></label>
        <input type="number" id="usos_maximos" name="usos_maximos" value="1" min="1" required>

        <label for="fecha_expiracion"><?= t('code_expiration') ?></label>
        <input type="date" id="fecha_expiracion" name="fecha_expiracion">

        <button type="submit" name="crear_codigo"><?= t('create_code') ?></button>
    </form>
</div>

<div class="admin-section">
    <h3>📋 <?= t('gift_codes') ?> (<?= count($codigos) ?>)</h3>
    <?php if (empty($codigos)): ?>
        <p style="color:#9db4d8; text-align:center; font-size:13px;"><?= t('no_data') ?></p>
    <?php else: ?>
        <table class="admin-tabla">
            <thead>
                <tr>
                    <th><?= t('code') ?></th>
                    <th><?= t('code_type') ?></th>
                    <th><?= t('code_value') ?></th>
                    <th><?= t('code_uses') ?></th>
                    <th><?= t('code_expires') ?></th>
                    <th><?= t('code_status') ?></th>
                    <th><?= t('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($codigos as $c): ?>
                    <tr>
                        <td><strong style="color:#f0c94a;"><?= htmlspecialchars($c['codigo']) ?></strong></td>
                        <td><?= $c['tipo'] === 'oro' ? '💰 ' . t('gold') : '🎁 Item' ?></td>
                        <td><?= $c['tipo'] === 'oro' ? number_format($c['valor']) . ' ' . t('gold') : 'ID ' . $c['valor'] ?></td>
                        <td><?= $c['usos_actuales'] ?> / <?= $c['usos_maximos'] ?></td>
                        <td><?= $c['fecha_expiracion'] ? date('d/m/Y', strtotime($c['fecha_expiracion'])) : '-' ?></td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <!-- NUEVO: Token CSRF -->
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="toggle_id" value="<?= $c['id'] ?>">
                                <button type="submit" name="toggle_codigo" class="btn-borrar" style="opacity:.85;">
                                    <?= $c['activo'] ? '✅' : '❌' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('<?= t('are_you_sure') ?>');">
                                <!-- NUEVO: Token CSRF -->
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                                <button type="submit" name="borrar_codigo" class="btn-borrar"><?= t('delete') ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
