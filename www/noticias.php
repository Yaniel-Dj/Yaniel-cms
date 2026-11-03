<?php
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/config.php';

// Elegir archivo de noticias según el idioma
$noticias_file = ($idioma === 'en') ? __DIR__ . '/noticias_en.json' : __DIR__ . '/noticias_es.json';

$noticias = [];
$error_json = false;
$error_msg = '';

if (!file_exists($noticias_file)) {
    $error_json = true;
    $error_msg = 'No se encuentra el archivo: ' . $noticias_file;
} else {
    $json = file_get_contents($noticias_file);
    if ($json === false) {
        $error_json = true;
        $error_msg = 'No se puede leer el archivo.';
    } else {
        $datos = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_json = true;
            $error_msg = 'JSON inválido: ' . json_last_error_msg();
        } elseif (!is_array($datos)) {
            $error_json = true;
            $error_msg = 'El JSON no es un array válido.';
        } else {
            $noticias = $datos;
            usort($noticias, function($a, $b) {
                return strtotime($b['fecha'] ?? '1970-01-01') - strtotime($a['fecha'] ?? '1970-01-01');
            });
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= t('news_titulo') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/forms.css">
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
        .nav-botones {
            display: flex; gap: 12px; justify-content: center;
            margin-bottom: 35px; flex-wrap: wrap;
        }
        .nav-botones .btn-nav {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 18px; font-size: 13px;
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
<?php endif; ?>

<div class="contenedor" style="padding-top: 80px;">
    <div class="caja caja-logueado">
        <div class="cabecera" style="text-align:center; margin-bottom:30px;">
            <div class="logo-name" style="color:#f0c94a; font-size:32px; font-weight:800; text-shadow:0 0 18px rgba(240,201,74,0.55);">
                <?= htmlspecialchars(SERVER_NAME) ?>
            </div>
            <p class="subtitulo"><?= t('news_subtitulo') ?></p>
        </div>

        <div class="nav-botones">
            <a href="noticias.php" class="btn-nav"><i class="fa-solid fa-rotate-right"></i> <?= t('news_recargar') ?></a>
        </div>

        <div class="noticias-lista">
            <?php if ($error_json): ?>
                <div class="alerta error">
                    <strong>⚠ <?= t('news_error') ?></strong><br>
                    <?= htmlspecialchars($error_msg) ?><br>
                    <code><?= htmlspecialchars($noticias_file) ?></code>
                </div>
            <?php elseif (empty($noticias)): ?>
                <div class="vacio">
                    <span class="icono">📭</span>
                    <?= t('news_vacio') ?><br>
                    <?= t('news_vacio_add') ?> <code>noticias_es.json</code> / <code>noticias_en.json</code>.
                </div>
            <?php else: ?>
                <?php foreach ($noticias as $n): ?>
                    <article class="noticia">
                        <h2 class="noticia-titulo"><?= htmlspecialchars($n['titulo'] ?? t('news_sin_titulo')) ?></h2>
                        <div class="noticia-meta">
                            <?php if (!empty($n['fecha'])): ?>
                                <span class="meta-fecha"><?= htmlspecialchars(fechaBonita($n['fecha'])) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($n['autor'])): ?>
                                <span class="meta-autor"><?= htmlspecialchars($n['autor']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="noticia-contenido"><?= htmlspecialchars($n['contenido'] ?? '') ?></div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>