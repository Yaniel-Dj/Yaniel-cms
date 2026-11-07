<?php
require_once __DIR__ . '/config.php';

// Conexiones
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, CHAR_DB_NAME, (int)DB_PORT);
$worldConn = new mysqli(DB_HOST, DB_USER, DB_PASS, WORLD_DB_NAME, (int)DB_PORT);

if ($conn->connect_error || $worldConn->connect_error) {
    die("Error de conexión a la base de datos.");
}
$conn->set_charset("utf8"); 
$worldConn->set_charset("utf8");

// 1. Lista de personajes
$personajes = [];
$res = $conn->query("SELECT guid, name, class, race, level FROM characters ORDER BY level DESC LIMIT 100");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $personajes[] = $row;
    }
}

$personaje = null; 
$stats = null; 
$equipo = []; 
$profesiones = []; 
$esHorda = false;
$logros = 0; 
$gearscore = 0; 
$totalItems = 0; 
$sumaIlvl = 0;

if (isset($_GET['guid']) && is_numeric($_GET['guid'])) {
    $guid = (int)$_GET['guid'];

    // Datos del personaje
    $stmt = $conn->prepare("SELECT guid, name, class, race, level FROM characters WHERE guid = ?");
    $stmt->bind_param("i", $guid);
    $stmt->execute();
    $personaje = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($personaje) {
        // Detección de facción
        $razasHorda = [2, 5, 6, 8, 10];
        if (in_array((int)$personaje['race'], $razasHorda)) {
            $esHorda = true;
        }

        // LOGROS
        $stmtLogros = $conn->prepare("SELECT SUM(quantity) as total FROM character_achievement WHERE guid = ?");
        $stmtLogros->bind_param("i", $guid);
        $stmtLogros->execute();
        $resLogros = $stmtLogros->get_result()->fetch_assoc();
        $logros = $resLogros['total'] ?? 0;
        $stmtLogros->close();

        // Stats del personaje
        $stmtStats = $conn->prepare("SELECT maxhealth, maxpower1, strength, agility, stamina, intellect, critPct, attackPower FROM character_stats WHERE guid = ?");
        $stmtStats->bind_param("i", $guid);
        $stmtStats->execute();
        $stats = $stmtStats->get_result()->fetch_assoc();
        $stmtStats->close();

        // Equipo
        $sqlEq = "SELECT ci.slot, ii.itemEntry FROM character_inventory ci JOIN item_instance ii ON ci.item = ii.guid WHERE ci.guid = ? AND ci.bag = 0 AND ci.slot BETWEEN 0 AND 18";
        $stmtEq = $conn->prepare($sqlEq);
        $stmtEq->bind_param("i", $guid);
        $stmtEq->execute();
        $resEq = $stmtEq->get_result();
        
        $itemsEquipados = [];
        while ($row = $resEq->fetch_assoc()) {
            $itemsEquipados[(int)$row['slot']] = (int)$row['itemEntry'];
        }
        $stmtEq->close();

        if (!empty($itemsEquipados)) {
            $ids = implode(',', array_unique(array_values($itemsEquipados)));
            // Consulta con descripción incluida
            $sqlItems = "SELECT entry, name, Quality, icon, ItemLevel, RequiredLevel, armor, dmg_min1, dmg_max1, delay, 
                                InventoryType, description, stat_type1, stat_value1, stat_type2, stat_value2, stat_type3, stat_value3, 
                                stat_type4, stat_value4, stat_type5, stat_value5 
                         FROM item_template WHERE entry IN ($ids)";
            $resItems = $worldConn->query($sqlItems);
            
            $statNames = [
                3 => 'Agilidad', 4 => 'Fuerza', 5 => 'Intelecto', 6 => 'Espíritu', 7 => 'Aguante',
                12 => 'Defensa', 13 => 'Esquiva', 14 => 'Parada', 15 => 'Bloqueo',
                16 => 'Golpe', 31 => 'Golpe', 32 => 'Golpe Crítico', 36 => 'Celeridad', 37 => 'Pericia',
                38 => 'Poder de Ataque', 43 => 'Poder con Hechizos', 44 => 'Poder de Ataque', 45 => 'Poder con Hechizos'
            ];

            $invTypes = [
                1 => 'Cabeza', 2 => 'Cuello', 3 => 'Hombros', 4 => 'Camisa', 5 => 'Pecho',
                6 => 'Cintura', 7 => 'Piernas', 8 => 'Pies', 9 => 'Muñecas', 10 => 'Manos',
                11 => 'Anillo', 12 => 'Abalorio', 13 => 'Una Mano', 14 => 'Mano Secundaria (Escudo)',
                15 => 'A Distancia', 16 => 'Espalda', 17 => 'Dos Manos', 20 => 'Pecho',
                21 => 'Arma Principal', 22 => 'Mano Secundaria', 23 => 'Mano Secundaria',
                26 => 'Reliquia', 28 => 'Tabardo'
            ];

            if ($resItems) {
                while ($item = $resItems->fetch_assoc()) {
                    $sumaIlvl += (int)$item['ItemLevel'];
                    $totalItems++;

                    $tooltip = "";
                    $tipo = $invTypes[$item['InventoryType']] ?? 'Objeto';
                    $tooltip .= "<div style='color:#888; font-size:11px;'>".$tipo."</div>";

                    if ($item['armor'] > 0) {
                        $tooltip .= "<div style='color:#fff;'>{$item['armor']} de Armadura</div>";
                    }
                    if ($item['dmg_min1'] > 0 && $item['delay'] > 0) {
                        $velocidad = number_format($item['delay'] / 1000, 1);
                        $tooltip .= "<div style='color:#fff;'>{$item['dmg_min1']} - {$item['dmg_max1']} de Daño <span style='color:#888;'>(Velocidad $velocidad)</span></div>";
                    }
                    if ($item['RequiredLevel'] > 1) {
                        $tooltip .= "<div style='color:#888; font-size:11px;'>Requiere nivel {$item['RequiredLevel']}</div>";
                    }
                    
                    $tooltip .= "<div style='height: 5px;'></div>";

                    for ($i = 1; $i <= 5; $i++) {
                        $stType = $item["stat_type$i"];
                        $stVal = $item["stat_value$i"];
                        if ($stType > 0 && $stVal > 0) {
                            $stName = $statNames[$stType] ?? "Stat ($stType)";
                            $tooltip .= "<div style='color:#fff;'>+{$stVal} {$stName}</div>";
                        }
                    }

                    // DESCRIPCIÓN DEL ÍTEM (Naranja)
                    if (!empty($item['description'])) {
                        $tooltip .= "<div style='height: 5px;'></div>";
                        $tooltip .= "<div style='color:#ff8000; font-style: italic;'>".$item['description']."</div>";
                    }

                    foreach ($itemsEquipados as $slot => $id) {
                        if ($id == $item['entry']) {
                            $item['tooltip_html'] = $tooltip;
                            $equipo[$slot] = $item;
                        }
                    }
                }
            }
            if ($totalItems > 0) {
                $gearscore = round($sumaIlvl / $totalItems);
            }
        }
        
        // Profesiones
        $skillIds = [164,165,171,182,186,197,202,333,393,755];
        $placeholders = implode(',', array_fill(0, count($skillIds), '?'));
        $sqlProf = "SELECT skill, value, max FROM character_skills WHERE guid = ? AND skill IN ($placeholders)";
        $stmtProf = $conn->prepare($sqlProf);
        
        if ($stmtProf) {
            $types = str_repeat('i', count($skillIds) + 1);
            $params = array_merge([$guid], $skillIds);
            $stmtProf->bind_param($types, ...$params);
            $stmtProf->execute();
            $resProf = $stmtProf->get_result();
            
            $profNames = [
                164=>'Herrería',165=>'Peletería',171=>'Alquimia',182=>'Herboristería',
                186=>'Minería',197=>'Sastrería',202=>'Ingeniería',333=>'Encantamiento',
                393=>'Desuello',755=>'Joyería'
            ];
            
            while ($p = $resProf->fetch_assoc()) {
                $profesiones[] = [
                    'nombre'=>$profNames[$p['skill']] ?? 'Desconocida', 
                    'nivel'=>$p['value'], 
                    'max'=>$p['max']
                ];
            }
            $stmtProf->close();
        }
    }
}
$conn->close(); 
$worldConn->close();

