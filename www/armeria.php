<?php
require_once __DIR__ . '/config.php';

// Conexiones
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, CHAR_DB_NAME, (int)DB_PORT);
$worldConn = new mysqli(DB_HOST, DB_USER, DB_PASS, WORLD_DB_NAME, (int)DB_PORT);

if ($conn->connect_error || $worldConn->connect_error) {
    die("<p style='color:red; text-align:center;'>Error de conexión a la base de datos.</p>");
}
$conn->set_charset("utf8mb4"); 
$worldConn->set_charset("utf8mb4");

// Datos por defecto
$personaje = null;
$equipo = [];
$stats = null;
$esHorda = false;

// Si se selecciona un personaje
if (isset($_GET['guid']) && is_numeric($_GET['guid'])) {
    $guid = (int)$_GET['guid'];

    // 1. Datos del personaje
    $stmt = $conn->prepare("SELECT guid, name, class, race, level FROM characters WHERE guid = ?");
    $stmt->bind_param("i", $guid);
    $stmt->execute();
    $personaje = $stmt->get_result()->fetch_assoc();
    
    if ($personaje) {
        $esHorda = in_array((int)$personaje['race'], [2, 5, 6, 8, 10]);

        // 2. Stats
        $stmtStats = $conn->prepare("SELECT maxhealth, maxpower1, strength, agility, stamina, intellect, critPct, attackPower FROM character_stats WHERE guid = ?");
        $stmtStats->bind_param("i", $guid);
        $stmtStats->execute();
        $stats = $stmtStats->get_result()->fetch_assoc() ?? [];

        // 3. Equipo
        $sqlEq = "SELECT ci.slot, ii.itemEntry FROM character_inventory ci JOIN item_instance ii ON ci.item = ii.guid WHERE ci.guid = ? AND ci.bag = 0 AND ci.slot BETWEEN 0 AND 18";
        $stmtEq = $conn->prepare($sqlEq);
        $stmtEq->bind_param("i", $guid);
        $stmtEq->execute();
        $resEq = $stmtEq->get_result();
        
        $itemsEquipados = [];
        while ($row = $resEq->fetch_assoc()) { 
            $itemsEquipados[(int)$row['slot']] = (int)$row['itemEntry']; 
        }

        if (!empty($itemsEquipados)) {
            $ids = implode(',', array_unique(array_values($itemsEquipados)));
            $sqlItems = "SELECT entry, name, Quality, icon, ItemLevel, RequiredLevel, armor, dmg_min1, dmg_max1, delay, stat_type1, stat_value1, stat_type2, stat_value2, stat_type3, stat_value3, stat_type4, stat_value4, stat_type5, stat_value5 FROM item_template WHERE entry IN ($ids)";
            $resItems = $worldConn->query($sqlItems);
            
            $statNames = [3 => 'Agilidad', 4 => 'Fuerza', 5 => 'Intelecto', 6 => 'Espíritu', 7 => 'Aguante', 32 => 'Golpe Crítico', 36 => 'Celeridad', 38 => 'Poder de Ataque', 43 => 'Poder con Hechizos'];

            if ($resItems) {
                while ($item = $resItems->fetch_assoc()) {
                    // Construir Tooltip
                    $tooltip = "<div style='color:#888; font-size:11px;'>{$item['ItemLevel']} Nivel de Objeto</div>";
                    if ($item['armor'] > 0) $tooltip .= "<div style='color:#fff;'>{$item['armor']} de Armadura</div>";
                    if ($item['dmg_min1'] > 0 && $item['delay'] > 0) {
                        $vel = number_format($item['delay'] / 1000, 1);
                        $tooltip .= "<div style='color:#fff;'>{$item['dmg_min1']} - {$item['dmg_max1']} de Daño <span style='color:#888;'>(Vel. $vel)</span></div>";
                    }
                    for ($i = 1; $i <= 5; $i++) {
                        $stType = $item["stat_type$i"]; $stVal = $item["stat_value$i"];
                        if ($stType > 0 && $stVal > 0) {
                            $stName = $statNames[$stType] ?? "Stat ($stType)";
                            $tooltip .= "<div style='color:#fff;'>+{$stVal} {$stName}</div>";
                        }
                    }
                    $item['tooltip_html'] = $tooltip;

                    // Asignar al slot
                    foreach ($itemsEquipados as $slot => $id) {
                        if ($id == $item['entry']) $equipo[$slot] = $item;
                    }
                }
            }
        }
    }
}
$conn->close(); 
$worldConn->close();

// Funciones de colores
function colorDeClase($classId) {
    $colores = [1=>'#C79C6E', 2=>'#F58CBA', 3=>'#ABD473', 4=>'#FFF569', 5=>'#FFFFFF', 6=>'#C41F3B', 7=>'#0070DE', 8=>'#69CCF0', 9=>'#9482C9', 11=>'#FF7D0A'];
    return $colores[(int)$classId] ?? '#888888';
}
function calidadClase($q) {
    $map = [0=>'q-pobre',1=>'q-comun',2=>'q-bueno',3=>'q-raro',4=>'q-epico',5=>'q-legendario'];
    return $map[(int)$q] ?? '';
}
function colorCalidad($q) {
    $colores = [0=>'#9d9d9d', 1=>'#ffffff', 2=>'#1eff00', 3=>'#0070dd', 4=>'#a335ee', 5=>'#ff8000'];
    return $colores[(int)$q] ?? '#fff';
}

