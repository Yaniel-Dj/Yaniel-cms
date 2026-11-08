<?php
require_once __DIR__ . '/config.php';

// Conexión a la base de datos de personajes y del mundo
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, CHAR_DB_NAME, (int)DB_PORT);
$worldConn = new mysqli(DB_HOST, DB_USER, DB_PASS, WORLD_DB_NAME, (int)DB_PORT);
$conn->set_charset("utf8mb4");
$worldConn->set_charset("utf8mb4");

// Obtenemos el ID del personaje desde la URL
$guid = (int)($_GET['guid'] ?? 0);
if ($guid === 0) die("Personaje no válido.");

// Buscamos los datos del personaje
$stmt = $conn->prepare("SELECT name, class, race, level FROM characters WHERE guid = ?");
$stmt->bind_param("i", $guid);
$stmt->execute();
$personaje = $stmt->get_result()->fetch_assoc();

if (!$personaje) die("Personaje no encontrado.");

// Saber si es Horda (rojo) o Alianza (azul)
$esHorda = in_array((int)$personaje['race'], [2, 5, 6, 8, 10]);

// Función para pintar el borde según la calidad del ítem (lo que pediste)
function calidadClase($q) {
    $map = [0=>'q-pobre',1=>'q-comun',2=>'q-bueno',3=>'q-raro',4=>'q-epico',5=>'q-legendario'];
    return $map[(int)$q] ?? '';
}

// Función que dibuja cada ranura
function dibujarSlot($slotNum, $item, $slotBgImages) {
    $calidad = $item ? calidadClase($item['Quality']) : '';
    $bgImage = isset($slotBgImages[$slotNum]) ? $slotBgImages[$slotNum] . '.gif' : '';
    
    echo '<div class="gear-slot '.$calidad.'">';
    
    // Dibuja la silueta .gif
    if ($bgImage) {
        echo '<img src="Img/armeria/slots/'.$bgImage.'" class="slot-placeholder">';
    }

    // Si hay un ítem equipado, mostramos el tooltip al pasar el ratón
    if ($item) {
        echo '<div class="tooltip">';
        echo '<div style="color:#fff; font-weight:bold;">'.$item['name'].'</div>';
        echo '<div style="color:#888; font-size:11px;">Nivel '.$item['ItemLevel'].'</div>';
        echo '</div>';
    }
    echo '</div>';
}

// Array de imágenes .gif para cada ranura
$slotBgImages = [
    0 => 'head', 1 => 'neck', 2 => 'shoulder', 14 => 'back',
    4 => 'chest', 3 => 'shirt', 18 => 'tabard', 8 => 'wrist',
    9 => 'hands', 5 => 'waist', 6 => 'legs', 7 => 'feet',
    10 => 'ring', 11 => 'ring', 12 => 'trinket', 13 => 'trinket',
    15 => 'mainhand', 16 => 'offhand', 17 => 'ranged'
];

// Aquí iría la lógica para leer el equipo (te la dejo simplificada para que veas cómo se une)
// ... (Código para buscar en character_inventory e item_template) ...
// Supongamos que ya lo tienes en un array llamado $equipo (donde la clave es el número de slot)

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= htmlspecialchars($personaje['name']) ?></title>
    <link rel="stylesheet" href="css/armeria.css">
</head>
<body>
    <div class="container">
        <!-- Volver al buscador -->
        <a href="armeria.php" style="color:#d4af37; text-decoration:none; margin-bottom:20px; display:inline-block;">← Volver al buscador</a>

        <!-- Cabecera del personaje -->
        <div class="char-header">
            <img class="char-avatar" src="Img/armeria/avatares/<?= $personaje['name'] ?>.gif" onerror="this.src='Img/armeria/avatares/default.gif'">
            <div>
                <h2 class="char-title"><?= htmlspecialchars($personaje['name']) ?></h2>
                <div class="char-subtitle">Nivel <?= $personaje['level'] ?></div>
            </div>
            <div style="margin-left: auto;">
                <span class="faction-<?= $esHorda ? '2' : '1' ?>"></span>
            </div>
        </div>

        <!-- Aquí van las 8 - 8 - 3 -->
        <div class="equipment-wrapper">
            <div class="equipment-row">
                <div class="gear-column left">
                    <?php 
                    // 8 ranuras a la izquierda
                    for ($i = 0; $i <= 8; $i++) { if($i==3) continue; dibujarSlot($i, $equipo[$i] ?? null, $slotBgImages); }
                    dibujarSlot(3, $equipo[3] ?? null, $slotBgImages); // Camisa
                    dibujarSlot(18, $equipo[18] ?? null, $slotBgImages); // Tabardo
                    ?>
                </div>
                
                <div class="gear-center">
                    <div class="faction-bg <?= $esHorda ? 'horda' : 'alianza' ?>"></div>
                </div>

                <div class="gear-column right">
                    <?php 
                    // 8 ranuras a la derecha
                    for ($i = 9; $i <= 14; $i++) { dibujarSlot($i, $equipo[$i] ?? null, $slotBgImages); }
                    ?>
                </div>
            </div>

            <!-- 3 ranuras abajo -->
            <div class="bottom-slots">
                <?php 
                dibujarSlot(15, $equipo[15] ?? null, $slotBgImages); 
                dibujarSlot(16, $equipo[16] ?? null, $slotBgImages); 
                dibujarSlot(17, $equipo[17] ?? null, $slotBgImages); 
                ?>
            </div>
        </div>
    </div>
</body>
</html>
