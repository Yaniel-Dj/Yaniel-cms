<?php
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/config.php';

$mensaje = '';
$tipo = '';

$logueado = !empty($_SESSION['web_logueado']);
$es_admin = !empty($_SESSION['es_admin']);
$web_user = $_SESSION['web_username'] ?? '';
$web_id   = $_SESSION['web_user_id'] ?? 0;

// ============================================
// LOGOUT
// ============================================
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// ============================================
// VERIFICAR SRP6
// ============================================
function verifySRP6Password($username, $password, $salt_binario, $verifier_esperado) {
    $g = gmp_init(7);
    $N = gmp_init('894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7', 16);

    $h1 = sha1(strtoupper($username) . ':' . strtoupper($password), true);
    $h2 = sha1($salt_binario . $h1, true);
    $h2_int = gmp_import($h2, 1, GMP_LSW_FIRST);
    $verifier_calc = gmp_powm($g, $h2_int, $N);
    $verifier_bin = gmp_export($verifier_calc, 1, GMP_LSW_FIRST);
    $verifier_bin = str_pad($verifier_bin, 32, chr(0), STR_PAD_RIGHT);

    return hash_equals($verifier_esperado, $verifier_bin);
}

// ============================================
// LOGIN WEB
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_web'])) {
    $user = trim($_POST['login_user'] ?? '');
    $pass = $_POST['login_pass'] ?? '';
    if (empty($user) || empty($pass)) {
        $mensaje = t('login_empty'); $tipo = 'error';
    } else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->prepare("SELECT id, salt, verifier FROM account WHERE username = ?");
            $stmt->execute([strtoupper($user)]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cuenta && verifySRP6Password($user, $pass, $cuenta['salt'], $cuenta['verifier'])) {
                $_SESSION['web_user_id']  = $cuenta['id'];
                $_SESSION['web_username'] = strtoupper($user);
                $_SESSION['web_logueado'] = true;
                $stmt_gm = $pdo->prepare("SELECT gmlevel FROM account_access WHERE id = ? AND RealmID IN (-1, 1) LIMIT 1");
                $stmt_gm->execute([$cuenta['id']]);
                $gm = $stmt_gm->fetch(PDO::FETCH_ASSOC);
                $_SESSION['es_admin'] = ($gm && (int)$gm['gmlevel'] >= 3);
                header('Location: index.php');
                exit;
            } else {
                $mensaje = t('login_invalid'); $tipo = 'error';
            }
        } catch (PDOException $e) {
            $mensaje = t('db_error'); $tipo = 'error';
        }
    }
}

// ============================================
// SRP6 REGISTRO
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
// ESTADO SERVIDOR
// ============================================
$conexion = @fsockopen(SERVER_IP, SERVER_PORT, $errno, $errstr, SERVER_TIMEOUT);
if ($conexion) {
    $server_online = true; $estado_clase = 'online'; $estado_texto = t('online');
    fclose($conexion);
} else {
    $server_online = false; $estado_clase = 'offline'; $estado_texto = t('offline');
}

// ============================================
// FACCIONES
// ============================================
function getFactionCounts() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query("SELECT COUNT(*) FROM characters WHERE online = 1 AND race IN (1, 3, 4, 7, 11)");
        $alianza = (int)$stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM characters WHERE online = 1 AND race IN (2, 5, 6, 8, 10)");
        $horda = (int)$stmt->fetchColumn();
        return ['alianza' => $alianza, 'horda' => $horda];
    } catch (PDOException $e) { return ['alianza' => 0, 'horda' => 0]; }
}
$facciones = getFactionCounts();

