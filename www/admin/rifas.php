<?php
// Incluido desde admin/index.php

$mensaje = '';
$tipo = '';

// CREAR RIFA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_rifa'])) {
    $item_entry     = (int)($_POST['item_entry'] ?? 0);
    $item_nombre    = trim($_POST['item_nombre'] ?? '');
    $item_nombre_en = trim($_POST['item_nombre_en'] ?? '');
    $precio_boleto  = (int)($_POST['precio_boleto'] ?? 0);

    $dias     = max(0, (int)($_POST['dias'] ?? 0));
    $horas    = max(0, (int)($_POST['horas'] ?? 0));
    $minutos  = max(0, (int)($_POST['minutos'] ?? 0));
    $segundos = max(0, (int)($_POST['segundos'] ?? 0));

    $total_segundos = ($dias * 86400) + ($horas * 3600) + ($minutos * 60) + $segundos;

    if ($item_entry <= 0 || empty($item_nombre) || $precio_boleto <= 0) {
        $mensaje = t('required_fields'); $tipo = 'error';
    } elseif ($total_segundos <= 0) {
        $mensaje = 'Debes indicar una duración mayor que 0 (en días, horas, minutos o segundos).'; $tipo = 'error';
    } else {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            $pdo_world = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . WORLD_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt_w = $pdo_world->prepare("SELECT name FROM item_template WHERE entry = ?");
            $stmt_w->execute([$item_entry]);
            $item_world = $stmt_w->fetch(PDO::FETCH_ASSOC);

            if (!$item_world) {
                $mensaje = t('item_not_exist') . ' ' . $item_entry; $tipo = 'error';
            } else {
                $fecha_fin = date('Y-m-d H:i:s', time() + $total_segundos);
                
                $stmt = $pdo_shop->prepare("INSERT INTO web_rifas (item_entry, item_nombre, item_nombre_en, precio_boleto, fecha_fin, estado) VALUES (?, ?, ?, ?, ?, 'activa')");
                $stmt->execute([$item_entry, $item_nombre, $item_nombre_en, $precio_boleto, $fecha_fin]);
                $mensaje = t('created_successfully') . '. Termina el ' . date('d/m/Y H:i:s', strtotime($fecha_fin)); $tipo = 'exito';
            }
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// ACTIVAR / DESACTIVAR RIFA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_rifa'])) {
    $rifa_id = (int)($_POST['toggle_id'] ?? 0);
    if ($rifa_id > 0) {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo_shop->prepare("UPDATE web_rifas SET estado = IF(estado = 'activa', 'pausada', 'activa') WHERE id = ?");
            $stmt->execute([$rifa_id]);
            $mensaje = t('saved_successfully'); $tipo = 'exito';
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// FINALIZAR RIFA (marca como finalizada siempre, aunque SOAP falle)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_rifa'])) {
    $rifa_id = (int)($_POST['rifa_id'] ?? 0);
    if ($rifa_id <= 0) {
        $mensaje = 'ID de rifa inválido.'; $tipo = 'error';
    } else {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            // Verificar que la rifa existe
            $stmt_rifa = $pdo_shop->prepare("SELECT * FROM web_rifas WHERE id = ?");
            $stmt_rifa->execute([$rifa_id]);
            $rifa = $stmt_rifa->fetch(PDO::FETCH_ASSOC);

            if (!$rifa) {
                $mensaje = 'La rifa no existe.'; $tipo = 'error';
            } else {
                $stmt_boletos = $pdo_shop->prepare("SELECT character_guid, character_name, cantidad FROM web_rifas_boletos WHERE rifa_id = ?");
                $stmt_boletos->execute([$rifa_id]);
                $boletos = $stmt_boletos->fetchAll(PDO::FETCH_ASSOC);

                if (empty($boletos)) {
                    $mensaje = t('raffle_no_participants_err') . ' (No se puede finalizar sin participantes)'; $tipo = 'error';
                } else {
                    // Elegir ganador
                    $pool = [];
                    foreach ($boletos as $b) {
                        for ($i = 0; $i < $b['cantidad']; $i++) {
                            $pool[] = ['guid' => $b['character_guid'], 'name' => $b['character_name']];
                        }
                    }
                    $ganador = $pool[array_rand($pool)];

                    // Marcar como finalizada ANTES de SOAP (para que no se quede colgada)
                    $stmt_upd = $pdo_shop->prepare("UPDATE web_rifas SET estado = 'finalizada', ganador_guid = ?, ganador_nombre = ? WHERE id = ?");
                    $stmt_upd->execute([$ganador['guid'], $ganador['name'], $rifa_id]);

                    // Intentar enviar el item por SOAP
                    $detalle_soap = '';
                    try {
                        $conn = new SoapClient(NULL, array(
                            'location' => "http://" . SOAP_HOST . ":" . SOAP_PORT . "/",
                            'uri'      => SOAP_URI,
                            'style'    => SOAP_RPC,
                            'login'    => SOAP_GM_USER,
                            'password' => SOAP_GM_PASS,
                            'connection_timeout' => 5
                        ));
                        $comando = ".send items " . $ganador['name'] . " \"Rifa\" \"¡Felicidades!\" " . $rifa['item_entry'] . ":1";
                        $conn->executeCommand(new SoapParam($comando, 'command'));
                        $mensaje = t('raffle_finished_msg') . ' ' . htmlspecialchars($ganador['name']) . ' (' . t('raffle_item_sent') . ')'; $tipo = 'exito';
                    } catch (Exception $e) {
                        $detalle_soap = $e->getMessage();
                        $mensaje = t('raffle_finished_msg') . ' ' . htmlspecialchars($ganador['name']) . '. ⚠️ El item NO se pudo enviar por SOAP: ' . $detalle_soap . '. Envíalo manualmente con .send items.'; $tipo = 'error';
                    }
                }
            }
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// BORRAR RIFA (manual)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_rifa'])) {
    $del_id = (int)($_POST['delete_id'] ?? 0);
    if ($del_id > 0) {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo_shop->prepare("DELETE FROM web_rifas WHERE id = ?");
            $stmt->execute([$del_id]);
            $stmt_boletos = $pdo_shop->prepare("DELETE FROM web_rifas_boletos WHERE rifa_id = ?");
            $stmt_boletos->execute([$del_id]);
            $mensaje = t('deleted_successfully'); $tipo = 'exito';
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// CARGAR RIFAS
$rifas = [];
try {
    $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo_shop->query("SELECT * FROM web_rifas ORDER BY id DESC");
    $rifas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<?php if ($mensaje): ?>
    <div class="alerta <?= $tipo ?>"><?= $mensaje ?></div>
<?php endif; ?>

<div class="admin-section">
    <h3>🎟️ <?= t('create_raffle') ?></h3>
    <form method="POST">
        <label for="item_entry"><?= t('raffle_item_id') ?></label>
        <input type="number" id="item_entry" name="item_entry" required min="1">

        <label for="item_nombre"><?= t('raffle_item_name') ?> (ES)</label>
        <input type="text" id="item_nombre" name="item_nombre" required>

        <label for="item_nombre_en"><?= t('raffle_item_name') ?> (EN)</label>
        <input type="text" id="item_nombre_en" name="item_nombre_en">

        <label for="precio_boleto"><?= t('raffle_ticket_price_gold') ?></label>
        <input type="number" id="precio_boleto" name="precio_boleto" required min="1">

        <label>Duración de la rifa (rellena al menos uno)</label>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:15px;">
            <div>
                <input type="number" id="dias" name="dias" placeholder="0" min="0" style="margin-bottom:0;">
                <small style="color:#9db4d8; font-size:11px; display:block; text-align:center; margin-top:4px;">Días</small>
            </div>
            <div>
                <input type="number" id="horas" name="horas" placeholder="0" min="0" style="margin-bottom:0;">
                <small style="color:#9db4d8; font-size:11px; display:block; text-align:center; margin-top:4px;">Horas</small>
            </div>
            <div>
                <input type="number" id="minutos" name="minutos" placeholder="0" min="0" style="margin-bottom:0;">
                <small style="color:#9db4d8; font-size:11px; display:block; text-align:center; margin-top:4px;">Minutos</small>
            </div>
            <div>
                <input type="number" id="segundos" name="segundos" placeholder="0" min="0" style="margin-bottom:0;">
                <small style="color:#9db4d8; font-size:11px; display:block; text-align:center; margin-top:4px;">Segundos</small>
            </div>
        </div>
        <small style="color:#9db4d8; font-size:11px; display:block; margin-bottom:15px;">
            Puedes usar solo uno, dos, tres o los cuatro campos. Ejemplo: solo 5 en minutos = rifa de 5 minutos. 2 en días y 3 en horas = rifa de 2 días y 3 horas. Los campos vacíos cuentan como 0.
        </small>

        <button type="submit" name="crear_rifa"><?= t('raffle_create_btn') ?></button>
    </form>
</div>

<div class="admin-section">
    <h3>📋 <?= t('raffle_existing') ?> (<?= count($rifas) ?>)</h3>
    <?php if (empty($rifas)): ?>
        <p style="color:#9db4d8; text-align:center;"><?= t('raffle_no_raffles') ?></p>
    <?php else: ?>
        <table class="admin-tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= t('raffle_item_name') ?> (ES)</th>
                    <th><?= t('raffle_item_name') ?> (EN)</th>
                    <th><?= t('raffle_ticket_price') ?></th>
                    <th><?= t('date') ?></th>
                    <th><?= t('status') ?></th>
                    <th><?= t('raffle_winner') ?></th>
                    <th><?= t('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rifas as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['item_nombre']) ?></td>
                        <td><?= htmlspecialchars($r['item_nombre_en'] ?? '-') ?></td>
                        <td><?= number_format($r['precio_boleto']) ?> <?= t('gold') ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($r['fecha_fin'])) ?></td>
                        <td>
                            <?php if ($r['estado'] === 'activa'): ?>
                                <span style="color:#5ac86b;"><?= t('active') ?></span>
                            <?php elseif ($r['estado'] === 'pausada'): ?>
                                <span style="color:#f0c94a;">Pausada</span>
                            <?php else: ?>
                                <span style="color:#7a8590;"><?= t('raffle_ended') ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $r['ganador_nombre'] ? htmlspecialchars($r['ganador_nombre']) : '-' ?></td>
                        <td>
                            <div style="display:flex; gap:5px; flex-wrap:wrap;">
                                <?php if ($r['estado'] !== 'finalizada'): ?>
                                    <form method="POST" style="margin:0;" onsubmit="return confirm('<?= $r['estado'] === 'activa' ? '¿Pausar esta rifa?' : '¿Activar esta rifa?' ?>');">
                                        <input type="hidden" name="toggle_id" value="<?= $r['id'] ?>">
                                        <button type="submit" name="toggle_rifa" class="btn-borrar" style="background:rgba(240,201,74,0.2); border-color:#f0c94a; color:#f0c94a;">
                                            <?= $r['estado'] === 'activa' ? '⏸️ Pausar' : '▶️ Activar' ?>
                                        </button>
                                    </form>

                                    <form method="POST" style="margin:0;" onsubmit="return confirm('<?= t('raffle_finish_confirm') ?>');">
                                        <input type="hidden" name="rifa_id" value="<?= $r['id'] ?>">
                                        <button type="submit" name="finalizar_rifa" class="btn-borrar" style="background:rgba(220,53,69,0.3); border-color:#dc3545; color:#ff9aa4;"><?= t('raffle_finish') ?></button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="margin:0;" onsubmit="return confirm('¿Borrar esta rifa finalizada? Esta acción no se puede deshacer.');">
                                        <input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
                                        <button type="submit" name="borrar_rifa" class="btn-borrar" style="background:rgba(220,53,69,0.3); border-color:#dc3545; color:#ff9aa4;">🗑️ Borrar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>