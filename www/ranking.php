<?php
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/config.php';

$web_user = $_SESSION['web_username'] ?? '';

// Pestaña activa
$tipo = $_GET['tipo'] ?? 'nivel';
if (!in_array($tipo, ['nivel', 'oro', 'tiempo', 'pvp'], true)) $tipo = 'nivel';

// Configuración de cada ranking
$rankings = [
    'nivel' => [
        'titulo' => t('ranking_tab_nivel'),
        'icono'  => 'fa-star',
        'campo'  => 'level',
        'formato' => function($v) { return $v; },
    ],
    'oro' => [
        'titulo' => t('ranking_tab_oro'),
        'icono'  => 'fa-coins',
        'campo'  => 'money',
        'formato' => function($v) { return number_format(floor($v / COBRE_POR_ORO)) . ' ' . t('gold'); },
    ],
    'tiempo' => [
        'titulo' => t('ranking_tab_tiempo'),
        'icono'  => 'fa-clock',
        'campo'  => 'totaltime',
        'formato' => function($v) { return floor($v / 3600) . ' h'; },
    ],
    'pvp' => [
        'titulo' => t('ranking_tab_pvp'),
        'icono'  => 'fa-sword',
        'campo'  => 'totalKills',
        'formato' => function($v) { return number_format($v); },
    ],
];

$config = $rankings[$tipo];

// Mapeo de razas y clases
$razas = [1=>'Humano',2=>'Orco',3=>'Enano',4=>'Elfo de la Noche',5=>'No-Muerto',6=>'Tauren',7=>'Gnomo',8=>'Trol',10=>'Elfo de Sangre',11=>'Draenei'];
$clases = [1=>'Guerrero',2=>'Paladín',3=>'Cazador',4=>'Pícaro',5=>'Sacerdote',6=>'Caballero de la Muerte',7=>'Chamán',8=>'Mago',9=>'Brujo',11=>'Druida'];

$colores_clase = [
    1 => '#C79C6E',
    2 => '#F58CBA',
    3 => '#ABD473',
    4 => '#FFF569',
    5 => '#FFFFFF',
    6 => '#C41F3B',
    7 => '#0070DE',
    8 => '#69CCF0',
    9 => '#9482C9',
    11 => '#FF7D0A',
];

// Cargar top
$top = [];
try {
    $pdo_char = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $campo = $config['campo'];
    $stmt = $pdo_char->query("SELECT guid, name, level, race, class, money, totaltime, totalKills, online FROM characters WHERE level >= 1 ORDER BY $campo DESC LIMIT 50");
    $top = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = t('db_error') . ': ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= t('ranking_title') ?></title>
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

        .ranking-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .ranking-tab {
            padding: 10px 18px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(212,175,55,0.3);
            border-radius: 8px;
            color: #9db4d8;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .ranking-tab:hover { background: rgba(212,175,55,0.1); color: #f0c94a; }
        .ranking-tab.active { background: rgba(212,175,55,0.2); color: #f0c94a; border-color: #f0c94a; }

        .ranking-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .ranking-tabla thead th {
            background: rgba(212,175,55,0.15);
            color: #f0c94a;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid rgba(212,175,55,0.4);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .ranking-tabla tbody td {
            padding: 10px;
            color: #c9d8f0;
            border-bottom: 1px solid rgba(212,175,55,0.1);
        }
        .ranking-tabla tbody tr:hover td {
            background: rgba(212,175,55,0.05);
        }
        .ranking-tabla .pos {
            font-weight: 800;
            font-size: 14px;
            width: 50px;
        }
        .pos-1 { color: #ffd700; }
        .pos-2 { color: #c0c0c0; }
        .pos-3 { color: #cd7f32; }
        .pos-otro { color: #7a8590; }
        .ranking-nombre { font-weight: 700; }
        .ranking-valor { color: #f0c94a; font-weight: 700; text-align: right; }
        .online-dot { color: #5ac86b; font-size: 10px; margin-left: 5px; }
        .offline-dot { color: #7a8590; font-size: 10px; margin-left: 5px; }
    </style>
</head>
<body>

<?php if (!empty($_SESSION['web_logueado'])): ?>
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
                <a href="sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav primary"><span class="ico">🏆</span> <?= t('ranking_title') ?></a>
            <?php else: ?>
                <a href="reglas.php" class="btn-nav"><span class="ico">📜</span> <?= t('rules') ?></a>
                <a href="sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav primary"><span class="ico">🏆</span> <?= t('ranking_title') ?></a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<div class="contenedor">
    <div class="caja caja-logueado">
        <h2 style="color:#f0c94a; text-align:center; margin-bottom:10px;">🏆 <?= t('ranking_title') ?></h2>
        <p style="color:#9db4d8; text-align:center; font-size:13px; margin-bottom:25px;"><?= t('ranking_subtitle') ?></p>

        <!-- Pestañas -->
        <div class="ranking-tabs">
            <?php foreach ($rankings as $key => $r): ?>
                <a href="?tipo=<?= $key ?>" class="ranking-tab <?= $tipo === $key ? 'active' : '' ?>">
                    <i class="fa-solid <?= $r['icono'] ?>"></i> <?= $r['titulo'] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Tabla -->
        <?php if (empty($top)): ?>
            <div class="vacio"><span class="icono">🏆</span><?= t('ranking_vacio') ?></div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="ranking-tabla">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= t('ranking_col_personaje') ?></th>
                            <th><?= t('ranking_col_nivel') ?></th>
                            <th><?= t('ranking_col_clase') ?></th>
                            <th style="text-align:right;"><?= $config['titulo'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $pos = 1; foreach ($top as $p): ?>
                            <?php
                                $color = $colores_clase[$p['class']] ?? '#c9d8f0';
                                $pos_class = $pos === 1 ? 'pos-1' : ($pos === 2 ? 'pos-2' : ($pos === 3 ? 'pos-3' : 'pos-otro'));
                                $valor = $config['formato']($p[$config['campo']]);
                            ?>
                            <tr>
                                <td class="pos <?= $pos_class ?>">
                                    <?php if ($pos <= 3): ?>
                                        <i class="fa-solid fa-crown"></i>
                                    <?php endif; ?>
                                    <?= $pos ?>
                                </td>
                                <td>
                                    <span class="ranking-nombre" style="color:<?= $color ?>;">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </span>
                                    <?php if ($p['online']): ?>
                                        <span class="online-dot">●</span>
                                    <?php else: ?>
                                        <span class="offline-dot">●</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $p['level'] ?></td>
                                <td style="color:<?= $color ?>;"><?= $clases[$p['class']] ?? '?' ?></td>
                                <td class="ranking-valor"><?= $valor ?></td>
                            </tr>
                        <?php $pos++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>