// Función para dibujar cada Slot
function dibujarSlot($slotNum, $equipo) {
    $item = $equipo[$slotNum] ?? null;
    $calidad = $item ? calidadClase($item['Quality']) : '';
    $colorNombre = $item ? colorCalidad($item['Quality']) : '#fff';
    
    echo '<div class="gear-slot '.$calidad.'">';
    if ($item) {
        // RUTA LOCAL A TUS ICONOS (Asegúrate de que la carpeta se llame así)
        $icono = 'Img/armeria/iconos/' . $item['icon'] . '.jpg';
        echo '<img src="'.$icono.'" onerror="this.src=\'Img/armeria/iconos/inv_misc_questionmark.jpg\'">';
        echo '<div class="tooltip">';
        echo '<div style="color:'.$colorNombre.'; font-size:14px; font-weight:bold; margin-bottom:4px;">'.$item['name'].'</div>';
        echo $item['tooltip_html']; 
        echo '</div>';
    }
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armería - <?= $personaje ? htmlspecialchars($personaje['name']) : 'Inicio' ?></title>
    <link rel="stylesheet" href="css/armeria.css">
</head>
<body>

<div class="container">
    <!-- BUSCADOR -->
    <div class="page-subbody">
        <form method="GET" action="armeria.php" class="input-group">
            <select class="form-control" name="guid" required onchange="this.form.submit()">
                <option value="">Selecciona un personaje...</option>
                <?php 
                // Re-conectamos rápido solo para el dropdown
                $conn2 = new mysqli(DB_HOST, DB_USER, DB_PASS, CHAR_DB_NAME, (int)DB_PORT);
                $resP = $conn2->query("SELECT guid, name, level FROM characters ORDER BY level DESC LIMIT 100");
                while($p = $resP->fetch_assoc()){
                    $sel = (isset($_GET['guid']) && $_GET['guid'] == $p['guid']) ? 'selected' : '';
                    echo "<option value='{$p['guid']}' $sel>{$p['name']} (Nv. {$p['level']})</option>";
                }
                $conn2->close();
                ?>
            </select>
            <button type="submit" class="nice_button">Ver Armería</button>
        </form>
    </div>

    <?php if ($personaje): ?>
    <!-- VISTA DEL PERSONAJE -->
    <div id="character_view" style="display:block;">
        <div class="char-header">
            <!-- Avatar Local -->
            <img class="char-avatar" src="Img/armeria/avatares/<?= $personaje['name'] ?>.gif" onerror="this.src='Img/armeria/avatares/default.gif'">
            <div>
                <h2 class="char-title" style="color: <?= colorDeClase($personaje['class']) ?>"><?= htmlspecialchars($personaje['name']) ?></h2>
                <div class="char-subtitle">Nivel <?= $personaje['level'] ?> · Raza ID <?= $personaje['race'] ?></div>
            </div>
            <div style="margin-left: auto;">
                <span class="<?= $esHorda ? 'faction-2' : 'faction-1' ?>"></span>
            </div>
        </div>

        <div class="equipment-wrapper">
            <div class="equipment-row">
                <!-- Columna Izquierda (8) -->
                <div class="gear-column left">
                    <?php dibujarSlot(0, $equipo); dibujarSlot(1, $equipo); dibujarSlot(2, $equipo); dibujarSlot(14, $equipo); ?>
                    <?php dibujarSlot(4, $equipo); dibujarSlot(3, $equipo); dibujarSlot(18, $equipo); dibujarSlot(8, $equipo); ?>
                </div>
                
                <!-- Escudo Central -->
                <div class="gear-center">
                    <div class="faction-bg <?= $esHorda ? 'horda' : 'alianza' ?>"></div>
                </div>

                <!-- Columna Derecha (8) -->
                <div class="gear-column right">
                    <?php dibujarSlot(9, $equipo); dibujarSlot(5, $equipo); dibujarSlot(6, $equipo); dibujarSlot(7, $equipo); ?>
                    <?php dibujarSlot(10, $equipo); dibujarSlot(11, $equipo); dibujarSlot(12, $equipo); dibujarSlot(13, $equipo); ?>
                </div>
            </div>

            <!-- Fila Inferior (3) -->
            <div class="bottom-slots">
                <?php dibujarSlot(15, $equipo); dibujarSlot(16, $equipo); dibujarSlot(17, $equipo); ?>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-box"><span class="stat-value"><?= number_format($stats['maxhealth'] ?? 0) ?></span><span class="stat-label">Salud</span></div>
            <div class="stat-box"><span class="stat-value"><?= number_format($stats['attackPower'] ?? 0) ?></span><span class="stat-label">Poder de Ataque</span></div>
            <div class="stat-box"><span class="stat-value"><?= number_format($stats['strength'] ?? 0) ?></span><span class="stat-label">Fuerza</span></div>
            <div class="stat-box"><span class="stat-value"><?= number_format($stats['critPct'] ?? 0, 2) ?>%</span><span class="stat-label">Crítico</span></div>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
