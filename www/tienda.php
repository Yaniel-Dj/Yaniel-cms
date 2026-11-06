<?php
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/config.php';

if (empty($_SESSION['web_logueado'])) {
    header('Location: index.php');
    exit;
}

// ============================================
// NUEVO: GENERAR TOKEN CSRF SI NO EXISTE
// ============================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$web_user = $_SESSION['web_username'] ?? '';
$web_id   = $_SESSION['web_user_id'] ?? 0;

$uploads_url = 'uploads/tienda/';

$mensaje = '';
$tipo = '';

// ============================================
// SOAP ENVIAR ITEM
// ============================================
function enviarItemPorSOAP($personaje, $itemID, $cantidad = 1) {
    try {
        $conn = new SoapClient(NULL, array(
            'location' => "http://" . SOAP_HOST . ":" . SOAP_PORT . "/",
            'uri'      => SOAP_URI,
            'style'    => SOAP_RPC,
            'login'    => SOAP_GM_USER,
            'password' => SOAP_GM_PASS,
            'connection_timeout' => 5
        ));
        $comando = ".send items " . $personaje . " \"Tienda\" \"Gracias por tu compra\" " . $itemID . ":" . $cantidad;
        $resultado = $conn->executeCommand(new SoapParam($comando, 'command'));
        unset($conn);
        return [true, (string)$resultado];
    } catch (Exception $e) {
        return [false, $e->getMessage()];
    }
}

