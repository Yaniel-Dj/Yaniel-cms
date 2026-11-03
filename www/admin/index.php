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
        /* Caja fija: nombre de usuario + idiomas + atrás + cerrar sesión */
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

<div class="contenedor" style="padding-top: 80px;">
    <div class="caja caja-logueado">

        <div class="tabs">
            <a href="?tab=items&lang=<?= $idioma ?>" class="tab <?= $tab === 'items' ? 'activo' : '' ?>">🛒 <?= t('manage_items') ?></a>
            <a href="?tab=noticias&lang=<?= $idioma ?>" class="tab <?= $tab === 'noticias' ? 'activo' : '' ?>">📰 <?= t('create_news') ?></a>
            <a href="?tab=usuarios&lang=<?= $idioma ?>" class="tab <?= $tab === 'usuarios' ? 'activo' : '' ?>">👥 <?= t('manage_users') ?></a>
            <a href="?tab=reglas&lang=<?= $idioma ?>" class="tab <?= $tab === 'reglas' ? 'activo' : '' ?>">📖 <?= t('rules') ?></a>
            <a href="?tab=codigos&lang=<?= $idioma ?>" class="tab <?= $tab === 'codigos' ? 'activo' : '' ?>">🎁 <?= t('gift_codes') ?></a>
            <a href="?tab=rifas&lang=<?= $idioma ?>" class="tab <?= $tab === 'rifas' ? 'activo' : '' ?>">🎟️ <?= t('raffle') ?></a>
        </div>

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

</body>
</html>