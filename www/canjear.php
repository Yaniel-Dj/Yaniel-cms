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
    $stmt = $pdo_char->prepare("SELECT guid, name, level FROM characters WHERE account = ? ORDER BY level DESC");
    $stmt->execute([$web_id]);
    $personajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// ============================================
// CANJEAR CÓDIGO
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['canjear'])) {
    $codigo_input = strtoupper(trim($_POST['codigo'] ?? ''));
    $char_guid = (int)($_POST['char_guid'] ?? 0);

    if (empty($codigo_input) || $char_guid <= 0) {
        $mensaje = t('required_fields'); $tipo = 'error';
    } else {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo_char = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $stmt_char = $pdo_char->prepare("SELECT name, money FROM characters WHERE guid = ? AND account = ?");
            $stmt_char->execute([$char_guid, $web_id]);
            $personaje = $stmt_char->fetch(PDO::FETCH_ASSOC);

            if (!$personaje) {
                $mensaje = t('char_not_yours'); $tipo = 'error';
            } else {
                $stmt = $pdo_shop->prepare("SELECT * FROM web_gift_codes WHERE codigo = ? AND activo = 1");
                $stmt->execute([$codigo_input]);
                $gift = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$gift) {
                    $mensaje = t('code_not_found'); $tipo = 'error';
                } 
                elseif ($gift['fecha_expiracion'] && strtotime($gift['fecha_expiracion']) < time()) {
                    $mensaje = t('code_expired'); $tipo = 'error';
                }
                elseif ($gift['usos_actuales'] >= $gift['usos_maximos']) {
                    $mensaje = t('code_max_uses'); $tipo = 'error';
                }
                else {
                    $stmt_check = $pdo_shop->prepare("SELECT id FROM web_gift_redemptions WHERE codigo_id = ? AND account_id = ?");
                    $stmt_check->execute([$gift['id'], $web_id]);
                    if ($stmt_check->fetch()) {
                        $mensaje = t('code_already_used'); $tipo = 'error';
                    } else {
                        $exito = false;
                        $detalle = '';

                        if ($gift['tipo'] === 'oro') {
                            $oro_cobre = $gift['valor'] * COBRE_POR_ORO;
                            $stmt_oro = $pdo_char->prepare("UPDATE characters SET money = money + ? WHERE guid = ?");
                            $stmt_oro->execute([$oro_cobre, $char_guid]);
                            $exito = true;
                            $detalle = number_format($gift['valor']) . ' ' . t('gold');
                        } 
                        elseif ($gift['tipo'] === 'item') {
                            try {
                                $conn = new SoapClient(NULL, array(
                                    'location' => "http://" . SOAP_HOST . ":" . SOAP_PORT . "/",
                                    'uri'      => SOAP_URI,
                                    'style'    => SOAP_RPC,
                                    'login'    => SOAP_GM_USER,
                                    'password' => SOAP_GM_PASS,
                                    'connection_timeout' => 5
                                ));
                                $comando = ".send items " . $personaje['name'] . " \"Código Regalo\" \"¡Disfruta tu regalo!\" " . $gift['valor'] . ":1";
                                $conn->executeCommand(new SoapParam($comando, 'command'));
                                $exito = true;
                                $detalle = '1 item (ID: ' . $gift['valor'] . ')';
                            } catch (Exception $e) {
                                $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
                            }
                        }

                        if ($exito) {
                            $stmt_insert = $pdo_shop->prepare("INSERT INTO web_gift_redemptions (codigo_id, account_id, character_guid) VALUES (?, ?, ?)");
                            $stmt_insert->execute([$gift['id'], $web_id, $char_guid]);

                            $stmt_upd = $pdo_shop->prepare("UPDATE web_gift_codes SET usos_actuales = usos_actuales + 1 WHERE id = ?");
                            $stmt_upd->execute([$gift['id']]);

                            if ($gift['usos_actuales'] + 1 >= $gift['usos_maximos']) {
                                $stmt_off = $pdo_shop->prepare("UPDATE web_gift_codes SET activo = 0 WHERE id = ?");
                                $stmt_off->execute([$gift['id']]);
                            }

                            $mensaje = t('code_redeemed') . ' ' . $detalle; $tipo = 'exito';
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= t('redeem_code') ?></title>
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
    <div class="caja caja-logueado" style="max-width: 500px;">
        <h2 style="color:#f0c94a; text-align:center; margin-bottom:10px;">🎁 <?= t('redeem_code') ?></h2>
        <p style="color:#9db4d8; text-align:center; font-size:13px; margin-bottom:25px;"><?= t('code_ph') ?></p>

        <?php if ($mensaje): ?>
            <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if (empty($personajes)): ?>
            <div class="vacio"><?= t('no_characters') ?></div>
        <?php else: ?>
            <form method="POST">
                <label for="codigo"><?= t('code') ?></label>
                <input type="text" id="codigo" name="codigo" placeholder="<?= t('code_ph') ?>" required style="text-transform: uppercase;">

                <label for="char_guid"><?= t('select_char') ?></label>
                <select id="char_guid" name="char_guid" required>
                    <option value="">-- <?= t('select_char') ?> --</option>
                    <?php foreach ($personajes as $pj): ?>
                        <option value="<?= $pj['guid'] ?>">
                            <?= htmlspecialchars($pj['name']) ?> (<?= t('level') ?> <?= $pj['level'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="canjear"><?= t('redeem_btn') ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
