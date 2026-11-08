<?php
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/config.php';

// Variable para el botón de "atrás"
$web_user = $_SESSION['web_username'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma ?? 'es') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(SERVER_NAME) ?> - Logro del Amor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/layout.css">
    <style>
        /* ============================================
           ESTILOS DEL FORO (LOGRO DEL AMOR)
           ============================================ */
        .guia-container {
            max-width: 1100px;
            margin: 40px auto;
            background: rgba(10, 14, 23, 0.95);
            border: 1px solid #3a2e1a;
            border-radius: 8px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.9), inset 0 0 20px rgba(0,0,0,0.6);
            padding: 40px;
            position: relative;
        }

        /* Esquinas decorativas */
        .guia-container::before, .guia-container::after {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            border: 2px solid #a88438;
            pointer-events: none;
        }
        .guia-container::before {
            top: -1px; left: -1px;
            border-right: none; border-bottom: none;
        }
        .guia-container::after {
            bottom: -1px; right: -1px;
            border-left: none; border-top: none;
        }

        /* Título */
        .guia-titulo {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid rgba(168, 132, 56, 0.4);
            padding-bottom: 25px;
        }
        .guia-titulo h1 {
            color: #e3c47d;
            font-family: 'Georgia', serif;
            font-size: 42px;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 0 0 25px rgba(227, 196, 125, 0.5);
            margin-bottom: 10px;
        }

        /* Secciones Generales */
        .seccion { margin-bottom: 45px; }
        .seccion-titulo {
            color: #e3c47d;
            font-family: 'Georgia', serif;
            font-size: 26px;
            border-bottom: 2px solid #3a2e1a;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .seccion-titulo::before {
            content: '◆';
            color: #a88438;
            font-size: 18px;
        }

        /* Estilos de textos y listas */
        h4 {
            color: #e3c47d;
            font-size: 18px;
            margin: 25px 0 10px 0;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 5px;
        }
        
        p { margin-bottom: 10px; font-size: 14.5px; color: #c9d8f0; }
        ul { list-style: none; padding-left: 0; margin-bottom: 15px; }
        li { margin-bottom: 8px; padding-left: 22px; position: relative; font-size: 14.5px; color: #c9d8f0; }
        li::before { content: '›'; color: #a88438; position: absolute; left: 0; font-weight: bold; font-size: 18px; top: -2px; }
        
        /* Bloques de Misión */
        .mision {
            background: rgba(255,255,255,0.02);
            border-left: 3px solid #a88438;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 0 6px 6px 0;
            font-size: 14.5px;
        }
        .mision .titulo { color: #e3c47d; font-weight: bold; font-size: 16px; margin-bottom: 8px; }
        .mision .npc { font-size: 13.5px; color: #9db4d8; margin-bottom: 8px; }
        .mision .npc span { color: #fff; font-weight: bold; }
        .mision .objetivo { color: #e6e8ee; margin-bottom: 5px; font-weight: bold; }
        .mision .objeto { font-size: 13.5px; color: #5ac86b; font-style: italic; margin-bottom: 5px; }
        .mision .texto-jugador { font-size: 14px; color: #b0c4de; margin-bottom: 5px; }
        .mision .recompensa { color: #f0c94a; font-size: 13.5px; font-weight: bold; margin-top: 8px; }

        /* Tabla del Vendedor */
        .tabla-vendedor {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(0,0,0,0.4);
            font-size: 14px;
        }
        .tabla-vendedor th {
            background: rgba(168, 132, 56, 0.2);
            color: #e3c47d;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #3a2e1a;
        }
        .tabla-vendedor td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #c9d8f0;
        }
        .tabla-vendedor tr:hover td { background: rgba(168, 132, 56, 0.05); }

        /* Logros */
        .logro {
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(168, 132, 56, 0.2);
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .logro-titulo { color: #e3c47d; font-size: 18px; font-weight: bold; margin-bottom: 8px; }
        .logro-desc { color: #9db4d8; font-size: 14.5px; margin-bottom: 15px; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 10px; }
        .logro-pasos { font-size: 14.5px; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 4px; border-left: 2px solid #1e5bc6; color: #c9d8f0; }

        /* Pasos de Mazmorra */
        .mazmorra-pasos {
            background: rgba(0,0,0,0.4);
            border-left: 4px solid #c41f3b;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }
        .mazmorra-pasos h5 { color: #e3c47d; font-size: 16px; margin-bottom: 15px; }
        .mazmorra-pasos ol { padding-left: 20px; font-size: 14.5px; color: #c9d8f0; }
        .mazmorra-pasos li { margin-bottom: 8px; }

        /* User Fixed Box */
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

        .img-icon { height: 16px; vertical-align: middle; margin-right: 4px; }
        .icon-text { color: #a88438; font-weight: bold; margin-right: 5px; }

        @media (max-width: 768px) {
            .guia-container { padding: 20px; margin-top: 80px; }
            .guia-titulo h1 { font-size: 28px; }
            .seccion-titulo { font-size: 20px; }
            .tabla-vendedor { font-size: 12px; }
            .tabla-vendedor th, .tabla-vendedor td { padding: 6px; }
        }
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
        <a href="?lang=es" class="btn-lang-fixed" title="Español" style="<?= ($idioma ?? 'es') === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
        <a href="?lang=en" class="btn-lang-fixed" title="English" style="<?= ($idioma ?? 'es') === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
        <a href="index.php" class="btn-back-fixed" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
        <a href="index.php?logout=1" class="btn-logout-fixed" title="Desconectar"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
<?php else: ?>
    <div class="user-fixed-box">
        <a href="?lang=es" class="btn-lang-fixed" title="Español" style="<?= ($idioma ?? 'es') === 'es' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">ES</a>
        <a href="?lang=en" class="btn-lang-fixed" title="English" style="<?= ($idioma ?? 'es') === 'en' ? 'background:#f0c94a;color:#1a1200;' : '' ?>">EN</a>
        <a href="index.php" class="btn-back-fixed" title="Volver"><i class="fa-solid fa-arrow-left"></i></a>
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

<!-- CONTENIDO DE LA GUÍA -->
<div class="guia-container">
    
    <div class="guia-titulo">
        <h1>Logro del Amor</h1>
        <p style="color: #7a8596; font-style: italic;">Guía completa del evento Amor en el Aire</p>
    </div>

    <!-- INTRODUCCIÓN -->
    <div class="seccion">
        <p>Amor en el aire es una celebración del amor y la amistad, en la que los jugadores comparten regalos y dulces, y el aire se llena de perfumes y colonias. Este evento se basa en las costumbres del Día de San Valentín.</p>
        <p>Los jugadores pueden mostrar su aprecio a sus amigos y líderes de facción al recolectar Talismán precioso y así crear Pulsera de talismanes preciosos que puede entregarse para recompensas diarias. La compañía Químicos La Corona ha proporcionado perfumes, colonias, dulces y otros bienes de la estación, en las ciudades principales de Azeroth, que pueden adquirirse mediante Prueba de amor obtenida al completar misiones diarias. Sin embargo, las apariencias engañan, ¡y será responsabilidad de los jugadores investigar a estos nuevos goblins sospechosos y su plaga perfumada!</p>
        <p>Al completar los doce logros especificados en Loco de amor o Loco de amor los jugadores recibirán el título de el Loco de Amor.</p>
        
        <h4>Fechas:</h4>
        <ul>
            <li>Empieza el 5 de Febrero a las 0:01 AM</li>
            <li>Termina el 18 de Febrero a las 23:59 PM</li>
        </ul>

        <h4>Ubicaciones:</h4>
        <ul>
            <li>Alianza: Ciudad de Ventormenta, El Exodar, Darnassus, Forjaz</li>
            <li>Horda: Cima del Trueno, Ciudad de Lunargenta, Entrañas, Orgrimmar</li>
        </ul>
    </div>

    <!-- OBJETOS Y RECOMPENSAS -->
    <div class="seccion">
        <div class="seccion-titulo">Objetos y Recompensas</div>
        
        <h4>Comidas:</h4>
        <ul>
            <li>Delicia de leche</li>
            <li>Deseo oscuro</li>
            <li>Crema de frutos cremosa</li>
            <li>Dulce sorpresa</li>
        </ul>

        <h4>Perfumes:</h4>
        <ul>
            <li>Perfume "VICTORIA"</li>
            <li>Perfume "Encantadora"</li>
            <li>Perfume "Para siempre"</li>
            <li>Colonia "Bravura"</li>
            <li>Colonia "Hechicería"</li>
            <li>Colonia "INCONDICIONAL"</li>
            <li>Botella de perfume</li>
            <li>Botella de colonia</li>
        </ul>

        <h4>Tarjetas:</h4>
        <ul>
            <li>Tarjeta de Cima del Trueno preciosa</li>
            <li>Tarjeta de Orgrimmar preciosa</li>
            <li>Tarjeta de El Exodar preciosa</li>
            <li>Tarjeta de Ventormenta preciosa</li>
            <li>Tarjeta de Ciudad de Lunargenta preciosa</li>
            <li>Tarjeta de Entrañas preciosa</li>
            <li>Tarjeta de Darnassus preciosa</li>
            <li>Tarjeta de Forjaz preciosa</li>
        </ul>

        <h4>Cajas:</h4>
        <ul>
            <li>Caja de bombones</li>
            <li>Caja de vestido precioso</li>
            <li>Caja de traje de noche</li>
        </ul>

        <h4>Varios:</h4>
        <ul>
            <li>Rosa preciosa</li>
            <li>Bolsa de caramelos de corazón</li>
            <li>Puñado de pétalos de rosa</li>
            <li>Cohete del amor</li>
            <li>Cesta de merienda romántica</li>
        </ul>

        <h4>Monedas:</h4>
        <ul>
            <li>Talismán precioso</li>
            <li>Juego de coleccionista de talismanes preciosos</li>
            <li>Pulsera de talismanes preciosos</li>
            <li>Prueba de amor</li>
        </ul>

        <h4>Mascotas:</h4>
        <ul>
            <li>Flecha con astil de veraplata</li>
            <li>Desperdicio tóxico</li>
        </ul>

        <h4>Monturas:</h4>
        <ul>
            <li>Gran cohete de amor</li>
        </ul>

        <h4>Recompensa del buscador de mazmorras:</h4>
        <p>Caja con forma de corazón. Contiene:</p>
        <ul>
            <li>Guiño de amor (4%)</li>
            <li>Talismán de corazón roto (4%)</li>
            <li>Fragmento de felicidad saltarina (4%)</li>
            <li>Broche de perfume dulce (4%)</li>
            <li>Gargantilla de corazón puro (4%)</li>
            <li>Gran cohete de amor (0.03%)</li>
            <li>Máscara de fumigador vil (6%)</li>
            <li>Desperdicio tóxico (6%)</li>
            <li>Rosa eternamente preciosa (6%)</li>
            <li>El Rompecorazones (1.1%)</li>
            <li>Prueba de amor (100%)</li>
        </ul>

        <h4>Jefes del evento:</h4>
        <ul>
            <li>Boticario Hummel</li>
            <li>Boticario Baxter</li>
            <li>Boticario Frye</li>
        </ul>
        <p>Instancia: Castillo de Colmillo Oscuro</p>
    </div>

    <!-- VENDEDORES -->
    <div class="seccion">
        <div class="seccion-titulo">Vendedores</div>
        <p>NPC: Mercader preciosa</p>
        <div style="overflow-x:auto;">
            <table class="tabla-vendedor">
                <thead>
                    <tr>
                        <th>Ítem</th>
                        <th>Costo</th>
                        <th>Nota</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Prueba de amor</td><td>Pulsera de talismanes preciosos x1</td><td>Las misiones dan x5</td></tr>
                    <tr><td>Perfume "VICTORIA" x5</td><td>Prueba de amor x1</td><td>Flirtear con el desastre / Flirtear con el desastre</td></tr>
                    <tr><td>Perfume "Encantadora" x5</td><td>Prueba de amor x1</td><td>Flirtear con el desastre / Flirtear con el desastre</td></tr>
                    <tr><td>Perfume "Para siempre" x5</td><td>Prueba de amor x1</td><td>Flirtear con el desastre / Flirtear con el desastre</td></tr>
                    <tr><td>Colonia "Bravura" x5</td><td>Prueba de amor x1</td><td></td></tr>
                    <tr><td>Colonia "Hechicería" x5</td><td>Prueba de amor x1</td><td></td></tr>
                    <tr><td>Colonia "INCONDICIONAL" x5</td><td>Prueba de amor x1</td><td></td></tr>
                    <tr><td>Bolsa de caramelos de corazón</td><td>Prueba de amor x2</td><td>¡Sé mío!</td></tr>
                    <tr><td>Puñado de pétalos de rosa x5</td><td>Prueba de amor x2</td><td>Puñado de amor / Flirtear con el desastre / Flirtear con el desastre</td></tr>
                    <tr><td>Flecha con astil de plata x5</td><td>Prueba de amor x5</td><td>¡Pinchado!</td></tr>
                    <tr><td>Cohete del amor x5</td><td>Prueba de amor x5</td><td>El resplandor rosa del cohete</td></tr>
                    <tr><td>Rosa preciosa x5</td><td>Prueba de amor x5</td><td></td></tr>
                    <tr><td>Loco de amor</td><td>Prueba de amor x10</td><td>Me apiadé del loco</td></tr>
                    <tr><td>Caja de bombones</td><td>Prueba de amor x10</td><td>Goloso / ¿Solo?</td></tr>
                    <tr><td>Cesta de merienda romántica</td><td>Prueba de amor x10</td><td>¿Solo?</td></tr>
                    <tr><td>Caja de vestido precioso</td><td>Prueba de amor x20</td><td>La preciosa suerte está de tu lado</td></tr>
                    <tr><td>Caja de traje de noche</td><td>Prueba de amor x20</td><td></td></tr>
                    <tr><td>Flecha con astil de veraplata</td><td>Prueba de amor x40</td><td>Pies Rápidos permanentes</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PULSERAS Y RAMOS -->
    <div class="seccion">
        <div class="seccion-titulo">Pulseras de talismanes preciosos</div>
        <p>Para conseguir Pulsera de talismanes preciosos debes tener en el inventario Juego de coleccionista de talismanes preciosos (Obtenido de Kwee Q. Pies Rápidos al aceptar sus misiones) y matar enemigos que den experiencia/honor.</p>
        <p>Al matarlos, automáticamente se recibirá en el inventario Talismán precioso.</p>
        <p>Con 10 Talismán precioso se puede crear Pulsera de talismanes preciosos.</p>
        
        <h4>Ramos de rosas:</h4>
        <p>Los siguientes jefes en mazmorras tienen un 50% de chance de tirar Ramo de rosas de ébano o Ramo de rosas rojas:</p>
        <ul>
            <li>Doncella de Pena (Normal y Heroico)</li>
            <li>Príncipe Keleseth (Normal y Heroico)</li>
            <li>Príncipe Taldaram (Normal y Heroico)</li>
        </ul>
    </div>

    <!-- MISIONES ALIANZA -->
    <div class="seccion">
        <div class="seccion-titulo">Misiones lado Alianza</div>
        
        <h4>Cadena principal</h4>
        <ul>
            <li>[1] Olores extraños</li>
            <li>[1] Algo apesta</li>
            <li>[1] Afanar el aroma</li>
            <li>[1] Fuegos artificiales en La Rosa Áurea</li>
            <li>[1] Pisándole los talones</li>
            <li>[1] Una charla amistosa...</li>
        </ul>

        <p>Desbloquea las siguientes misiones según el nivel del personaje:</p>
        <ul>
            <li>[5-13][Diaria] Aplasta La Corona</li>
            <li>[14-22][Diaria] Aplasta La Corona</li>
            <li>[23-31][Diaria] Aplasta La Corona</li>
            <li>[32-40][Diaria] Aplasta La Corona</li>
            <li>[41-50][Diaria] Aplasta La Corona</li>
            <li>[51-60][Diaria] Aplasta La Corona</li>
            <li>[61-70][Diaria] Aplasta La Corona</li>
            <li>[71-80][Diaria] Aplasta La Corona</li>
        </ul>

        <h4>Cadena opcional</h4>
        <ul>
            <li>[78] El topo</li>
            <li>[80][Diaria mazmorra] ¡Citado!</li>
            <li>[80] Hay algo en el aire (y no es amor)</li>
        </ul>

        <h4>Diarias de "Un regalo para..."</h4>
        <ul>
            <li>[Diaria] Un regalo para el Rey de Ventormenta</li>
            <li>[Diaria] Un regalo para un Señor de Forjaz</li>
            <li>[Diaria] Un regalo para la Suma sacerdotisa de Elune</li>
            <li>[Diaria] Un regalo para el Profeta</li>
        </ul>

        <h4>Diarias sueltas (1 diferente cada día de forma aleatoria)</h4>
        <ul>
            <li>[Diaria] Cúmulo de colonia clásica</li>
            <li>[Diaria] El punto perfecto de perfume</li>
            <li>[Diaria] Bombardeo de bombones</li>
        </ul>

        <h4>Detalle de las misiones</h4>
        
        <div class="mision">
            <div class="titulo">1) Olores extraños</div>
            <div class="npc">Empieza: <span>Inspector subalterno</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Preséntate ante el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="texto-jugador">El jugador debe ir a hablar con Inspector Snip Tuerceganzúas en la Ciudad de Ventormenta.</div>
        </div>

        <div class="mision">
            <div class="titulo">2) Algo apesta</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Usa el analizador de aire de Tuerceganzúas para analizar el aire alrededor de 6 guardias altamente perfumados. Luego vuelve con el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Analizador de aire de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe usar Analizador de aire de Tuerceganzúas en 6 Guardia de la ciudad de Ventormenta que tengan el buff Altamente perfumado.</div>
        </div>

        <div class="mision">
            <div class="titulo">3) Afanar el aroma</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mientras estés de mensajero, ve al montón de cajones de Químicos La Corona, S.L. fuera de las puertas de Ventormenta y vuelve con el paquete que te lancen a ver al inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="texto-jugador">El jugador recibirá el buff Uniforme del servicio de paquetería La Corona al momento de aceptar la misión y el personaje empezará andar automáticamente hacia adelante.</div>
            <div class="texto-jugador">Se recomienda tomar la misión apuntando a las afueras de la Ciudad de Ventormenta para empezar camino hacia la zona de la misión.</div>
            <div class="texto-jugador">Hay que acercarse a Avizor de suministros de La Corona cercanos a Suministros de Químicos La Corona, S.L. para que le lancen una caja al jugador.</div>
            <div class="texto-jugador">Una vez con la caja en las espaldas, se debe volver rápidamente a Inspector Snip Tuerceganzúas.</div>
        </div>

        <div class="mision">
            <div class="titulo">4) Fuegos artificiales en La Rosa Áurea</div>
            <div class="npc">Empieza: <span>Inspector subalterno</span> | Termina: <span>Marion Sutton</span></div>
            <div class="objetivo">Objetivo: Habla con Marion Sutton en La Rosa Áurea, en el Distrito de Mercaderes de Ventormenta.</div>
            <div class="texto-jugador">El jugador debe ir a hablar con Marion Sutton en la Ciudad de Ventormenta.</div>
        </div>

        <div class="mision">
            <div class="titulo">5) Pisándole los talones</div>
            <div class="npc">Empieza: <span>Marion Sutton</span> | Termina: <span>Marion Sutton</span></div>
            <div class="objetivo">Objetivo: Busca a Quejica Misilóxido en la Contaduría de Ventormenta, la casa de subastas y la peluquería de la Ciudad de Ventormenta.</div>
            <div class="texto-jugador">El jugador debe buscar a Quejica Misilóxido en las siguientes zonas de Ventormenta:</div>
            <ul>
                <li>Casa de subastas</li>
                <li>Contaduría</li>
                <li>Peluquería</li>
            </ul>
            <div class="texto-jugador">El jugador no podrá moverse cada vez que vea a Quejica Misilóxido hasta que éste termine de hablar.</div>
        </div>

        <div class="mision">
            <div class="titulo">6) Una charla amistosa...</div>
            <div class="npc">Empieza: <span>Marion Sutton</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Ve al Puerto de Ventormenta e interroga a Quejica Misilóxido sobre los suministros. Vuelve con cualquier prueba que te aporte.</div>
            <div class="texto-jugador">El jugador debe encontrar a Quejica Misilóxido en el Puerto de Ventormenta e interrogarlo para recibir Libro de contabilidad de Quejica.</div>
        </div>

        <h4>Desbloquea misión diaria: Aplasta La Corona</h4>

        <div class="mision">
            <div class="titulo">[5-13] Aplasta La Corona</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mata a 5 lacayos de La Corona y utiliza la bomba de korio de Tuerceganzúas para destruir un vagón químico al noreste del Cuartel de Arroyoeste, en el Bosque de Elwynn. Después, vuelve con el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Bomba de korio de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe matar a 5 Lacayo de La Corona y usar Bomba de korio de Tuerceganzúas en Vagón químico (Bosque de Elwynn).</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">[14-22] Aplasta La Corona</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mata a 5 matones de La Corona y utiliza la bomba de korio de Tuerceganzúas para destruir un vagón químico al sur del Páramo de los Ancianos en Costa Oscura. Después, vuelve con el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Bomba de korio de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe matar a 5 Matón de La Corona y usar Bomba de korio de Tuerceganzúas en Vagón químico (Costa Oscura).</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">[23-31] Aplasta La Corona</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mata a 5 desempolvadores de La Corona y utiliza la bomba de korio de Tuerceganzúas para destruir un vagón químico en la granja abandonada al sureste de Villa Oscura, en el Bosque del Ocaso, y después vuelve con el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Bomba de korio de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe matar a 5 Desempolvador de La Corona y usar Bomba de korio de Tuerceganzúas en Vagón químico (Laderas de Trabalomas).</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">[32-40] Aplasta La Corona</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mata a 5 macarras de La Corona y utiliza la bomba de korio de Tuerceganzúas para destruir un vagón químico a las afueras de Theramore en Marjal Revolcafango. Después, vuelve con el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Bomba de korio de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe matar a 5 Macarra de La Corona y usar Bomba de korio de Tuerceganzúas en Vagón químico (Marjal Revolcafango).</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">[41-50] Aplasta La Corona</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mata a 5 agentes de La Corona y utiliza la bomba de korio de Tuerceganzúas para destruir un vagón químico al este de Pico Nidal en las Tierras del Interior. Después, vuelve con el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Bomba de korio de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe matar a 5 Agente de La Corona y usar Bomba de korio de Tuerceganzúas en Vagón químico (Tierras del Interior).</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">[51-60] Aplasta La Corona</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mata a 5 rociadores de La Corona y utiliza la bomba de korio de Tuerceganzúas para destruir un vagón químico al este de Vista Eterna, en Cuna del Invierno. Después, vuelve con el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Bomba de korio de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe matar a 5 Rociador de La Corona y usar Bomba de korio de Tuerceganzúas en Vagón químico (Cuna del Invierno).</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">[61-70] Aplasta La Corona</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mata a 5 subordinados de La Corona y utiliza la bomba de korio de Tuerceganzúas para destruir un vagón químico al este de Shattrath, en el Bosque de Terokkar. Después, vuelve con el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Bomba de korio de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe matar a 5 Subordinado de La Corona y usar Bomba de korio de Tuerceganzúas en Vagón químico (Bosque de Terokkar).</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">[71-80] Aplasta La Corona</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mata a 5 pulverizadores de La Corona y utiliza la bomba de korio de Tuerceganzúas para destruir un vagón químico en El Bosque Desolado en el Bosque Canto de Cristal. Después, vuelve con el inspector Snip Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Bomba de korio de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe matar a 5 Pulverizador de La Corona y usar Bomba de korio de Tuerceganzúas en Vagón químico (Bosque Canto de Cristal).</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <h4>Cadena opcional</h4>
        <div class="mision">
            <div class="titulo">1) El topo</div>
            <div class="npc">Empieza: <span>Inspector Snip Tuerceganzúas</span> | Termina: <span>Investigador Fezzen Arreolatón</span></div>
            <div class="objetivo">Objetivo: Preséntate ante el investigador Fezzen Arreolatón en el Castillo de Colmillo Oscuro.</div>
            <div class="texto-jugador">El jugador debe ir a hablar con Investigador Fezzen Arreolatón en el Castillo de Colmillo Oscuro.</div>
        </div>

        <div class="mision">
            <div class="titulo">2) Desbloquea misión diaria de mazmorra: ¡Citado!</div>
            <div class="npc">Empieza: <span>Investigador Fezzen Arreolatón</span> | Termina: <span>Boticario Hummel</span></div>
            <div class="objetivo">Objetivo: Entrega la citación judicial al boticario Hummel en el Castillo de Colmillo Oscuro.</div>
            <div class="objeto">Objeto provisto: Juego estándar para servir de los boticarios</div>
            <div class="texto-jugador">El jugador debe abrir Juego estándar para servir de los boticarios para obtener Citación judicial y entregarla a Boticario Hummel cuando es amistoso.</div>
        </div>

        <div class="mision">
            <div class="titulo">3) Hay algo en el aire (y no es amor)</div>
            <div class="npc">Empieza: <span>Tarjeta preciosa descolorida</span> | Termina: <span>Inspector Snip Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Llévale la tarjeta preciosa descolorida al inspector Snap Tuerceganzúas en Ventormenta.</div>
            <div class="objeto">Objeto provisto: Tarjeta preciosa descolorida</div>
            <div class="texto-jugador">El jugador debe llevar Tarjeta preciosa descolorida a Inspector Snip Tuerceganzúas en la Ciudad de Ventormenta.</div>
        </div>

        <h4>Diarias de "Un regalo para..."</h4>
        
        <div class="mision">
            <div class="titulo">Un regalo para el Rey de Ventormenta</div>
            <div class="npc">Empieza: <span>Kwee Q. Pies Rápidos</span> | Termina: <span>Rey Varian Wrynn</span></div>
            <div class="objetivo">Objetivo: Regálale una pulsera de talismanes preciosos al rey Varian Wrynn.</div>
            <div class="objeto">Objeto provisto: Juego de coleccionista de talismanes preciosos</div>
            <div class="texto-jugador">El jugador debe regalarle una Pulsera de talismanes preciosos a Rey Varian Wrynn en la Ciudad de Ventormenta.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5) + Tarjeta de Ventormenta preciosa</div>
        </div>

        <div class="mision">
            <div class="titulo">Un regalo para un Señor de Forjaz</div>
            <div class="npc">Empieza: <span>Kwee Q. Pies Rápidos</span> | Termina: <span>Rey Magni Barbabronce</span></div>
            <div class="objetivo">Objetivo: Regálale una pulsera de talismanes preciosos a Muradin Barbabronce.</div>
            <div class="objeto">Objeto provisto: Juego de coleccionista de talismanes preciosos</div>
            <div class="texto-jugador">El jugador debe regalarle una Pulsera de talismanes preciosos a Rey Magni Barbabronce en Forjaz.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5) + Tarjeta de Forjaz preciosa</div>
        </div>

        <div class="mision">
            <div class="titulo">Un regalo para la Suma sacerdotisa de Elune</div>
            <div class="npc">Empieza: <span>Kwee Q. Pies Rápidos</span> | Termina: <span>Tyrande Susurravientos</span></div>
            <div class="objeto">Objeto provisto: Juego de coleccionista de talismanes preciosos</div>
            <div class="texto-jugador">El jugador debe regalarle una Pulsera de talismanes preciosos a Tyrande Susurravientos en Darnassus.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5) + Tarjeta de Darnassus preciosa</div>
        </div>

        <div class="mision">
            <div class="titulo">Un regalo para el Profeta</div>
            <div class="npc">Empieza: <span>Kwee Q. Pies Rápidos</span> | Termina: <span>Profeta Velen</span></div>
            <div class="objeto">Objeto provisto: Juego de coleccionista de talismanes preciosos</div>
            <div class="texto-jugador">El jugador debe regalarle una Pulsera de talismanes preciosos a Profeta Velen en El Exodar.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5) + Tarjeta de El Exodar preciosa</div>
        </div>

        <h4>Diarias sueltas (1 diferente cada día de forma aleatoria)</h4>

        <div class="mision">
            <div class="titulo">Cúmulo de colonia clásica</div>
            <div class="objetivo">Objetivo: Rocía a 10 personas con el vaporizador de colonia La Corona.</div>
            <div class="objeto">Objeto provisto: Vaporizador de colonia de La Corona</div>
            <div class="texto-jugador">El jugador debe rociar a 10 jugadores con Vaporizador de colonia de La Corona.</div>
            <div class="texto-jugador">Sólo se puede usar en jugadores que no tengan el buff Satisfacción de muestra.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">El punto perfecto de perfume</div>
            <div class="objetivo">Objetivo: Rocía a 10 personas con el vaporizador de colonia La Corona.</div>
            <div class="objeto">Objeto provisto: Vaporizador de perfume de La Corona</div>
            <div class="texto-jugador">El jugador debe rociar a 10 jugadores con Vaporizador de perfume de La Corona.</div>
            <div class="texto-jugador">Sólo se puede usar en jugadores que no tengan el buff Satisfacción de muestra.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">Bombardeo de bombones</div>
            <div class="objetivo">Objetivo: Lanza muestras de chocolate de La Corona a 10 personas.</div>
            <div class="objeto">Objeto provisto: Muestra de chocolate de La Corona</div>
            <div class="texto-jugador">El jugador debe lanzar a 10 jugadores Muestra de chocolate de La Corona.</div>
            <div class="texto-jugador">Sólo se puede usar en jugadores que no tengan el buff Satisfacción de muestra.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>
    </div>

    <!-- MISIONES HORDA -->
    <div class="seccion">
        <div class="seccion-titulo">Misiones lado Horda</div>
        
        <h4>Cadena principal</h4>
        <ul>
            <li>[1] Olores extraños</li>
            <li>[1] Algo apesta</li>
            <li>[1] Afanar el aroma</li>
            <li>[1] La querida de Quejica</li>
            <li>[1] Pisándole los talones</li>
            <li>[1] Una charla amistosa...</li>
        </ul>

        <p>Desbloquea las siguientes misiones según el nivel del personaje:</p>
        <ul>
            <li>[5-13][Diaria] Aplasta La Corona</li>
            <li>[14-22][Diaria] Aplasta La Corona</li>
            <li>[23-31][Diaria] Aplasta La Corona</li>
            <li>[32-40][Diaria] Aplasta La Corona</li>
            <li>[41-50][Diaria] Aplasta La Corona</li>
            <li>[51-60][Diaria] Aplasta La Corona</li>
            <li>[61-70][Diaria] Aplasta La Corona</li>
            <li>[71-80][Diaria] Aplasta La Corona</li>
        </ul>

        <h4>Cadena opcional</h4>
        <ul>
            <li>[78] El topo</li>
            <li>[80][Diaria mazmorra] ¡Citado!</li>
            <li>[80] Hay algo en el aire (y no es amor)</li>
        </ul>

        <h4>Diarias de "Un regalo para..."</h4>
        <ul>
            <li>[Diaria] Un regalo para el Jefe de Guerra</li>
            <li>[Diaria] Un regalo para la Reina alma en pena</li>
            <li>[Diaria] Un regalo para el Señor regente de Quel'Thalas</li>
            <li>[Diaria] Un regalo para el Gran jefe</li>
        </ul>

        <h4>Diarias sueltas (1 diferente cada día de forma aleatoria)</h4>
        <ul>
            <li>[Diaria] Cúmulo de colonia clásica</li>
            <li>[Diaria] El punto perfecto de perfume</li>
            <li>[Diaria] Bombardeo de bombones</li>
        </ul>

        <h4>Detalle de las misiones</h4>
        
        <div class="mision">
            <div class="titulo">1) Olores extraños</div>
            <div class="npc">Empieza: <span>Detective subalterno</span> | Termina: <span>Detective Snap Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Preséntate ante el detective Snap Tuerceganzúas en Orgrimmar.</div>
            <div class="texto-jugador">El jugador debe ir a hablar con Detective Snap Tuerceganzúas en Orgrimmar.</div>
        </div>

        <div class="mision">
            <div class="titulo">2) Algo apesta</div>
            <div class="npc">Empieza: <span>Detective Snap Tuerceganzúas</span> | Termina: <span>Detective Snap Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Usa el analizador de aire de Tuerceganzúas para analizar el aire alrededor de 6 guardias altamente perfumados. Luego vuelve con el detective Snap Tuerceganzúas en Orgrimmar.</div>
            <div class="objeto">Objeto provisto: Analizador de aire de Tuerceganzúas</div>
            <div class="texto-jugador">El jugador debe usar Analizador de aire de Tuerceganzúas en 6 Bruto de Orgrimmar que tengan el buff Altamente perfumado.</div>
        </div>

        <div class="mision">
            <div class="titulo">3) Afanar el aroma</div>
            <div class="npc">Empieza: <span>Detective Snap Tuerceganzúas</span> | Termina: <span>Detective Snap Tuerceganzúas</span></div>
            <div class="objetivo">Objetivo: Mientras estés de mensajero, ve al montón de cajones de Químicos La Corona, S.L. fuera de las puertas de Orgrimmar y vuelve con el paquete que te lancen a ver al detective Snap Tuerceganzúas en Orgrimmar.</div>
            <div class="texto-jugador">El jugador recibirá el buff Uniforme del servicio de paquetería La Corona al momento de aceptar la misión y el personaje empezará andar automáticamente hacia adelante.</div>
            <div class="texto-jugador">Se recomienda tomar la misión apuntando a las afueras de Orgrimmar para empezar camino hacia la zona de la misión.</div>
            <div class="texto-jugador">Hay que acercarse a Guardia de suministros de La Corona cercanos a Suministros de Químicos La Corona, S.L. para que le lancen una caja al jugador.</div>
            <div class="texto-jugador">Una vez con la caja en las espaldas, se debe volver rápidamente a Detective Snap Tuerceganzúas.</div>
        </div>

        <div class="mision">
            <div class="titulo">4) La querida de Quejica</div>
            <div class="npc">Empieza: <span>Detective Snap Tuerceganzúas</span> | Termina: <span>Roka</span></div>
            <div class="objeto">Objeto provisto: Juego de coleccionista de talismanes preciosos</div>
            <div class="texto-jugador">El jugador debe regalarle una Pulsera de talismanes preciosos a Lady Sylvanas Brisaveloz en Entrañas.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5) + Tarjeta de Entrañas preciosa</div>
        </div>

        <div class="mision">
            <div class="titulo">Un regalo para el Señor regente de Quel'Thalas</div>
            <div class="npc">Empieza: <span>Kwee Q. Pies Rápidos</span> | Termina: <span>Lor'themar Theron</span></div>
            <div class="objeto">Objeto provisto: Juego de coleccionista de talismanes preciosos</div>
            <div class="texto-jugador">El jugador debe regalarle una Pulsera de talismanes preciosos a Lor'themar Theron en Lunargenta.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5) + Tarjeta de Ciudad de Lunargenta preciosa</div>
        </div>

        <div class="mision">
            <div class="titulo">Un regalo para el Gran jefe</div>
            <div class="npc">Empieza: <span>Kwee Q. Pies Rápidos</span> | Termina: <span>Cairne Pezuña de Sangre</span></div>
            <div class="objeto">Objeto provisto: Juego de coleccionista de talismanes preciosos</div>
            <div class="texto-jugador">El jugador debe regalarle una Pulsera de talismanes preciosos a Cairne Pezuña de Sangre en Cima del Trueno.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5) + Tarjeta de Cima del Trueno preciosa</div>
        </div>

        <h4>Diarias sueltas (1 diferente cada día de forma aleatoria)</h4>

        <div class="mision">
            <div class="titulo">Cúmulo de colonia clásica</div>
            <div class="objetivo">Objetivo: Rocía a 10 personas con el vaporizador de colonia La Corona.</div>
            <div class="objeto">Objeto provisto: Vaporizador de colonia de La Corona</div>
            <div class="texto-jugador">El jugador debe rociar a 10 jugadores con Vaporizador de colonia de La Corona.</div>
            <div class="texto-jugador">Sólo se puede usar en jugadores que no tengan el buff Satisfacción de muestra.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">El punto perfecto de perfume</div>
            <div class="objetivo">Objetivo: Rocía a 10 personas con el vaporizador de colonia La Corona.</div>
            <div class="objeto">Objeto provisto: Vaporizador de perfume de La Corona</div>
            <div class="texto-jugador">El jugador debe rociar a 10 jugadores con Vaporizador de perfume de La Corona.</div>
            <div class="texto-jugador">Sólo se puede usar en jugadores que no tengan el buff Satisfacción de muestra.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>

        <div class="mision">
            <div class="titulo">Bombardeo de bombones</div>
            <div class="objetivo">Objetivo: Lanza muestras de chocolate de La Corona a 10 personas.</div>
            <div class="objeto">Objeto provisto: Muestra de chocolate de La Corona</div>
            <div class="texto-jugador">El jugador debe lanzar a 10 jugadores Muestra de chocolate de La Corona.</div>
            <div class="texto-jugador">Sólo se puede usar en jugadores que no tengan el buff Satisfacción de muestra.</div>
            <div class="recompensa">Recompensa: Prueba de amor (5)</div>
        </div>
    </div>

    <!-- LOGROS -->
    <div class="seccion">
        <div class="seccion-titulo">Logros</div>
        
        <div class="logro">
            <div class="logro-titulo">¡Sé mío!</div>
            <div class="logro-desc">Requisitos: Come los ocho corazones de la "bolsa de caramelos" listados a continuación.</div>
            <div class="logro-pasos">
                - Corazón de caramelo "¡Sé mío!"<br>
                - Corazón de caramelo "TE QUIERO"<br>
                - Corazón de caramelo "Te seguiré por todo Azeroth."<br>
                - Corazón de caramelo "Todo tuyo."<br>
                - Corazón de caramelo "¡No hay nadie como tú!"<br>
                - Corazón de caramelo "¡Soy todo tuyo!"<br>
                - Corazón de caramelo "¡Para mí!"<br>
                - Corazón de caramelo "Labios calientes."<br><br>
                1) Compra Bolsa de caramelos de corazón de Mercader preciosa. La bolsa puede contener caramelos repetidos por lo que es muy probable que haya que comprar más de una.<br>
                2) Usa las 10 cargas de Bolsa de caramelos de corazón para obtener los corazones.<br>
                3) Cómelos.
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Encantador</div>
            <div class="logro-desc">Requisitos: Crea 12 pulseras de talismanes preciosos.</div>
            <div class="logro-pasos">1) Crea 12 Pulsera de talismanes preciosos de la manera indicada en la parte principal de esta guía.</div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Amor peligroso</div>
            <div class="logro-desc">Requisitos: Ayuda al Cártel Bonvapor a detener el complot de la siniestra empresa Químicos La Corona, S.L.</div>
            <div class="logro-pasos">
                1) Debes completar la serie de misiones que empieza con Olores extraños / Olores extraños.<br>
                2) Cuando completes la última misión de la serie, Una charla amistosa... / Una charla amistosa... se desbloquearán las misiones para este logro.<br>
                3) Completa una de las misiones de nombre Aplasta la corona, ofrecida por Inspector Snip Tuerceganzúas en Ciudad de Ventormenta / Detective Snap Tuerceganzúas en Orgrimmar. Las misiones varían de acuerdo al nivel del personaje.
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Puñado de amor</div>
            <div class="logro-desc">Requisitos: Usa un puñado de pétalos de rosa sobre cada una de las combinaciones de raza/clase listadas debajo.</div>
            <div class="logro-pasos">
                1) Compra Puñado de pétalos de rosa de Mercader preciosa.<br>
                2) Úsalo en los siguientes personajes:<br>
                - Gnomo brujo<br>
                - Orco caballero de la muerte<br>
                - Humano caballero de la muerte<br>
                - Elfo de la noche sacerdote<br>
                - Orco chamán<br>
                - Tauren druida<br>
                - No-muerto guerrero<br>
                - Trol pícaro<br>
                - Elfo de sangre mago<br>
                - Draenei paladín<br>
                - Enano cazador
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Flirtear con el desastre</div>
            <div class="logro-desc">Requisitos: Acaba totalmente borracho, ponte tu mejor perfume, tira un puñado de pétalos de rosa encima de Sraaz y luego bésalo. Por la mañana lo lamentarás.</div>
            <div class="logro-pasos">
                - Alianza: Acaba totalmente borracho, ponte tu mejor perfume, tira un puñado de pétalos de rosa encima de Jeremiah Payson y luego bésalo. Por la mañana lo lamentarás.<br>
                - Horda: Acaba totalmente borracho, ponte tu mejor perfume, tira un puñado de pétalos de rosa encima de Sraaz y luego bésalo. Por la mañana lo lamentarás.<br><br>
                1) Asegúrate de estar completamente borracho. Lo notarás cuando en el chat aparezca el mensaje 'Estás completamente borracho'.<br>
                2) Compra Puñado de pétalos de rosa de Mercader preciosa.<br>
                3) Compra y utiliza cualquiera de Perfume "VICTORIA" o Perfume "Encantadora" o Perfume "Para siempre" de Mercader preciosa.<br>
                4) Ve a Sraaz en Forjaz si eres Alianza | Ve a Jeremiah Payson en Entrañas si eres Horda.<br>
                5) Lanza Puñado de pétalos de rosa en Sraaz | Jeremiah Payson.<br>
                6) Usa /kiss en Sraaz | Jeremiah Payson.
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Me apiadé del loco</div>
            <div class="logro-desc">Requisitos: Apiádate del loco de amor en los lugares especificados a continuación: Conquista de Invierno, Anillo de la batalla de la Arena Gurubashi, Herrería de la Cuenca de Arathi, La Matanza de Stratholme, Naxxramas.</div>
            <div class="logro-pasos">
                1) Compra 5 Loco de amor (1 por cada mapa) de Mercader preciosa.<br>
                2) Ve a las zonas/mapas indicados.<br>
                3) Usa Loco de amor.<br>
                4) Usa /pena o /pity en el Loco de amor que haz invocado.
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">¿Solo?</div>
            <div class="logro-desc">Requisitos: Disfruta una delicia de leche con alguien en Dalaran en un picnic romántico durante la celebración del Amor en el aire.</div>
            <div class="logro-pasos">
                1) Compra Cesta de merienda romántica de Mercader preciosa.<br>
                2) Compra Caja de bombones de Mercader preciosa.<br>
                3) Consigue de dicha caja Delicia de leche.<br>
                4) Ve a Dalaran con un amigo/a ;). Debes estar en una zona donde el mini mapa diga "Dalaran", no una subzona como "Plaza Tejerruna".<br>
                5) Invoca Cesta de merienda romántica.<br>
                6) Tú y tu amigo/a deben hacer clic en la cesta y comer Delicia de leche.
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">La preciosa suerte está de tu lado</div>
            <div class="logro-desc">Requisitos: Abre una caja de vestido precioso y recibe un vestido negro precioso.</div>
            <div class="logro-pasos">1) Compra Caja de vestido precioso de Mercader preciosa hasta conseguir Vestido negro precioso.</div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Mi amor es como una rosa roja</div>
            <div class="logro-desc">Requisitos: Consigue un ramo de rosas rojas o un ramo de rosas de ébano durante la celebración del Amor en el aire.</div>
            <div class="logro-pasos">
                1) Lotea de los siguientes jefes Ramo de rosas rojas o Ramo de rosas de ébano:<br>
                - Doncella de Pena (Normal y Heroico)<br>
                - Príncipe Keleseth (Normal y Heroico)<br>
                - Príncipe Taldaram (Normal y Heroico)
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">La nación de la adoración</div>
            <div class="logro-desc">Requisitos: Completa las misiones diarias de pulseras de talismanes preciosos para cada capital de la Alianza / Completa las misiones diarias de pulseras de talismanes preciosos para cada capital de la Horda.</div>
            <div class="logro-pasos">
                1) Completa las siguientes misiones diarias según tu facción:<br>
                - Un regalo para la Suma sacerdotisa de Elune<br>
                - Un regalo para un Señor de Forjaz<br>
                - Un regalo para el Profeta<br>
                - Un regalo para el Rey de Ventormenta<br>
                - Un regalo para el Jefe de Guerra<br>
                - Un regalo para el Gran jefe<br>
                - Un regalo para el Señor regente de Quel'Thalas<br>
                - Un regalo para la Reina alma en pena
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Pies Rápidos</div>
            <div class="logro-desc">Requisitos: Consigue una mascota Pies Rápidos permanente obteniendo una flecha con astil de veraplata.</div>
            <div class="logro-pasos">1) Compra Flecha con astil de veraplata de Mercader preciosa.</div>
        </div>

        <div class="logro">
            <div class="logro-titulo">El resplandor rosa del cohete</div>
            <div class="logro-desc">Requisitos: Lanza 10 cohetes del amor en menos de 20 segundos.</div>
            <div class="logro-pasos">
                1) Compra 10 Cohete del amor de Mercader preciosa.<br>
                2) Lánzalos en menos de 20 segundos.
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">¡Pinchado!</div>
            <div class="logro-desc">Requisitos: Dispara a 10 jugadores con flechas con astil de plata.</div>
            <div class="logro-pasos">
                1) Compra 10 Flecha con astil de plata de Mercader preciosa.<br>
                2) Úsalo en 10 jugadores.
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Goloso</div>
            <div class="logro-desc">Requisitos: Prueba las golosinas del Amor en el aire listadas a continuación: Delicia de leche, Deseo oscuro, Dulce sorpresa, Crema de frutos cremosa.</div>
            <div class="logro-pasos">
                1) Compra Caja de bombones de Mercader preciosa.<br>
                2) Abre la caja y toma todos los chocolates.<br>
                3) Cómelos.
            </div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Amor duele</div>
            <div class="logro-desc">Requisitos: Derrota al trío de boticarios de Químicos La Corona, S.L. en el Castillo de Colmillo Oscuro.</div>
            <div class="logro-pasos">1) Derrota a Boticario Hummel, Boticario Baxter y Boticario Frye en Castillo de Colmillo Oscuro.</div>
        </div>

        <div class="logro">
            <div class="logro-titulo">Loco de amor</div>
            <div class="logro-desc">Requisitos: Completa los logros de Amor en el aire listados a continuación. Recibirás el título el Loco de Amor.</div>
            <div class="logro-pasos">
                Para la Alianza: Encantador, ¡Pinchado!, Flirtear con el desastre, ¿Solo?, Amor peligroso, La nación de la adoración, El resplandor rosa del cohete, Puñado de amor, Goloso, ¡Sé mío!, Mi amor es como una rosa roja, Me apiadé del loco.<br><br>
                Para la Horda: Encantador, ¡Pinchado!, Flirtear con el desastre, ¿Solo?, Amor peligroso, El resplandor rosa del cohete, La nación de la adoración, Puñado de amor, Goloso, ¡Sé mío!, Mi amor es como una rosa roja, Me apiadé del loco.
            </div>
        </div>
    </div>

    <!-- MAZMORRA -->
    <div class="seccion">
        <div class="seccion-titulo">Cómo empezar el encuentro según la forma de ingresar a la instancia</div>
        
        <div class="mazmorra-pasos">
            <h5>Si se ingresa por la entrada de la instancia:</h5>
            <ol>
                <li>Llegar hasta la sala de Rethilgore</li>
                <li>Hacer clic en la palanca de la celda del medio</li>
                <li>Hablar con Hechicero Ashcrombe</li>
                <li>Seguir su recorrido y esperar que abra la puerta del patio</li>
                <li>Hablar con Investigador Fezzen Arreolatón y aceptar la misión ¡Citado!</li>
                <li>Tras aceptar la misión, abrir el objeto provisto Juego estándar para servir de los boticarios</li>
                <li>Tomar Citación judicial</li>
                <li>Entregar la misión a Boticario Hummel</li>
                <li>Luego de entregar la misión, Boticario Hummel dirá unas palabras y se volverá atacable, permitiendo comenzar el encuentro</li>
            </ol>
        </div>

        <div class="mazmorra-pasos" style="border-left-color: #1e5bc6;">
            <h5>Si se ingresa por la opción del Buscador de mazmorras Químicos La Corona, S.L.:</h5>
            <ol>
                <li>Acercarse hasta la puerta del patio</li>
                <li>A su izquierda, se encuentra una palanca que abrirá la puerta tras hacerle clic</li>
                <li>Una vez abierta la puerta, hablar con Investigador Fezzen Arreolatón y aceptar la misión ¡Citado!</li>
                <li>Tras aceptar la misión, abrir el objeto provisto Juego estándar para servir de los boticarios</li>
                <li>Tomar Citación judicial</li>
                <li>Entregar la misión a Boticario Hummel</li>
                <li>Luego de entregar la misión, Boticario Hummel dirá unas palabras y se volverá atacable, permitiendo comenzar el encuentro</li>
            </ol>
        </div>
    </div>

</div>

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
