<?php
require_once __DIR__ . '/../lang.php';
require_once __DIR__ . '/../config.php';

// Solo admin
if (empty($_SESSION['es_admin'])) {
    header('Location: ../index.php');
    exit;
}

$web_user = $_SESSION['web_username'] ?? '';
$tab = $_GET['tab'] ?? 'items';
if (!in_array($tab, ['items', 'noticias', 'usuarios', 'reglas', 'codigos', 'rifas'], true)) $tab = 'items';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= t('gm_panel') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/layout.css">
    <link rel="stylesheet" href="../css/forms.css">
    <link rel="stylesheet" href="../css/tienda.css">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        /* ===== Caja fija: usuario + idiomas + atrás + logout ===== */
        .user-fixed-box {
            position: fixed;
            top: 15px;
            right: 20px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(8, 18, 40, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(240,201,74,0.3);
            border-radius: 30px;
            padding: 5px 6px 5px 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }
        .user-fixed-name {
            color: #f0c94a;
            font-weight: 700;
            font-size: 13px;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .user-fixed-name .badge-gm {
            background: rgba(240,201,74,0.2);
            border: 1px solid #f0c94a;
            color: #f0c94a;
            font-size: 9px;
            padding: 1px 5px;
            border-radius: 8px;
            font-weight: 700;
        }
        .btn-lang-fixed, .btn-back-fixed, .btn-logout-fixed {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .btn-lang-fixed {
            background: rgba(240, 201, 74, 0.15);
            border: 2px solid #f0c94a;
            color: #f0c94a;
            font-size: 11px;
            font-weight: 700;
        }
        .btn-lang-fixed:hover {
            background: #f0c94a;
            color: #1a1200;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(240,201,74,0.6);
        }
        .btn-back-fixed {
            background: rgba(240, 201, 74, 0.15);
            border: 2px solid #f0c94a;
            color: #f0c94a;
        }
        .btn-back-fixed:hover {
            background: #f0c94a;
            color: #1a1200;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(240,201,74,0.6);
        }
        .btn-logout-fixed {
            background: rgba(220, 53, 69, 0.2);
            border: 2px solid #dc3545;
            color: #ff9aa4;
        }
        .btn-logout-fixed:hover {
            background: #dc3545;
            color: #fff;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(220,53,69,0.6);
        }

        /* ===== Panel admin con dos sidebars ===== */
        .admin-layout {
            display: flex;
            gap: 20px;
            max-width: 1500px;
            margin: 20px auto;
            padding: 0 20px;
            align-items: flex-start;
        }
        .admin-content {
            flex: 1;
            min-width: 0;
        }
        .admin-nav-derecha {
            flex: 0 0 220px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            position: sticky;
            top: 80px;
            align-self: flex-start;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            padding: 10px;
            background: rgba(8, 18, 40, 0.85);
            border: 1px solid rgba(240, 201, 74, 0.3);
            border-radius: 12px;
            order: 2;
        }
        .admin-nav-derecha .titulo-nav {
            color: #f0c94a;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            padding-bottom: 10px;
            margin-bottom: 5px;
            border-bottom: 1px solid rgba(212,175,55,0.2);
        }
        .admin-nav-derecha a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            color: #c9d8f0;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }
        .admin-nav-derecha a:hover {
            background: rgba(240, 201, 74, 0.1);
            border-color: rgba(240, 201, 74, 0.3);
            color: #f0c94a;
        }
        .admin-nav-derecha a.active {
            background: linear-gradient(135deg, rgba(240,201,74,0.25), rgba(212,175,55,0.15));
            border-color: #f0c94a;
            color: #f0c94a;
        }
        .admin-nav-derecha a i {
            color: #f0c94a;
            font-size: 14px;
            width: 18px;
            text-align: center;
        }
        .admin-nav-derecha a.active i {
            filter: drop-shadow(0 0 6px #f0c94a);
        }

        @media (max-width: 900px) {
            .admin-layout { flex-direction: column; }
            .admin-nav-derecha {
                flex: 0 0 auto;
                position: static;
                flex-direction: row;
                overflow-x: auto;
                overflow-y: hidden;
                max-height: none;
                white-space: nowrap;
                order: 0;
                width: 100%;
            }
            .admin-nav-derecha .titulo-nav { display: none; }
            .admin-nav-derecha a { flex: 0 0 auto; }
        }
    </style>
</head>
<body>

<div class="user-fixed-box">
    <span class="user-fixed-name">
        <i class="fa-solid fa-user" style="color:#9db4d8;"></i>
        <?= htmlspecialchars($web_user) ?>
        <span class="badge-gm">GM</span>
    </span>
    <a href="?tab=<?= $tab ?>&lang=es" class="btn-lang-fixed" title="Español" style="<?= $idioma === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
    <a href="?tab=<?= $tab ?>&lang=en" class="btn-lang-fixed" title="English" style="<?= $idioma === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
    <a href="../index.php" class="btn-back-fixed" title="<?= t('back') ?>"><i class="fa-solid fa-arrow-left"></i></a>
    <a href="../index.php?logout=1" class="btn-logout-fixed" title="<?= t('logout') ?>"><i class="fa-solid fa-right-from-bracket"></i></a>
</div>

<!-- ============================================
     SIDEBAR VERTICAL IZQUIERDO (navegación web)
     ============================================ -->
<div class="topbar">
    <nav class="nav nav-vertical">
        <div class="brand-group">
            <a href="../index.php" class="brand">👑 <?= t('gm_panel') ?></a>
        </div>
        <div class="menu menu-vertical">
            <a href="../index.php" class="btn-nav"><span class="ico">🏠</span> <?= t('home') ?></a>
            <a href="../cuenta.php" class="btn-nav"><span class="ico">👤</span> <?= t('my_account') ?></a>
            <a href="../tienda.php" class="btn-nav"><span class="ico">🛒</span> <?= t('shop') ?></a>
            <a href="../noticias.php" class="btn-nav"><span class="ico">📰</span> <?= t('news_titulo') ?></a>
            <a href="../guias.php" class="btn-nav"><span class="ico">📖</span> <?= t('guias_titulo') ?></a>
            <a href="../reglas.php" class="btn-nav"><span class="ico">📜</span> <?= t('rules') ?></a>
            <a href="../sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
            <a href="../ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
        </div>
    </nav>
</div>

<!-- ============================================
     LAYOUT DEL ADMIN: contenido + nav derecha
     ============================================ -->
<div class="admin-layout">

    <!-- Contenido del panel (izquierda/centro) -->
    <div class="admin-content">
        <div class="caja caja-logueado">

            <?php if ($tab === 'items'): ?>
                <?php require __DIR__ . '/items.php'; ?>
            <?php elseif ($tab === 'noticias'): ?>
                <?php require __DIR__ . '/noticias.php'; ?>
            <?php elseif ($tab === 'usuarios'): ?>
                <?php require __DIR__ . '/usuarios.php'; ?>
            <?php elseif ($tab === 'reglas'): ?>
                <?php require __DIR__ . '/reglas.php'; ?>
            <?php elseif ($tab === 'codigos'): ?>
                <?php require __DIR__ . '/codigos.php'; ?>
            <?php elseif ($tab === 'rifas'): ?>
                <?php require __DIR__ . '/rifas.php'; ?>
            <?php endif; ?>

        </div>
    </div>

    <!-- Nav de modificar (derecha) -->
    <nav class="admin-nav-derecha">
        <div class="titulo-nav">⚙️ Modificar</div>
        <a href="?tab=items&lang=<?= $idioma ?>" class="<?= $tab === 'items' ? 'active' : '' ?>">
            <i class="fa-solid fa-cart-shopping"></i> <?= t('manage_items') ?>
        </a>
        <a href="?tab=noticias&lang=<?= $idioma ?>" class="<?= $tab === 'noticias' ? 'active' : '' ?>">
            <i class="fa-solid fa-newspaper"></i> <?= t('create_news') ?>
        </a>
        <a href="?tab=usuarios&lang=<?= $idioma ?>" class="<?= $tab === 'usuarios' ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i> <?= t('manage_users') ?>
        </a>
        <a href="?tab=reglas&lang=<?= $idioma ?>" class="<?= $tab === 'reglas' ? 'active' : '' ?>">
            <i class="fa-solid fa-scroll"></i> <?= t('rules') ?>
        </a>
        <a href="?tab=codigos&lang=<?= $idioma ?>" class="<?= $tab === 'codigos' ? 'active' : '' ?>">
            <i class="fa-solid fa-gift"></i> <?= t('gift_codes') ?>
        </a>
        <a href="?tab=rifas&lang=<?= $idioma ?>" class="<?= $tab === 'rifas' ? 'active' : '' ?>">
            <i class="fa-solid fa-ticket"></i> <?= t('raffle') ?>
        </a>
    </nav>

</div>

</body>
</html>