<?php
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/config.php';

// Página pública: no requiere login
$mensaje = '';
$tipo = '';

$sanciones = [];
$total_sanciones = 0;

$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$por_pagina = 20;
$offset = ($pagina - 1) * $por_pagina;

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $check = $pdo->query("SHOW TABLES LIKE 'account_banned'");
    $usa_account_banned = ($check->rowCount() > 0);

    if ($usa_account_banned) {
        $stmt_count = $pdo->query("
            SELECT COUNT(*) FROM account_banned ab
            WHERE ab.active = 1
              AND (ab.unbandate = 0 OR ab.unbandate > UNIX_TIMESTAMP())
        ");
        $total_sanciones = (int)$stmt_count->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT ab.id, a.username, ab.bandate, ab.unbandate, ab.bannedby, ab.banreason
            FROM account_banned ab
            LEFT JOIN account a ON a.id = ab.id
            WHERE ab.active = 1
              AND (ab.unbandate = 0 OR ab.unbandate > UNIX_TIMESTAMP())
            ORDER BY ab.bandate DESC
            LIMIT $por_pagina OFFSET $offset
        ");
        $stmt->execute();
        $sanciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $mensaje = t('db_error'); $tipo = 'error';
}

$total_paginas = (int)ceil($total_sanciones / $por_pagina);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= t('sanctions_title') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
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

        .sancion-card {
            background: rgba(0,10,30,0.6);
            border: 1.5px solid rgba(220,53,69,0.4);
            border-radius: 12px;
            padding: 18px 22px;
            margin-bottom: 15px;
        }
        .sancion-cuenta {
            color: #ff5c5c;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sancion-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 12px;
            color: #9db4d8;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(212,175,55,0.15);
        }
        .sancion-meta span::before { margin-right: 5px; color: #d4af37; }
        .meta-fecha::before { content: '📅'; }
        .meta-gm::before { content: '👤'; }
        .meta-duracion::before { content: '⏱️'; }
        .sancion-motivo {
            color: #c9d8f0;
            font-size: 13px;
            line-height: 1.6;
            font-style: italic;
        }
        .vacio-sanciones {
            text-align: center;
            padding: 50px 20px;
            color: #5ac86b;
            font-size: 15px;
        }
        .vacio-sanciones .icono {
            font-size: 50px;
            display: block;
            margin-bottom: 15px;
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
                <a href="reglas.php" class="btn-nav"><span class="ico">📜</span> <?= t('rules') ?></a>
                <a href="sanciones.php" class="btn-nav primary"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
            <?php else: ?>
                <a href="reglas.php" class="btn-nav"><span class="ico">📜</span> <?= t('rules') ?></a>
                <a href="sanciones.php" class="btn-nav primary"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<div class="contenedor">
    <div class="caja caja-logueado">
        <div style="text-align:center; margin-bottom:30px;">
            <h2 style="color:#ff5c5c; font-size:26px; margin-bottom:10px;">
                🚫 <?= t('sanctions_title') ?>
            </h2>
            <p style="color:#9db4d8; font-size:13px;">
                <?= t('sanctions_subtitle') ?>
            </p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if (empty($sanciones)): ?>
            <div class="vacio-sanciones">
                <span class="icono">✅</span>
                <?= t('sanctions_empty') ?>
            </div>
        <?php else: ?>
            <?php foreach ($sanciones as $s): ?>
                <div class="sancion-card">
                    <div class="sancion-cuenta">
                        <i class="fa-solid fa-user-slash"></i>
                        <?= htmlspecialchars($s['username'] ?? 'Cuenta #' . $s['id']) ?>
                    </div>
                    <div class="sancion-meta">
                        <span class="meta-fecha">
                            <?= $s['bandate'] ? date('d/m/Y H:i', $s['bandate']) : '?' ?>
                        </span>
                        <?php if (!empty($s['bannedby'])): ?>
                            <span class="meta-gm"><?= htmlspecialchars($s['bannedby']) ?></span>
                        <?php endif; ?>
                        <span class="meta-duracion">
                            <?php if ((int)$s['unbandate'] === 0): ?>
                                <?= t('sanction_permanent') ?>
                            <?php else: ?>
                                <?= date('d/m/Y H:i', (int)$s['unbandate']) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if (!empty($s['banreason'])): ?>
                        <div class="sancion-motivo">
                            "<?= htmlspecialchars($s['banreason']) ?>"
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if ($total_paginas > 1): ?>
                <div style="display:flex; justify-content:center; gap:5px; margin-top:25px; flex-wrap:wrap;">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?= $pagina - 1 ?>" class="btn-borrar" style="text-decoration:none; padding:6px 12px;">
                            <?= t('previous') ?>
                        </a>
                    <?php endif; ?>

                    <span style="padding:6px 12px; color:#f0c94a;">
                        <?= t('page') ?> <?= $pagina ?> <?= t('of') ?> <?= $total_paginas ?>
                    </span>

                    <?php if ($pagina < $total_paginas): ?>
                        <a href="?pagina=<?= $pagina + 1 ?>" class="btn-borrar" style="text-decoration:none; padding:6px 12px;">
                            <?= t('next') ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

</body>
</html>