// ============================================
// COMPRAR ITEM
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar_item'])) {
    // NUEVO: Verificar Token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Acceso denegado: Token de seguridad inválido.");
    }

    $item_id = (int)($_POST['item_id'] ?? 0);
    $char_guid = (int)($_POST['char_guid'] ?? 0);
    if ($item_id <= 0 || $char_guid <= 0) {
        $mensaje = t('buy_invalid'); $tipo = 'error';
    } else {
        try {
            $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo_shop->prepare("SELECT * FROM web_shop_items WHERE id = ? AND activo = 1");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) {
                $mensaje = t('item_not_found'); $tipo = 'error';
            } else {
                $pdo_char = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                $stmt_char = $pdo_char->prepare("SELECT name, money, online FROM characters WHERE guid = ? AND account = ?");
                $stmt_char->execute([$char_guid, $web_id]);
                $personaje = $stmt_char->fetch(PDO::FETCH_ASSOC);

                if (!$personaje) {
                    $mensaje = t('char_not_yours'); $tipo = 'error';
                }
                elseif ($personaje['online']) {
                    $mensaje = t('char_online_error'); $tipo = 'error';
                }
                elseif ($personaje['money'] < $item['precio']) {
                    $oro_tiene = floor($personaje['money'] / COBRE_POR_ORO);
                    $oro_necesita = floor($item['precio'] / COBRE_POR_ORO);
                    $mensaje = t('not_enough_gold') . ' ' . t('need') . ' ' . $oro_necesita . ' ' . t('gold') . ' ' . t('have') . ' ' . $oro_tiene . ' ' . t('gold'); $tipo = 'error';
                } else {
                    $stmt_restar = $pdo_char->prepare("UPDATE characters SET money = money - ? WHERE guid = ?");
                    $stmt_restar->execute([$item['precio'], $char_guid]);

                    $stmt_compra = $pdo_shop->prepare("INSERT INTO web_shop_purchases (account_id, character_guid, item_entry, cantidad, precio_pagado, estado) VALUES (?, ?, ?, 1, ?, 'pendiente')");
                    $stmt_compra->execute([$web_id, $char_guid, $item['item_entry'], $item['precio']]);
                    $compra_id = $pdo_shop->lastInsertId();

                    list($ok, $detalle) = enviarItemPorSOAP($personaje['name'], $item['item_entry'], 1);

                    if ($ok) {
                        $stmt_estado = $pdo_shop->prepare("UPDATE web_shop_purchases SET estado = 'enviado' WHERE id = ?");
                        $stmt_estado->execute([$compra_id]);
                        $mensaje = t('buy_success'); $tipo = 'exito';
                    } else {
                        $stmt_devolver = $pdo_char->prepare("UPDATE characters SET money = money + ? WHERE guid = ?");
                        $stmt_devolver->execute([$item['precio'], $char_guid]);

                        $stmt_estado = $pdo_shop->prepare("UPDATE web_shop_purchases SET estado = 'fallido' WHERE id = ?");
                        $stmt_estado->execute([$compra_id]);

                        $mensaje = t('buy_fail_refund') . ' ' . t('buy_fail_soap') . ' ' . $detalle; $tipo = 'error';
                    }
                }
            }
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// ============================================
// CARGAR DATOS
// ============================================
$productos_tienda = [];
$personajes = [];
$categorias = [];
$cat_actual = $_GET['cat'] ?? 'todas';

try {
    $pdo_shop = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . SHOP_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt_cat = $pdo_shop->query("SELECT * FROM web_shop_categories");
    $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

    if ($cat_actual !== 'todas') {
        $stmt_cat_id = $pdo_shop->prepare("SELECT id FROM web_shop_categories WHERE slug = ?");
        $stmt_cat_id->execute([$cat_actual]);
        $cat_id = $stmt_cat_id->fetchColumn();

        if ($cat_id) {
            $stmt = $pdo_shop->prepare("SELECT * FROM web_shop_items WHERE activo = 1 AND categoria_id = ?");
            $stmt->execute([$cat_id]);
        } else {
            $stmt = $pdo_shop->query("SELECT * FROM web_shop_items WHERE activo = 1");
        }
    } else {
        $stmt = $pdo_shop->query("SELECT * FROM web_shop_items WHERE activo = 1");
    }
    $productos_tienda = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

try {
    $pdo_char = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt_char = $pdo_char->prepare("SELECT guid, name, level, money, online FROM characters WHERE account = ? ORDER BY level DESC");
    $stmt_char->execute([$web_id]);
    $personajes = $stmt_char->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= t('shop') ?></title>
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

        .producto-imagen {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: contain;
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
            margin-bottom: 12px;
            border: 1px solid rgba(212,175,55,0.3);
            display: block;
        }
        .producto-imagen-placeholder {
            width: 100%;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
            margin-bottom: 12px;
            border: 1px solid rgba(212,175,55,0.2);
            color: #7a8590;
            font-size: 48px;
        }
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
    <nav class="nav nav-vertical">
        <div class="brand-group">
            <a href="index.php" class="brand"><?= htmlspecialchars(SERVER_NAME) ?></a>
        </div>
        <div class="menu menu-vertical">
            <?php if (!empty($_SESSION['es_admin'])): ?>
                <a href="admin/index.php" class="btn-nav btn-gm-priority"><span class="ico">👑</span> <?= t('gm_panel') ?></a>
            <?php endif; ?>
            <a href="cuenta.php" class="btn-nav"><span class="ico">👤</span> <?= t('my_account') ?></a>
            <a href="tienda.php" class="btn-nav primary"><span class="ico">🛒</span> <?= t('shop') ?></a>
            <a href="noticias.php" class="btn-nav"><span class="ico">📰</span> <?= t('news_titulo') ?></a>
            <a href="guias.php" class="btn-nav"><span class="ico">📖</span> <?= t('guias_titulo') ?></a>
            <a href="reglas.php" class="btn-nav"><span class="ico">📜</span> <?= t('rules') ?></a>
            <a href="sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
            <a href="ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
        </div>
    </nav>
</div>

<div class="contenedor">
    <div class="caja caja-logueado">

        <div class="bienvenida">
            <?= t('welcome') ?>, <strong><?= htmlspecialchars($web_user) ?></strong>
        </div>

        <?php if (!empty($personajes)): ?>
            <div class="oro-badge">
                <div class="oro-titulo">💰 <?= t('your_gold') ?></div>
                <ul class="oro-lista">
                    <?php foreach ($personajes as $pj): ?>
                        <li>
                            <span class="oro-pj">
                                <?= htmlspecialchars($pj['name']) ?>
                                <?php if ($pj['online']): ?>
                                    <span style="color:#5ac86b; font-size:10px;">● <?= t('online') ?></span>
                                <?php else: ?>
                                    <span style="color:#ff5c5c; font-size:10px;">● <?= t('offline') ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="oro-cantidad"><?= number_format(floor($pj['money'] / COBRE_POR_ORO)) ?> <?= t('gold') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($mensaje): ?>
            <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <div class="tabs" style="margin-bottom: 25px; flex-wrap: wrap;">
            <a href="tienda.php?cat=todas" class="tab <?= $cat_actual === 'todas' ? 'activo' : '' ?>" style="text-decoration:none;"><?= t('all') ?></a>
            <?php foreach ($categorias as $cat): ?>
                <?php
                    $nombre_cat = $cat['nombre'];
                    if ($idioma === 'en' && !empty($cat['nombre_en'])) {
                        $nombre_cat = $cat['nombre_en'];
                    }
                ?>
                <a href="tienda.php?cat=<?= $cat['slug'] ?>" class="tab <?= $cat_actual === $cat['slug'] ? 'activo' : '' ?>" style="text-decoration:none;">
                    <i class="fa-solid <?= $cat['icono'] ?>"></i> <?= htmlspecialchars($nombre_cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($productos_tienda)): ?>
            <div class="vacio"><span class="icono">🛒</span><?= t('shop_empty') ?></div>
        <?php else: ?>
            <div class="tienda-grid">
                <?php foreach ($productos_tienda as $prod): ?>
                    <?php
                        $nombre_prod = $prod['nombre'];
                        if ($idioma === 'en' && !empty($prod['nombre_en'])) {
                            $nombre_prod = $prod['nombre_en'];
                        }
                        $ruta_img = !empty($prod['imagen']) ? $uploads_url . $prod['imagen'] : '';
                        $existe_img = $ruta_img && file_exists(__DIR__ . '/' . $ruta_img);
                    ?>
                    <div class="producto-card">
                        <?php if ($existe_img): ?>
                            <img src="<?= htmlspecialchars($ruta_img) ?>"
                                 alt="<?= htmlspecialchars($nombre_prod) ?>"
                                 class="producto-imagen"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="producto-imagen-placeholder">🛒</div>
                        <?php endif; ?>

                        <div class="producto-nombre"><?= htmlspecialchars($nombre_prod) ?></div>
                        <div class="producto-precio"><?= t('price') ?>: <span><?= number_format($prod['precio'] / COBRE_POR_ORO) ?> <?= t('gold') ?></span></div>
                        <form method="POST" class="producto-form">
                            <!-- NUEVO: Token CSRF -->
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <input type="hidden" name="item_id" value="<?= $prod['id'] ?>">
                            <?php if (empty($personajes)): ?>
                                <p style="color:#ff9aa4; font-size:12px; margin-bottom:10px;"><?= t('no_chars') ?></p>
                                <button type="button" disabled><?= t('buy') ?></button>
                            <?php else: ?>
                                <select name="char_guid" required>
                                    <option value=""><?= t('select_char') ?></option>
                                    <?php foreach ($personajes as $pj): ?>
                                        <option value="<?= $pj['guid'] ?>" <?= $pj['online'] ? 'disabled' : '' ?>>
                                            <?= htmlspecialchars($pj['name']) ?> (<?= t('level') ?> <?= $pj['level'] ?> — <?= number_format(floor($pj['money'] / COBRE_POR_ORO)) ?> <?= t('gold') ?>) <?= $pj['online'] ? '● ' . t('online') : '● ' . t('offline') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="comprar_item"><?= t('buy') ?></button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
