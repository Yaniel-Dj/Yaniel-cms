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

define('COSTE_CAMBIO_PASS', 500);

// ============================================
// CARGAR PERSONAJES
// ============================================
$personajes = [];
try {
    $pdo_char = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo_char->prepare("SELECT guid, name, level, race, class, money, online FROM characters WHERE account = ? ORDER BY level DESC");
    $stmt->execute([$web_id]);
    $personajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// ============================================
// CARGAR HISTORIAL DE COMPRAS
// ============================================
$compras = [];
try {
    $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sql = "SELECT p.*, c.name AS char_name, i.nombre AS item_nombre
            FROM web_shop_purchases p
            LEFT JOIN " . CHAR_DB_NAME . ".characters c ON p.character_guid = c.guid
            LEFT JOIN web_shop_items i ON p.item_entry = i.item_entry
            WHERE p.account_id = ?
            ORDER BY p.id DESC LIMIT 10";
    $stmt = $pdo_shop->prepare($sql);
    $stmt->execute([$web_id]);
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// ============================================
// CARGAR RIFA ACTIVA
// ============================================
$rifa = null;
try {
    $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo_shop->query("SELECT * FROM web_rifas WHERE estado = 'activa' ORDER BY id DESC LIMIT 1");
    $rifa = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// ============================================
// COMPRAR BOLETOS DE RIFA
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar_boleto_cuenta'])) {
    $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
    $char_guid = (int)($_POST['char_guid_rifa'] ?? 0);

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

// ============================================
// CARGAR PARTICIPANTES DE LA RIFA
// ============================================
$participantes = [];
if ($rifa) {
    try {
        $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt_part = $pdo_shop->prepare("SELECT character_name, SUM(cantidad) as total_boletos FROM web_rifas_boletos WHERE rifa_id = ? GROUP BY character_name ORDER BY total_boletos DESC");
        $stmt_part->execute([$rifa['id']]);
        $participantes = $stmt_part->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}

$nombre_rifa = $rifa['item_nombre'] ?? '';
if ($rifa && $idioma === 'en' && !empty($rifa['item_nombre_en'])) {
    $nombre_rifa = $rifa['item_nombre_en'];
}

// ============================================
// FUNCIÓN PARA CALCULAR SRP6
// ============================================
function calculateSRP6Verifier($username, $password) {
    $g = gmp_init(7);
    $N = gmp_init('894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7', 16);

    $h1 = sha1(strtoupper($username) . ':' . strtoupper($password), true);
    $salt = random_bytes(32);
    $h2 = sha1($salt . $h1, true);

    $h2_int = gmp_import($h2, 1, GMP_LSW_FIRST);
    $verifier = gmp_powm($g, $h2_int, $N);

    $verifier_bin = gmp_export($verifier, 1, GMP_LSW_FIRST);
    $verifier_bin = str_pad($verifier_bin, 32, chr(0), STR_PAD_RIGHT);

    return ['salt' => $salt, 'verifier' => $verifier_bin];
}

// ============================================
// PROCESAR CAMBIO DE CONTRASEÑA
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_pass'])) {
    $pass_actual = $_POST['pass_actual'] ?? '';
    $pass_nueva  = $_POST['pass_nueva'] ?? '';
    $pass_nueva2 = $_POST['pass_nueva2'] ?? '';
    $char_guid   = (int)($_POST['char_guid'] ?? 0);

    if (empty($pass_actual) || empty($pass_nueva) || empty($pass_nueva2)) {
        $mensaje = t('required_fields'); $tipo = 'error';
    } elseif ($char_guid <= 0) {
        $mensaje = t('char_that_pays') . ': ' . t('required_fields'); $tipo = 'error';
    } elseif (strlen($pass_nueva) < MIN_PASSWORD_LENGTH || strlen($pass_nueva) > MAX_PASSWORD_LENGTH) {
        $mensaje = t('register_password_len') . ' ' . MIN_PASSWORD_LENGTH . ' ' . t('and') . ' ' . MAX_PASSWORD_LENGTH; $tipo = 'error';
    } elseif ($pass_nueva !== $pass_nueva2) {
        $mensaje = t('register_pass_mismatch'); $tipo = 'error';
    } else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo_char = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            $stmt_pj = $pdo_char->prepare("SELECT name, money FROM characters WHERE guid = ? AND account = ?");
            $stmt_pj->execute([$char_guid, $web_id]);
            $pj = $stmt_pj->fetch(PDO::FETCH_ASSOC);

            $coste_en_cobre = COSTE_CAMBIO_PASS * COBRE_POR_ORO;

            if (!$pj) {
                $mensaje = t('char_not_yours'); $tipo = 'error';
            } elseif ($pj['money'] < $coste_en_cobre) {
                $mensaje = t('not_enough_gold') . ' ' . t('need') . ' ' . number_format(COSTE_CAMBIO_PASS) . ' ' . t('gold'); $tipo = 'error';
            } else {
                $stmt = $pdo->prepare("SELECT salt, verifier FROM account WHERE id = ?");
                $stmt->execute([$web_id]);
                $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

                $g = gmp_init(7);
                $N = gmp_init('894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7', 16);
                $h1 = sha1(strtoupper($web_user) . ':' . strtoupper($pass_actual), true);
                $h2 = sha1($cuenta['salt'] . $h1, true);
                $h2_int = gmp_import($h2, 1, GMP_LSW_FIRST);
                $verifier_calc = gmp_powm($g, $h2_int, $N);
                $verifier_bin = gmp_export($verifier_calc, 1, GMP_LSW_FIRST);
                $verifier_bin = str_pad($verifier_bin, 32, chr(0), STR_PAD_RIGHT);

                if (!hash_equals($cuenta['verifier'], $verifier_bin)) {
                    $mensaje = t('pass_wrong'); $tipo = 'error';
                } else {
                    $nuevo_srp = calculateSRP6Verifier($web_user, $pass_nueva);
                    
                    $stmt_oro = $pdo_char->prepare("UPDATE characters SET money = money - ? WHERE guid = ?");
                    $stmt_oro->execute([$coste_en_cobre, $char_guid]);

                    $stmt_upd = $pdo->prepare("UPDATE account SET salt = ?, verifier = ? WHERE id = ?");
                    $stmt_upd->execute([$nuevo_srp['salt'], $nuevo_srp['verifier'], $web_id]);

                    $mensaje = t('pass_changed') . ' ' . number_format(COSTE_CAMBIO_PASS) . ' ' . t('gold') . '.'; $tipo = 'exito';
                }
            }
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// Mapeo razas y clases
$razas = [1 => 'Humano', 2 => 'Orco', 3 => 'Enano', 4 => 'Elfo de la Noche', 5 => 'No-Muerto', 6 => 'Tauren', 7 => 'Gnomo', 8 => 'Trol', 10 => 'Elfo de Sangre', 11 => 'Draenei'];
$clases = [1 => 'Guerrero', 2 => 'Paladín', 3 => 'Cazador', 4 => 'Pícaro', 5 => 'Sacerdote', 6 => 'Caballero de la Muerte', 7 => 'Chamán', 8 => 'Mago', 9 => 'Brujo', 11 => 'Druida'];
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= t('my_account') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/tienda.css">
    <style>
        .cuenta-tabs { display: flex; gap: 8px; margin-bottom: 25px; flex-wrap: wrap; border-bottom: 1px solid rgba(212,175,55,0.2); padding-bottom: 15px; }
        .cuenta-tab { padding: 10px 18px; background: rgba(0,0,0,0.3); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; color: #9db4d8; text-decoration: none; font-weight: 600; font-size: 13px; transition: 0.2s; }
        .cuenta-tab:hover { background: rgba(212,175,55,0.1); color: #f0c94a; }
        .cuenta-tab.active { background: rgba(212,175,55,0.2); color: #f0c94a; border-color: #f0c94a; }
        
        .seccion-cuenta { display: none; }
        .seccion-cuenta.activa { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .char-card { background: rgba(0,10,30,0.6); border: 1px solid rgba(212,175,55,0.3); border-radius: 10px; padding: 15px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .char-info h4 { color: #f0c94a; margin-bottom: 5px; font-size: 16px; }
        .char-info span { font-size: 12px; color: #9db4d8; margin-right: 10px; }
        .char-oro { color: #f0c94a; font-weight: bold; font-size: 14px; }

        .compra-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 13px; }
        .compra-item:last-child { border-bottom: none; }

        .rifa-card-cuenta { background: rgba(0,10,30,0.7); border: 2px solid #f0c94a; border-radius: 15px; padding: 25px; text-align: center; margin-bottom: 20px; box-shadow: 0 0 30px rgba(240,201,74,0.2); }
        .rifa-card-cuenta h3 { color: #f0c94a; font-size: 20px; margin-bottom: 10px; }
        .rifa-card-cuenta .timer { font-size: 18px; color: #ff9aa4; font-weight: bold; margin: 10px 0; }
        .rifa-card-cuenta .precio { font-size: 16px; color: #fff; margin-bottom: 15px; }
        .rifa-card-cuenta .precio span { color: #f0c94a; font-weight: bold; }
        .tabla-participantes-cuenta { width: 100%; border-collapse: collapse; margin-top: 15px; background: rgba(0,0,0,0.3); border-radius: 10px; overflow: hidden; }
        .tabla-participantes-cuenta th { background: rgba(212,175,55,0.2); color: #f0c94a; padding: 8px; text-align: left; font-size: 12px; }
        .tabla-participantes-cuenta td { padding: 6px 8px; border-bottom: 1px solid rgba(255,255,255,0.05); color: #c9d8f0; font-size: 12px; }

        .user-fixed-box { position: fixed; top: 15px; right: 20px; z-index: 9999; display: flex; align-items: center; gap: 10px; background: rgba(8, 18, 40, 0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(240,201,74,0.3); border-radius: 30px; padding: 5px 6px 5px 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
        .user-fixed-name { color: #f0c94a; font-weight: 700; font-size: 13px; white-space: nowrap; display: flex; align-items: center; gap: 6px; }
        .user-fixed-name .badge-gm { background: rgba(240,201,74,0.2); border: 1px solid #f0c94a; color: #f0c94a; font-size: 9px; padding: 1px 5px; border-radius: 8px; font-weight: 700; }
        .btn-lang-fixed, .btn-back-fixed, .btn-logout-fixed { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; font-size: 13px; text-decoration: none; transition: all 0.25s ease; }
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

<div class="topbar">
    <nav class="nav">
        <div class="brand-group">
            <a href="index.php" class="brand"><?= htmlspecialchars(SERVER_NAME) ?></a>
        </div>
    </nav>
</div>

<div class="contenedor">
    <div class="caja caja-logueado">
        
        <h2 style="color:#f0c94a; text-align:center; margin-bottom:5px;">👤 <?= t('my_account') ?></h2>
        <p style="color:#9db4d8; text-align:center; font-size:13px; margin-bottom:30px;"><?= t('my_account_subtitle') ?></p>

        <?php if ($mensaje): ?>
            <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <div class="cuenta-tabs">
            <a href="?tab=personajes" class="cuenta-tab <?= (($_GET['tab'] ?? 'personajes') === 'personajes') ? 'active' : '' ?>">👥 <?= t('my_characters') ?></a>
            <a href="?tab=password" class="cuenta-tab <?= (($_GET['tab'] ?? '') === 'password') ? 'active' : '' ?>">🔑 <?= t('change_password') ?></a>
            <a href="?tab=historial" class="cuenta-tab <?= (($_GET['tab'] ?? '') === 'historial') ? 'active' : '' ?>">📜 <?= t('purchase_history') ?></a>
            <a href="?tab=canjear" class="cuenta-tab <?= (($_GET['tab'] ?? '') === 'canjear') ? 'active' : '' ?>">🎁 <?= t('redeem_code') ?></a>
            <a href="?tab=rifa" class="cuenta-tab <?= (($_GET['tab'] ?? '') === 'rifa') ? 'active' : '' ?>">🎟️ <?= t('current_raffle') ?></a>
        </div>

        <?php $tab_actual = $_GET['tab'] ?? 'personajes'; ?>

        <!-- 1. MIS PERSONAJES -->
        <div class="seccion-cuenta <?= $tab_actual === 'personajes' ? 'activa' : '' ?>">
            <?php if (empty($personajes)): ?>
                <div class="vacio"><?= t('no_characters') ?></div>
            <?php else: ?>
                <?php foreach ($personajes as $pj): ?>
                    <div class="char-card">
                        <div class="char-info">
                            <h4><?= htmlspecialchars($pj['name']) ?> <?php if ($pj['online']): ?><span style="color:#5ac86b; font-size:11px;"><?= t('online_status') ?></span><?php endif; ?></h4>
                            <span><?= t('level') ?> <?= $pj['level'] ?></span>
                            <span><?= $razas[$pj['race']] ?? '?' ?> / <?= $clases[$pj['class']] ?? '?' ?></span>
                        </div>
                        <div class="char-oro">💰 <?= number_format(floor($pj['money'] / COBRE_POR_ORO)) ?> <?= t('gold') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 2. CAMBIAR CONTRASEÑA -->
        <div class="seccion-cuenta <?= $tab_actual === 'password' ? 'activa' : '' ?>">
            <p style="color:#9db4d8; font-size:13px; margin-bottom:20px;">
                <?= t('change_pass_cost') ?> <strong style="color:#f0c94a;"><?= number_format(COSTE_CAMBIO_PASS) ?> <?= t('gold') ?></strong> <?= t('change_pass_cost_2') ?>
            </p>
            <?php if (empty($personajes)): ?>
                <div class="vacio"><?= t('no_characters') ?></div>
            <?php else: ?>
                <form method="POST">
                    <label for="char_guid"><?= t('char_that_pays') ?></label>
                    <select id="char_guid" name="char_guid" required>
                        <option value="">-- <?= t('select_char') ?> --</option>
                        <?php foreach ($personajes as $pj): ?>
                            <option value="<?= $pj['guid'] ?>">
                                <?= htmlspecialchars($pj['name']) ?> (<?= number_format(floor($pj['money'] / COBRE_POR_ORO)) ?> <?= t('gold') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="pass_actual"><?= t('current_password') ?></label>
                    <input type="password" id="pass_actual" name="pass_actual" required>

                    <label for="pass_nueva"><?= t('new_password') ?></label>
                    <input type="password" id="pass_nueva" name="pass_nueva" required minlength="<?= MIN_PASSWORD_LENGTH ?>" maxlength="<?= MAX_PASSWORD_LENGTH ?>">

                    <label for="pass_nueva2"><?= t('repeat_new_password') ?></label>
                    <input type="password" id="pass_nueva2" name="pass_nueva2" required minlength="<?= MIN_PASSWORD_LENGTH ?>" maxlength="<?= MAX_PASSWORD_LENGTH ?>">

                    <button type="submit" name="cambiar_pass"><?= t('change_password') ?></button>
                </form>
            <?php endif; ?>
        </div>

        <!-- 3. HISTORIAL DE COMPRAS -->
        <div class="seccion-cuenta <?= $tab_actual === 'historial' ? 'activa' : '' ?>">
            <?php if (empty($compras)): ?>
                <div class="vacio"><?= t('no_purchases') ?></div>
            <?php else: ?>
                <?php foreach ($compras as $c): ?>
                    <div class="compra-item">
                        <div>
                            <strong style="color:#f0c94a;"><?= htmlspecialchars($c['item_nombre'] ?? 'Item #' . $c['item_entry']) ?></strong><br>
                            <span style="color:#9db4d8; font-size:11px;">
                                👤 <?= htmlspecialchars($c['char_name'] ?? '?') ?> · 
                                🕐 <?= $c['fecha_compra'] ? date('d/m/Y H:i', strtotime($c['fecha_compra'])) : '?' ?>
                            </span>
                        </div>
                        <div style="text-align:right;">
                            <span style="color:#f0c94a; font-weight:bold;"><?= number_format($c['precio_pagado'] / COBRE_POR_ORO) ?> <?= t('gold') ?></span><br>
                            <span style="font-size:10px; color:<?= $c['estado'] === 'enviado' ? '#5ac86b' : ($c['estado'] === 'fallido' ? '#ff5c5c' : '#f0c94a') ?>;">
                                <?= t('status_' . $c['estado']) ?? ucfirst($c['estado']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 4. CANJEAR CÓDIGO -->
        <div class="seccion-cuenta <?= $tab_actual === 'canjear' ? 'activa' : '' ?>">
            <p style="color:#9db4d8; font-size:13px; margin-bottom:20px;"><?= t('redeem_code') ?></p>
            <form action="canjear.php" method="POST">
                <label for="codigo"><?= t('code') ?></label>
                <input type="text" id="codigo" name="codigo" placeholder="<?= t('code_ph') ?>" required style="text-transform: uppercase;">
                <label for="char_guid_canjear"><?= t('select_char') ?></label>
                <select id="char_guid_canjear" name="char_guid" required>
                    <option value="">-- <?= t('select_char') ?> --</option>
                    <?php foreach ($personajes as $pj): ?>
                        <option value="<?= $pj['guid'] ?>"><?= htmlspecialchars($pj['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="canjear"><?= t('redeem_btn') ?></button>
            </form>
        </div>

        <!-- 5. RIFA ACTUAL -->
        <div class="seccion-cuenta <?= $tab_actual === 'rifa' ? 'activa' : '' ?>">
            <?php if (!$rifa): ?>
                <div class="vacio"><span class="icono">🎟️</span><?= t('raffle_no_active') ?></div>
            <?php else: ?>
                <div class="rifa-card-cuenta">
                    <h3>🎟️ <?= t('raffle_prize') ?>: <?= htmlspecialchars($nombre_rifa) ?></h3>
                    <p style="color:#9db4d8; font-size:13px;"><?= t('raffle_buy_tickets') ?></p>
                    
                    <div class="timer">
                        ⏳ <?= t('raffle_ends_in') ?>: <span id="countdown-cuenta"></span>
                    </div>
                    <div class="precio"><?= t('raffle_ticket_price') ?>: <span><?= number_format($rifa['precio_boleto']) ?> <?= t('gold') ?></span></div>

                    <?php if (empty($personajes)): ?>
                        <p style="color:#ff9aa4;"><?= t('no_characters') ?></p>
                    <?php else: ?>
                        <form method="POST" style="max-width: 400px; margin: 0 auto;">
                            <label for="char_guid_rifa"><?= t('select_char') ?></label>
                            <select id="char_guid_rifa" name="char_guid_rifa" required>
                                <option value="">-- <?= t('select_char') ?> --</option>
                                <?php foreach ($personajes as $pj): ?>
                                    <option value="<?= $pj['guid'] ?>">
                                        <?= htmlspecialchars($pj['name']) ?> (<?= number_format(floor($pj['money'] / COBRE_POR_ORO)) ?> <?= t('gold') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label for="cantidad"><?= t('raffle_quantity') ?></label>
                            <input type="number" id="cantidad" name="cantidad" value="1" min="1" required>

                            <button type="submit" name="comprar_boleto_cuenta">🎟️ <?= t('raffle_buy_btn') ?></button>
                        </form>
                    <?php endif; ?>
                </div>

                <h4 style="color:#f0c94a; text-align:center; margin-bottom:10px;"><?= t('raffle_participants') ?></h4>
                <?php if (empty($participantes)): ?>
                    <p style="text-align:center; color:#9db4d8;"><?= t('raffle_no_participants') ?></p>
                <?php else: ?>
                    <table class="tabla-participantes-cuenta">
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
            var el = document.getElementById("countdown-cuenta");
            if (el) el.innerHTML = "<?= t('raffle_ended') ?>";
            return;
        }
        var dias = Math.floor(distancia / (1000 * 60 * 60 * 24));
        var horas = Math.floor((distancia % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutos = Math.floor((distancia % (1000 * 60 * 60)) / (1000 * 60));
        var segundos = Math.floor((distancia % (1000 * 60)) / 1000);
        var el = document.getElementById("countdown-cuenta");
        if (el) el.innerHTML = dias + "d " + horas + "h " + minutos + "m " + segundos + "s";
    }, 1000);
</script>
<?php endif; ?>

</body>
</html>