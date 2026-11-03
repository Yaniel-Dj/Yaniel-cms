<?php
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/config.php';

if (empty($_SESSION['web_logueado'])) {
    header('Location: index.php');
    exit;
}

$web_user = $_SESSION['web_username'] ?? '';
$web_id   = $_SESSION['web_user_id'] ?? 0;

$mensaje = '';
$tipo = '';

// Cargar personajes del usuario
$personajes = [];
try {
    $pdo_char = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo_char->prepare("SELECT guid, name, level, money FROM characters WHERE account = ? ORDER BY level DESC");
    $stmt->execute([$web_id]);
    $personajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Cargar rifa activa
$rifa = null;
try {
    $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo_shop->query("SELECT * FROM web_rifas WHERE estado = 'activa' ORDER BY id DESC LIMIT 1");
    $rifa = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// ============================================
// COMPRAR BOLETOS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar_boleto'])) {
    $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
    $char_guid = (int)($_POST['char_guid'] ?? 0);

    if (!$rifa) {
        $mensaje = t('raffle_no_active'); $tipo = 'error';
    } elseif ($char_guid <= 0) {
        $mensaje = t('required_fields'); $tipo = 'error';
    } else {
        try {
            $pdo_char = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $stmt_char = $pdo_char->prepare("SELECT name, money FROM characters WHERE guid = ? AND account = ?");
            $stmt_char->execute([$char_guid, $web_id]);
            $personaje = $stmt_char->fetch(PDO::FETCH_ASSOC);

            if (!$personaje) {
                $mensaje = t('char_not_yours'); $tipo = 'error';
            } else {
                $coste_total = $rifa['precio_boleto'] * $cantidad * COBRE_POR_ORO;
                
                if ($personaje['money'] < $coste_total) {
                    $mensaje = t('not_enough_gold') . ' ' . t('need') . ' ' . number_format($rifa['precio_boleto'] * $cantidad) . ' ' . t('gold'); $tipo = 'error';
                } else {
                    $stmt_oro = $pdo_char->prepare("UPDATE characters SET money = money - ? WHERE guid = ?");
                    $stmt_oro->execute([$coste_total, $char_guid]);

                    $stmt_insert = $pdo_shop->prepare("INSERT INTO web_rifas_boletos (rifa_id, account_id, character_guid, character_name, cantidad) VALUES (?, ?, ?, ?, ?)");
                    $stmt_insert->execute([$rifa['id'], $web_id, $char_guid, $personaje['name'], $cantidad]);

                    $mensaje = t('raffle_tickets_bought') . ' ' . $cantidad . ' ' . t('raffle_tickets_bought_2') . ' ' . htmlspecialchars($rifa['item_nombre']) . '!'; $tipo = 'exito';
                }
            }
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// Cargar participantes
$participantes = [];
if ($rifa) {
    try {
        $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt_part = $pdo_shop->prepare("SELECT character_name, SUM(cantidad) as total_boletos FROM web_rifas_boletos WHERE rifa_id = ? GROUP BY character_name ORDER BY total_boletos DESC");
        $stmt_part->execute([$rifa['id']]);
        $participantes = $stmt_part->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}

// Nombre del item según el idioma
$nombre_rifa = $rifa['item_nombre'] ?? '';
if ($rifa && $idioma === 'en' && !empty($rifa['item_nombre_en'])) {
    $nombre_rifa = $rifa['item_nombre_en'];
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= t('raffle') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/tienda.css">
    <style>
        .user-fixed-box {
            position: fixed; top: 15px; right: 20px; z-index: 9999;
            display: flex; align-items: center; gap: 10px;
            background: rgba(8, 18, 40, 0.85); backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(240,201,74,0.3); border-radius: 30px;
            padding: 5px 6px 5px 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }
        .user-fixed-name { color: #f0c94a; font-weight: 700; font-size: 13px; white-space: nowrap; display: flex; align-items: center; gap: 6px; }
        .user-fixed-name .badge-gm { background: rgba(240,201,74,0.2); border: 1px solid #f0c94a; color: #f0c94a; font-size: 9px; padding: 1px 5px; border-radius: 8px; font-weight: 700; }
        .btn-lang-fixed, .btn-back-fixed, .btn-logout-fixed {
            display: inline-flex; align-items: center; justify-content: center;
            width: 34px; height: 34px; border-radius: 50%; font-size: 13px;
            text-decoration: none; transition: all 0.25s ease;
        }
        .btn-lang-fixed { background: rgba(240, 201, 74, 0.15); border: 2px solid #f0c94a; color: #f0c94a; font-size: 11px; font-weight: 700; }
        .btn-lang-fixed:hover { background: #f0c94a; color: #1a1200; transform: scale(1.1); box-shadow: 0 0 15px rgba(240,201,74,0.6); }
        .btn-back-fixed { background: rgba(240, 201, 74, 0.15); border: 2px solid #f0c94a; color: #f0c94a; }
        .btn-back-fixed:hover { background: #f0c94a; color: #1a1200; transform: scale(1.1); box-shadow: 0 0 15px rgba(240,201,74,0.6); }
        .btn-logout-fixed { background: rgba(220, 53, 69, 0.2); border: 2px solid #dc3545; color: #ff9aa4; }
        .btn-logout-fixed:hover { background: #dc3545; color: #fff; transform: scale(1.1); box-shadow: 0 0 15px rgba(220,53,69,0.6); }

        .rifa-card { background: rgba(0,10,30,0.7); border: 2px solid #f0c94a; border-radius: 15px; padding: 30px; text-align: center; margin-bottom: 30px; box-shadow: 0 0 30px rgba(240,201,74,0.2); }
        .rifa-card h2 { color: #f0c94a; font-size: 24px; margin-bottom: 10px; }
        .rifa-timer { font-size: 20px; color: #ff9aa4; font-weight: bold; margin: 15px 0; }
        .rifa-precio { font-size: 18px; color: #fff; margin-bottom: 20px; }
        .rifa-precio span { color: #f0c94a; font-weight: bold; }
        .tabla-participantes { width: 100%; border-collapse: collapse; margin-top: 20px; background: rgba(0,0,0,0.3); border-radius: 10px; overflow: hidden; }
        .tabla-participantes th { background: rgba(212,175,55,0.2); color: #f0c94a; padding: 10px; text-align: left; }
        .tabla-participantes td { padding: 8px 10px; border-bottom: 1px solid rgba(255,255,255,0.05); color: #c9d8f0; }
    </style>
</head>
<body>

<div class="user-fixed-box">
    <span class="user-fixed-name">
        <i class="fa-solid fa-user" style="color:#9db4d8;"></i>
        <?= htmlspecialchars($web_user) ?>
        <?php if (!empty($_SESSION['es_admin'])): ?><span class="badge-gm">GM</span><?php endif; ?>
    </span>
    <a href="?lang=es" class="btn-lang-fixed" title="Español" style="<?= $idioma === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
    <a href="?lang=en" class="btn-lang-fixed" title="English" style="<?= $idioma === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
    <a href="index.php" class="btn-back-fixed" title="<?= t('back') ?>"><i class="fa-solid fa-arrow-left"></i></a>
    <a href="index.php?logout=1" class="btn-logout-fixed" title="<?= t('logout') ?>"><i class="fa-solid fa-right-from-bracket"></i></a>
</div>

<div class="contenedor" style="padding-top: 80px;">
    <div class="caja caja-logueado">

        <?php if ($mensaje): ?>
            <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if (!$rifa): ?>
            <div class="vacio"><span class="icono">🎟️</span><?= t('raffle_no_active') ?></div>
        <?php else: ?>
            <div class="rifa-card">
                <h2>🎟️ <?= t('raffle_prize') ?>: <?= htmlspecialchars($nombre_rifa) ?></h2>
                <p style="color:#9db4d8;"><?= t('raffle_buy_tickets') ?></p>
                
                <div class="rifa-timer">
                    ⏳ <?= t('raffle_ends_in') ?>: <span id="countdown"></span>
                </div>
                <div class="rifa-precio"><?= t('raffle_ticket_price') ?>: <span><?= number_format($rifa['precio_boleto']) ?> <?= t('gold') ?></span></div>

                <?php if (empty($personajes)): ?>
                    <p style="color:#ff9aa4;"><?= t('no_characters') ?></p>
                <?php else: ?>
                    <form method="POST" style="max-width: 400px; margin: 0 auto;">
                        <label for="char_guid"><?= t('select_char') ?></label>
                        <select id="char_guid" name="char_guid" required>
                            <option value="">-- <?= t('select_char') ?> --</option>
                            <?php foreach ($personajes as $pj): ?>
                                <option value="<?= $pj['guid'] ?>">
                                    <?= htmlspecialchars($pj['name']) ?> (<?= number_format(floor($pj['money'] / COBRE_POR_ORO)) ?> <?= t('gold') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label for="cantidad"><?= t('raffle_quantity') ?></label>
                        <input type="number" id="cantidad" name="cantidad" value="1" min="1" required>

                        <button type="submit" name="comprar_boleto">🎟️ <?= t('raffle_buy_btn') ?></button>
                    </form>
                <?php endif; ?>
            </div>

            <h3 style="color:#f0c94a; text-align:center; margin-bottom:15px;"><?= t('raffle_participants') ?></h3>
            <?php if (empty($participantes)): ?>
                <p style="text-align:center; color:#9db4d8;"><?= t('raffle_no_participants') ?></p>
            <?php else: ?>
                <table class="tabla-participantes">
                    <thead>
                        <tr>
                            <th><?= t('name') ?></th>
                            <th><?= t('quantity') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participantes as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['character_name']) ?></strong></td>
                                <td><?= $p['total_boletos'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($rifa): ?>
<script>
    // CORREGIDO: usar el timestamp de PHP (sin ambigüedad de zonas horarias)
    var fechaFin = <?= strtotime($rifa['fecha_fin']) * 1000 ?>;

    var timer = setInterval(function() {
        var ahora = new Date().getTime();
        var distancia = fechaFin - ahora;
        if (distancia < 0) {
            clearInterval(timer);
            var el = document.getElementById("countdown");
            if (el) el.innerHTML = "<?= t('raffle_ended') ?>";
            return;
        }
        var dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
        var horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
        var segundos = Math.floor((distancia % (1000 * 60)) / 1000);
        var el = document.getElementById("countdown");
        if (el) el.innerHTML = dias + "d " + horas + "h " + minutos + "m " + segundos + "s";
    }, 1000);
</script>
<?php endif; ?>

</body>
</html>