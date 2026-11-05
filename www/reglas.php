<?php
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/config.php';

// Elegir archivo de reglas según el idioma
$reglas_file = ($idioma === 'en') ? __DIR__ . '/reglas_en.json' : __DIR__ . '/reglas_es.json';

$reglas = [];
if (file_exists($reglas_file)) {
    $json = file_get_contents($reglas_file);
    $datos = json_decode($json, true);
    if (is_array($datos)) $reglas = $datos;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= t('rules_title') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/noticias.css">
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

        .regla-item {
            background: rgba(0,10,30,0.6);
            border: 1.5px solid rgba(212,175,55,0.4);
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 15px;
        }
        .regla-item h4 {
            color: #f0c94a;
            font-size: 16px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .regla-item p {
            color: #c9d8f0;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            white-space: pre-line;
        }
    </style>
</head>
<body>

<?php if (!empty($_SESSION['web_logueado'])): ?>
    <div class="user-fixed-box">
        <span class="user-fixed-name">
            <i class="fa-solid fa-user" style="color:#9db4d8;"></i>
            <?= htmlspecialchars($_SESSION['web_username'] ?? '') ?>
            <?php if (!empty($_SESSION['es_admin'])): ?><span class="badge-gm">GM</span><?php endif; ?>
        </span>
        <a href="?lang=es" class="btn-lang-fixed" title="Español" style="<?= $idioma === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
        <a href="?lang=en" class="btn-lang-fixed" title="English" style="<?= $idioma === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
        <a href="index.php" class="btn-back-fixed" title="<?= t('back') ?>"><i class="fa-solid fa-arrow-left"></i></a>
        <a href="index.php?logout=1" class="btn-logout-fixed" title="<?= t('logout') ?>"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
<?php else: ?>
    <div class="user-fixed-box">
        <a href="?lang=es" class="btn-lang-fixed" title="Español" style="<?= $idioma === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
        <a href="?lang=en" class="btn-lang-fixed" title="English" style="<?= $idioma === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
        <a href="index.php" class="btn-back-fixed" title="<?= t('back') ?>"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
<?php endif; ?>

<!-- SIDEBAR VERTICAL -->
<div class="topbar">
    <nav class="nav nav-vertical">
        <div class="brand-group">
            <a href="index.php" class="brand"><?= htmlspecialchars(SERVER_NAME) ?></a>
        </div>
        <div class="menu menu-vertical">
            <?php if (!empty($_SESSION['web_logueado'])): ?>
                <?php if (!empty($_SESSION['es_admin'])): ?>
                    <a href="admin/index.php" class="btn-nav btn-gm-priority"><span class="ico">👑</span> <?= t('gm_panel') ?></a>
                <?php endif; ?>
                <a href="cuenta.php" class="btn-nav"><span class="ico">👤</span> <?= t('my_account') ?></a>
                <a href="tienda.php" class="btn-nav"><span class="ico">🛒</span> <?= t('shop') ?></a>
                <a href="noticias.php" class="btn-nav"><span class="ico">📰</span> <?= t('news_titulo') ?></a>
                <a href="guias.php" class="btn-nav"><span class="ico">📖</span> <?= t('guias_titulo') ?></a>
                <a href="reglas.php" class="btn-nav primary"><span class="ico">📜</span> <?= t('rules') ?></a>
                <a href="sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
            <?php else: ?>
                <a href="reglas.php" class="btn-nav primary"><span class="ico">📜</span> <?= t('rules') ?></a>
                <a href="sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<div class="contenedor">
    <div class="caja caja-logueado">
        <h2 style="color:#f0c94a; text-align:center; margin-bottom:10px;">📖 <?= t('rules_title') ?></h2>
        <p style="color:#9db4d8; text-align:center; font-size:13px; margin-bottom:30px;"><?= t('rules_subtitle') ?></p>

        <?php if (empty($reglas)): ?>
            <div class="vacio">
                <span class="icono">📖</span>
                <?= t('rules_empty') ?>
            </div>
        <?php else: ?>
            <?php foreach ($reglas as $r): ?>
                <div class="regla-item">
                    <h4>
                        <i class="fa-solid fa-gavel" style="color:#d4af37;"></i>
                        <?= htmlspecialchars($r['titulo'] ?? t('news_sin_titulo')) ?>
                    </h4>
                    <p><?= nl2br(htmlspecialchars($r['contenido'] ?? '')) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>