// Funciones de colores
function colorCalidad($q) {
    $colores = [0=>'#9d9d9d', 1=>'#ffffff', 2=>'#1eff00', 3=>'#0070dd', 4=>'#a335ee', 5=>'#ff8000', 7=>'#e6cc80'];
    return $colores[(int)$q] ?? '#fff';
}
function calidadClase($q) {
    $map = [0=>'q-pobre',1=>'q-comun',2=>'q-bueno',3=>'q-raro',4=>'q-epico',5=>'q-legendario',7=>'q-heredado'];
    return $map[(int)$q] ?? '';
}
function colorDeClase($classId) {
    $colores = [1=>'#C79C6E', 2=>'#F58CBA', 3=>'#ABD473', 4=>'#FFF569', 5=>'#FFFFFF', 6=>'#C41F3B', 7=>'#0070DE', 8=>'#69CCF0', 9=>'#9482C9', 11=>'#FF7D0A'];
    return $colores[(int)$classId] ?? '#888888';
}
$colorClase = $personaje ? colorDeClase($personaje['class']) : '#888888';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Armería - <?= $personaje ? htmlspecialchars($personaje['name']) : 'Seleccionar' ?></title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, sans-serif; }
    body { background: #000; color: #fff; display: flex; justify-content: center; padding: 20px; }
    .contenedor { width: 100%; max-width: 800px; }
    
    /* SELECTOR */
    .selector { margin-bottom: 20px; display: flex; gap: 10px; }
    .selector select, .selector button { padding: 8px 15px; background: #111; border: 1px solid #333; color: #fff; border-radius: 4px; outline: none; }
    .selector button { background: #d4af37; color: #000; font-weight: bold; cursor: pointer; }
    
    /* CABECERA */
    .cabecera { display: flex; align-items: center; gap: 15px; border-bottom: 1px solid #222; padding-bottom: 15px; margin-bottom: 20px; }
    .escudo { width: 50px; height: 50px; border-radius: 50%; border: 2px solid #333; background-color: <?= $colorClase ?>; box-shadow: inset 0 0 15px rgba(0,0,0,0.8); }
    .info h1 { font-size: 22px; margin-bottom: 5px; color: #fff; }
    .info .detalles { font-size: 13px; color: #888; }
    
    /* MÉTRICAS */
    .metricas { display: flex; gap: 30px; margin-bottom: 20px; background: #111; padding: 12px 20px; border-radius: 4px; border: 1px solid #222; width: fit-content; }
    .metrica { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: bold; color: #888; }
    .metrica span { color: #d4af37; font-size: 16px; }

    /* MUÑECO */
    .zona-equipo { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; min-height: 420px; }
    .columna { display: flex; flex-direction: column; gap: 8px; }
    .columna.izq { align-items: flex-end; }
    .columna.der { align-items: flex-start; }
    
    /* RANURAS Y BORDES */
    .slot { width: 46px; height: 46px; background: #000; border: 1px solid #222; border-radius: 4px; display: flex; align-items: center; justify-content: center; position: relative; cursor: pointer; overflow: hidden;}
    
    /* COLORES DE RAREZA */
    .slot.q-pobre { border: 1px solid #9d9d9d; } 
    .slot.q-comun { border: 1px solid #ffffff; }
    .slot.q-bueno { border: 1px solid #1eff00; } 
    .slot.q-raro { border: 1px solid #0070dd; }
    .slot.q-epico { border: 1px solid #a335ee; } 
    .slot.q-legendario { border: 1px solid #ff8000; }

    /* ICONOS CSS */
    .icono-css { width: 26px; height: 26px; background-color: #666; position: relative; display: none; }
    .slot.activo .icono-css { display: block; }
    .icono-cabeza { border-radius: 12px 12px 4px 4px; clip-path: polygon(20% 0%, 80% 0%, 100% 40%, 100% 100%, 0% 100%, 0% 40%); }
    .icono-hombros { border-radius: 10px; clip-path: polygon(0 40%, 30% 0, 70% 0, 100% 40%, 100% 100%, 70% 80%, 30% 80%, 0 100%); }
    .icono-pecho { border-radius: 6px; clip-path: polygon(20% 0, 80% 0, 100% 20%, 100% 80%, 50% 100%, 0 80%, 0 20%); }
    .icono-manos { border-radius: 4px; clip-path: polygon(0 20%, 20% 0, 40% 10%, 60% 0, 80% 10%, 100% 0, 100% 100%, 0 100%); }
    .icono-cintura { height: 12px; margin-top: 7px; border-radius: 2px; border: 2px solid #333; }
    .icono-piernas { clip-path: polygon(10% 0, 90% 0, 100% 100%, 60% 100%, 50% 50%, 40% 100%, 0 100%); }
    .icono-pies { border-radius: 4px 4px 0 0; clip-path: polygon(10% 0, 90% 0, 90% 60%, 100% 100%, 80% 100%, 60% 60%, 40% 60%, 20% 100%, 0 100%, 10% 60%); }
    .icono-arma { width: 6px; height: 30px; border-radius: 2px 2px 0 0; margin: 0 auto; }
    .icono-arma::after { content: ''; position: absolute; bottom: 6px; left: -6px; width: 18px; height: 4px; background: #555; }
    .icono-escudo { clip-path: polygon(50% 0, 100% 15%, 100% 70%, 50% 100%, 0 70%, 0 15%); }
    .icono-anillo { border-radius: 50%; border: 5px solid #666; background: transparent; }
    .icono-otro { border-radius: 50%; }

    /* TOOLTIP */
    .slot .tooltip {
        visibility: hidden; opacity: 0; width: 240px; background: rgba(0, 0, 0, 0.95);
        border: 1px solid #c9a227; border-radius: 2px; padding: 10px; position: absolute; z-index: 100;
        bottom: 130%; left: 50%; transform: translateX(-50%); transition: opacity 0.2s;
        box-shadow: 0 0 10px rgba(0,0,0,1); font-size: 12px; line-height: 1.4; text-align: left;
        pointer-events: none; 
    }
    .slot:hover .tooltip { visibility: visible; opacity: 1; }
    .slot .tooltip::after { content: ""; position: absolute; top: 100%; left: 50%; margin-left: -6px; border-width: 6px; border-style: solid; border-color: #c9a227 transparent transparent transparent; }
    .columna.izq .tooltip { left: 0; transform: translateX(0); }
    .columna.izq .tooltip::after { left: 20px; }
    .columna.der .tooltip { left: auto; right: 0; transform: translateX(0); }
    .columna.der .tooltip::after { left: auto; right: 20px; }
    .tooltip-nombre { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
    
    /* CENTRO CON FACCIÓN */
    .centro { flex: 1; display: flex; align-items: center; justify-content: center; min-height: 400px; }
    .faccion-fondo { width: 220px; height: 220px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #222; box-shadow: inset 0 0 50px rgba(0,0,0,0.8); }
    .faccion-alianza { background: radial-gradient(circle, rgba(0, 112, 221, 0.4) 0%, rgba(0, 0, 0, 1) 70%); border-color: #0056b3; }
    .faccion-horda { background: radial-gradient(circle, rgba(196, 31, 59, 0.4) 0%, rgba(0, 0, 0, 1) 70%); border-color: #8a1528; }
    .escudo-faccion { width: 60px; height: 70px; clip-path: polygon(50% 0, 100% 20%, 100% 60%, 50% 100%, 0 60%, 0 20%); }
    .faccion-alianza .escudo-faccion { background-color: #0056b3; }
    .faccion-horda .escudo-faccion { background-color: #8a1528; }

    /* RESTO DE SECCIONES */
    .armas { display: flex; justify-content: center; gap: 8px; margin-bottom: 30px; }
    .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px 30px; border-top: 1px solid #222; padding-top: 20px; margin-bottom: 20px; }
    .stat { display: flex; align-items: center; gap: 12px; }
    .stat-info { display: flex; flex-direction: column; }
    .stat-valor { font-size: 14px; font-weight: bold; color: #fff; }
    .stat-nombre { font-size: 11px; color: #888; text-transform: uppercase; }
    
    .profesiones { border-top: 1px solid #222; padding-top: 15px; margin-bottom: 30px; }
    .profesiones h3 { font-size: 12px; color: #888; text-transform: uppercase; margin-bottom: 10px; }
    .caja-profesiones { display: flex; gap: 15px; flex-wrap: wrap; }
    .profesion { display: flex; align-items: center; gap: 10px; background: #111; border: 1px solid #222; border-radius: 4px; padding: 8px 15px 8px 8px; min-width: 180px; }
    .profesion-icono { width: 32px; height: 32px; background-color: #333; border-radius: 4px; border: 1px solid #555; display:flex; align-items:center; justify-content:center; font-size:10px; color:#888; }
    .profesion-nombre { font-size: 13px; font-weight: bold; color: #fff;}
    .profesion-nivel { font-size: 11px; color: #888; }
</style>
</head>
<body>
<div class="contenedor">
    <form method="GET" class="selector">
        <select name="guid" required>
            <option value="">Seleccionar personaje...</option>
            <?php foreach ($personajes as $p): ?>
                <option value="<?= $p['guid'] ?>" <?= (isset($_GET['guid']) && $_GET['guid']==$p['guid'])?'selected':'' ?>>
                    <?= htmlspecialchars($p['name']) ?> (Nv. <?= $p['level'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Ver</button>
    </form>

    <?php if ($personaje): ?>
    <div class="cabecera">
        <div class="escudo"></div>
        <div class="info">
            <h1><?= htmlspecialchars($personaje['name']) ?></h1>
            <div class="detalles">Nivel <?= $personaje['level'] ?> · Clase ID <?= $personaje['class'] ?> · Raza ID <?= $personaje['race'] ?></div>
        </div>
    </div>

    <div class="metricas">
        <div class="metrica">Logros: <span><?= number_format($logros) ?></span></div>
        <div class="metrica">Nivel de Objeto: <span><?= $gearscore ?></span></div>
    </div>

    <?php 
    function dibujarSlot($slotNum, $equipo) {
        $item = $equipo[$slotNum] ?? null;
        $calidad = $item ? calidadClase($item['Quality']) : '';
        $colorNombre = $item ? colorCalidad($item['Quality']) : '#fff';
        
        $iconos = [
            0 => 'icono-cabeza', 1 => 'icono-otro', 2 => 'icono-hombros', 3 => 'icono-pecho',
            4 => 'icono-pecho', 5 => 'icono-cintura', 6 => 'icono-piernas', 7 => 'icono-pies',
            8 => 'icono-otro', 9 => 'icono-manos', 10 => 'icono-anillo', 11 => 'icono-anillo',
            12 => 'icono-otro', 13 => 'icono-otro', 14 => 'icono-hombros', 15 => 'icono-arma',
            16 => 'icono-escudo', 17 => 'icono-arma', 18 => 'icono-pecho'
        ];
        $claseIcono = $iconos[$slotNum] ?? 'icono-otro';
        $claseActivo = $item ? 'activo' : '';

        echo '<div class="slot '.$calidad.' '.$claseActivo.'">';
        echo '<div class="icono-css '.$claseIcono.'"></div>';

        if ($item) {
            echo '<div class="tooltip">';
            echo '<div class="tooltip-nombre" style="color:'.$colorNombre.'">'.$item['name'].'</div>';
            echo $item['tooltip_html']; 
            echo '</div>';
        }
        echo '</div>';
    }
    ?>

    <div class="zona-equipo">
        <div class="columna izq">
            <?php 
            for ($i=0; $i<=8; $i++) { if($i==3) continue; dibujarSlot($i, $equipo); }
            dibujarSlot(3, $equipo);
            dibujarSlot(18, $equipo);
            ?>
        </div>

        <div class="centro">
            <div class="faccion-fondo <?= $esHorda ? 'faccion-horda' : 'faccion-alianza' ?>">
                <div class="escudo-faccion"></div>
            </div>
        </div>

        <div class="columna der">
            <?php for ($i=9; $i<=14; $i++) dibujarSlot($i, $equipo); ?>
        </div>
    </div>

    <div class="armas">
        <?php foreach ([15,17,16] as $slotNum) dibujarSlot($slotNum, $equipo); ?>
    </div>

    <div class="stats">
        <div class="stat"><div class="stat-info"><span class="stat-valor"><?= number_format($stats['maxhealth'] ?? 0) ?></span><span class="stat-nombre">Salud</span></div></div>
        <div class="stat"><div class="stat-info"><span class="stat-valor"><?= number_format($stats['maxpower1'] ?? 0) ?></span><span class="stat-nombre">Maná</span></div></div>
        <div class="stat"><div class="stat-info"><span class="stat-valor"><?= $stats['intellect'] ?? 0 ?></span><span class="stat-nombre">Intelecto</span></div></div>
        <div class="stat"><div class="stat-info"><span class="stat-valor"><?= $stats['stamina'] ?? 0 ?></span><span class="stat-nombre">Aguante</span></div></div>
        <div class="stat"><div class="stat-info"><span class="stat-valor"><?= number_format(($stats['critPct'] ?? 0), 2) ?>%</span><span class="stat-nombre">Golpe Crítico</span></div></div>
        <div class="stat"><div class="stat-info"><span class="stat-valor"><?= number_format(($stats['attackPower'] ?? 0)) ?></span><span class="stat-nombre">Poder de Ataque</span></div></div>
    </div>

    <div class="profesiones">
        <h3>Profesiones</h3>
        <div class="caja-profesiones">
            <?php if (empty($profesiones)): ?>
                <div style="color:#555;font-style:italic;font-size:13px;">Sin profesiones aprendidas.</div>
            <?php else: ?>
                <?php foreach ($profesiones as $prof): ?>
                    <div class="profesion">
                        <div class="profesion-icono">P</div>
                        <div class="profesion-info">
                            <span class="profesion-nombre"><?= $prof['nombre'] ?></span>
                            <span class="profesion-nivel"><?= $prof['nivel'] ?> / <?= $prof['max'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
        <p style="color:#555;text-align:center;padding:40px;">Selecciona un personaje para ver su armería.</p>
    <?php endif; ?>

</div>
</body>
</html>