// ============================================
// UPTIME
// ============================================
function getServerUptimeSeconds() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query("SELECT starttime FROM uptime ORDER BY starttime DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && (int)$row['starttime'] > 0) {
            $diff = time() - (int)$row['starttime'];
            return $diff > 0 ? $diff : 0;
        }
        return 0;
    } catch (PDOException $e) { return 0; }
}
function formatUptime($seconds) {
    if ($seconds <= 0) return '—';
    $d = floor($seconds / 86400); $h = floor(($seconds % 86400) / 3600); $m = floor(($seconds % 3600) / 60); $s = $seconds % 60;
    if ($d > 0) return "{$d}d {$h}h {$m}m";
    if ($h > 0) return "{$h}h {$m}m";
    if ($m > 0) return "{$m}m {$s}s";
    return "{$s}s";
}
$uptime_segundos = $server_online ? getServerUptimeSeconds() : 0;
$tiempo_online   = $server_online ? formatUptime($uptime_segundos) : null;

// ============================================
// REGISTRO
// ============================================
if (!$logueado && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'])) {
    $usuario   = trim($_POST['usuario'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (empty($usuario) || empty($password)) { $mensaje = t('login_empty'); $tipo = 'error'; }
    elseif (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) { $mensaje = t('register_invalid_chars'); $tipo = 'error'; }
    elseif (strlen($usuario) < MIN_USERNAME_LENGTH || strlen($usuario) > MAX_USERNAME_LENGTH) { $mensaje = t('register_username_len') . ' ' . MIN_USERNAME_LENGTH . ' ' . t('and') . ' ' . MAX_USERNAME_LENGTH; $tipo = 'error'; }
    elseif (strlen($password) < MIN_PASSWORD_LENGTH || strlen($password) > MAX_PASSWORD_LENGTH) { $mensaje = t('register_password_len') . ' ' . MIN_PASSWORD_LENGTH . ' ' . t('and') . ' ' . MAX_PASSWORD_LENGTH; $tipo = 'error'; }
    elseif ($password !== $password2) { $mensaje = t('register_pass_mismatch'); $tipo = 'error'; }
    else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->prepare("SELECT id FROM account WHERE username = ?");
            $stmt->execute([strtoupper($usuario)]);
            if ($stmt->fetch()) { $mensaje = t('register_taken'); $tipo = 'error'; }
            else {
                $srp = calculateSRP6Verifier($usuario, $password);
                $stmt = $pdo->prepare("INSERT INTO account (username, salt, verifier, email, reg_mail, joindate, last_ip, expansion) VALUES (?, ?, ?, '', '', NOW(), ?, ?)");
                $stmt->execute([strtoupper($usuario), $srp['salt'], $srp['verifier'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', EXPANSION]);
                $mensaje = t('register_success'); $tipo = 'exito';
            }
        } catch (PDOException $e) { $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error'; }
    }
}

// ============================================
// CARGAR DATOS PARA DASHBOARD
// ============================================
$noticias_mostrar = [];
$personajes = [];

if ($logueado) {
    $noticias_file = ($idioma === 'en') ? __DIR__ . '/noticias_en.json' : __DIR__ . '/noticias_es.json';

    if (file_exists($noticias_file)) {
        $json = file_get_contents($noticias_file);
        $datos = json_decode($json, true);
        if (is_array($datos)) {
            $noticias_mostrar = $datos;
            usort($noticias_mostrar, function($a, $b) {
                return strtotime($b['fecha'] ?? '1970-01-01') - strtotime($a['fecha'] ?? '1970-01-01');
            });
        }
    }
    try {
        $pdo_char = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . CHAR_DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt_char = $pdo_char->prepare("SELECT guid, name, level, money FROM characters WHERE account = ? ORDER BY level DESC");
        $stmt_char->execute([$web_id]);
        $personajes = $stmt_char->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - <?= $logueado ? t('welcome') : t('register_btn') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/noticias.css">
    <link rel="stylesheet" href="css/tienda.css">
    <style>
        .topbar .btn-nav.btn-gm-priority {
            background: linear-gradient(135deg, rgba(240,201,74,0.25), rgba(212,175,55,0.15)) !important;
            border: 2px solid #f0c94a !important;
            color: #f0c94a !important;
            font-weight: 800 !important;
            text-shadow: 0 0 8px rgba(240,201,74,0.6) !important;
            box-shadow: 0 0 15px rgba(240,201,74,0.25) !important;
            animation: pulso-gm 2s infinite alternate;
        }
        .topbar .btn-nav.btn-gm-priority:hover {
            background: linear-gradient(135deg, #f0c94a, #d4af37) !important;
            color: #1a1200 !important;
            text-shadow: none !important;
            box-shadow: 0 0 25px rgba(240,201,74,0.6) !important;
            transform: translateY(-2px);
        }
        @keyframes pulso-gm {
            0% { box-shadow: 0 0 10px rgba(240,201,74,0.2); }
            100% { box-shadow: 0 0 20px rgba(240,201,74,0.5); }
        }

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
        .btn-lang-fixed, .btn-logout-fixed {
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

<?php if ($logueado): ?>
    <div class="user-fixed-box">
        <span class="user-fixed-name">
            <i class="fa-solid fa-user" style="color:#9db4d8;"></i>
            <?= htmlspecialchars($web_user) ?>
            <?php if ($es_admin): ?><span class="badge-gm">GM</span><?php endif; ?>
        </span>
        <a href="?lang=es" class="btn-lang-fixed" title="Español" style="<?= $idioma === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
        <a href="?lang=en" class="btn-lang-fixed" title="English" style="<?= $idioma === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
        <a href="index.php?logout=1" class="btn-logout-fixed" title="<?= t('logout') ?>">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>
    </div>
<?php else: ?>
    <div class="user-fixed-box">
        <a href="?lang=es" class="btn-lang-fixed" title="Español" style="<?= $idioma === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
        <a href="?lang=en" class="btn-lang-fixed" title="English" style="<?= $idioma === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
    </div>
<?php endif; ?>

<!-- ============================================
     SIDEBAR VERTICAL
     ============================================ -->
<div class="topbar">
    <nav class="nav nav-vertical">
        <div class="brand-group">
            <a href="index.php" class="brand"><?= htmlspecialchars(SERVER_NAME) ?></a>
        </div>
        <div class="menu menu-vertical">
            <?php if ($logueado): ?>
                <?php if ($es_admin): ?>
                    <a href="admin/index.php" class="btn-nav btn-gm-priority"><span class="ico">👑</span> <?= t('gm_panel') ?></a>
                <?php endif; ?>
                <a href="cuenta.php" class="btn-nav"><span class="ico">👤</span> <?= t('my_account') ?></a>
                <a href="tienda.php" class="btn-nav"><span class="ico">🛒</span> <?= t('shop') ?></a>
                <a href="noticias.php" class="btn-nav"><span class="ico">📰</span> <?= t('news_titulo') ?></a>
                <a href="guias.php" class="btn-nav"><span class="ico">📖</span> <?= t('guias_titulo') ?></a>
                <a href="reglas.php" class="btn-nav"><span class="ico">📜</span> <?= t('rules') ?></a>
                <a href="sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
            <?php else: ?>
                <a href="reglas.php" class="btn-nav"><span class="ico">📜</span> <?= t('rules') ?></a>
                <a href="sanciones.php" class="btn-nav"><span class="ico">🚫</span> <?= t('sanctions_title') ?></a>
                <a href="ranking.php" class="btn-nav"><span class="ico">🏆</span> Ranking</a>
                <a href="#" class="btn-nav outline" onclick="cambiarTab('login'); return false;">→ <?= t('login_tab') ?></a>
                <a href="#" class="btn-nav primary" onclick="cambiarTab('registro'); return false;">+ <?= t('register_tab') ?></a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<!-- ============================================
     CONTENIDO
     ============================================ -->
<div class="contenedor">
    <div class="caja <?= $logueado ? 'caja-logueado' : '' ?>">

        <?php if ($logueado): ?>
            <div class="bienvenida">
                <?= t('welcome') ?>, <strong><?= htmlspecialchars($web_user) ?></strong>
            </div>

            <?php if (!empty($personajes)): ?>
                <div class="oro-badge">
                    <div class="oro-titulo">💰 <?= t('your_gold') ?></div>
                    <ul class="oro-lista">
                        <?php foreach ($personajes as $pj): ?>
                            <li>
                                <span class="oro-pj"><?= htmlspecialchars($pj['name']) ?></span>
                                <span class="oro-cantidad"><?= number_format(floor($pj['money'] / COBRE_POR_ORO)) ?> <?= t('gold') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($mensaje): ?>
                <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <div id="seccion-noticias">
                <h3 style="color:#f0c94a; font-size:16px; margin-bottom:15px; text-align:center;"><?= t('news_titulo') ?></h3>
                <?php if (empty($noticias_mostrar)): ?>
                    <div class="vacio"><span class="icono">📭</span><?= t('no_news') ?></div>
                <?php else: ?>
                    <div class="noticias-lista">
                        <?php foreach (array_slice($noticias_mostrar, 0, 3) as $n): ?>
                            <article class="noticia">
                                <h2 class="noticia-titulo"><?= htmlspecialchars($n['titulo'] ?? t('no_news')) ?></h2>
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
                    </div>
                    <div style="text-align:center; margin-top:20px;">
                        <a href="noticias.php" class="btn-nav"><?= t('news_recargar') ?> →</a>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <p class="subtitulo"><?= t('tagline') ?></p>
            <div class="server-status <?= $estado_clase ?>">
                <span class="status-text"><?= $estado_texto ?></span>
                <?php if ($server_online && $tiempo_online !== null && $tiempo_online !== '—'): ?>
                    <span class="uptime-text" id="uptime-display" data-segundos="<?= (int)$uptime_segundos ?>">⏱️ <?= $tiempo_online ?></span>
                <?php endif; ?>
            </div>
            <div class="faction-counts">
                <div class="faction alliance"><span class="faction-circle blue"></span><span class="faction-name"><?= t('alliance') ?></span><span class="faction-number"><?= $facciones['alianza'] ?></span></div>
                <div class="faction horde"><span class="faction-circle red"></span><span class="faction-name"><?= t('horde') ?></span><span class="faction-number"><?= $facciones['horda'] ?></span></div>
            </div>
            <?php if ($mensaje): ?>
                <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <div id="tab-login" class="tab-content activo">
                <form method="POST">
                    <label for="login_user"><?= t('username') ?></label>
                    <input type="text" id="login_user" name="login_user" placeholder="<?= t('login_user_ph') ?>" required>
                    <label for="login_pass"><?= t('password') ?></label>
                    <input type="password" id="login_pass" name="login_pass" placeholder="<?= t('login_pass_ph') ?>" required>
                    <button type="submit" name="login_web"><?= t('login_btn') ?></button>
                </form>
            </div>

            <div id="tab-registro" class="tab-content">
                <form method="POST">
                    <label for="usuario"><?= t('username') ?></label>
                    <input type="text" id="usuario" name="usuario" placeholder="<?= t('username_ph') ?>" required minlength="<?= MIN_USERNAME_LENGTH ?>" maxlength="<?= MAX_USERNAME_LENGTH ?>" value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
                    <label for="password"><?= t('password') ?></label>
                    <input type="password" id="password" name="password" placeholder="<?= t('password_ph') ?>" required minlength="<?= MIN_PASSWORD_LENGTH ?>" maxlength="<?= MAX_PASSWORD_LENGTH ?>">
                    <label for="password2"><?= t('password2') ?></label>
                    <input type="password" id="password2" name="password2" placeholder="<?= t('password2_ph') ?>" required minlength="<?= MIN_PASSWORD_LENGTH ?>" maxlength="<?= MAX_PASSWORD_LENGTH ?>">
                    <button type="submit"><?= t('register_btn') ?></button>
                </form>
            </div>
            <p class="info"><?= t('info_register') ?></p>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(SERVER_NAME) ?> · <?= t('copyright') ?></p>
</footer>

<script src="js/tabs.js"></script>
<script src="js/uptime.js"></script>
<script>
    function cambiarTab(tab) {
        document.querySelectorAll('.tab-content').forEach(function(c) {
            c.classList.remove('activo');
        });
        var contenido = document.getElementById('tab-' + tab);
        if (contenido) contenido.classList.add('activo');
    }
</script>
</body>
</html>