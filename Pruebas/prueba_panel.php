<?php
// Incluimos tu configuración (asegúrate de que esté en la misma carpeta)
require_once __DIR__ . '/config.php';

// Conexión a la base de datos de personajes
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, CHAR_DB_NAME, (int)DB_PORT);

if ($conn->connect_error) {
    die("Error de conexión a la base de datos.");
}
$conn->set_charset("utf8");

// Sacamos los últimos 5 personajes creados (puedes cambiar el LIMIT si quieres más)
$sqlNuevos = "SELECT guid, name, race, class, level FROM characters ORDER BY creation_date DESC LIMIT 5";
$resNuevos = $conn->query($sqlNuevos);

// Diccionarios para traducir IDs
$razas = [
    1=>'Humano', 2=>'Orco', 3=>'Enano', 4=>'Elfo de la Noche', 5=>'No-muerto', 
    6=>'Tauren', 7=>'Gnomo', 8=>'Trol', 10=>'Elfo de Sangre', 11=>'Draenei'
];
$clases = [
    1=>'Guerrero', 2=>'Paladín', 3=>'Cazador', 4=>'Pícaro', 5=>'Sacerdote', 
    6=>'Caballero de la Muerte', 7=>'Chamán', 8=>'Mago', 9=>'Brujo', 11=>'Druida'
];
$coloresClase = [
    1=>'#C79C6E', 2=>'#F58CBA', 3=>'#ABD473', 4=>'#FFF569', 5=>'#FFFFFF', 
    6=>'#C41F3B', 7=>'#0070DE', 8=>'#69CCF0', 9=>'#9482C9', 11=>'#FF7D0A'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevos Aventureros - <?= htmlspecialchars(SERVER_NAME) ?></title>
    <style>
        /* Fondo general */
        body {
            background-color: #03060a;
            background-image: radial-gradient(circle at center, #0d1829 0%, #03060a 100%);
            color: #fff;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        /* =======================================================
           EL MARCO PRINCIPAL (Efecto Piedra/Metal Biselado)
           ======================================================= */
        .panel-nuevos-aventureros {
            width: 100%;
            max-width: 850px;
            background: #060b14;
            border: 3px solid #b8860b;
            border-radius: 4px;
            position: relative;
            padding: 35px;
            box-sizing: border-box;
            box-shadow: 
                inset 0 0 0 1px #2a1e00, 
                inset 0 0 20px rgba(0,0,0,1), 
                0 0 0 1px #000, 
                0 0 30px rgba(184, 134, 11, 0.3);
        }

        /* Esquinas Biseladas */
        .panel-nuevos-aventureros::before,
        .panel-nuevos-aventureros::after {
            content: ''; position: absolute; width: 20px; height: 20px;
            border: 2px solid #d4af37; z-index: 10;
        }
        .panel-nuevos-aventureros::before { top: -3px; left: -3px; border-right: none; border-bottom: none; }
        .panel-nuevos-aventureros::after { bottom: -3px; right: -3px; border-left: none; border-top: none; }

        /* Adorno superior */
        .adorno-superior {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            color: #69CCF0;
            font-size: 30px;
            text-shadow: 0 0 20px #69CCF0, 0 0 10px #fff;
            background: #060b14;
            padding: 0 15px;
            line-height: 1;
            z-index: 11;
        }

        /* Cabecera */
        .pna-cabecera {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #2a3a52;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .pna-cabecera h2 {
            font-size: 24px;
            font-weight: 400;
            letter-spacing: 2px;
            color: #e6e6e6;
            margin: 0;
            font-family: 'Georgia', serif;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.8);
        }

        .pna-cabecera .unete {
            font-size: 13px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Lista de personajes */
        .pna-lista {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* =======================================================
           FILA DE PERSONAJE (Efecto Hundido)
           ======================================================= */
        .pna-fila {
            display: flex;
            align-items: center;
            background: #0a101d;
            border: 1px solid #1c2638;
            border-radius: 4px;
            padding: 12px 20px;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.8);
        }

        .pna-fila:hover {
            border-color: #b8860b;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.8), 0 0 10px rgba(184, 134, 11, 0.2);
            background: #0d1525;
        }

        /* El Avatar */
        .pna-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #000;
            margin-right: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 600;
            flex-shrink: 0;
            border: 2px solid; 
            box-shadow: inset 0 0 10px rgba(0,0,0,1);
            text-shadow: 1px 1px 2px #000;
        }

        /* Info del personaje */
        .pna-info { flex-grow: 1; }
        
        .pna-nombre { 
            font-size: 18px; 
            font-weight: 500; 
            color: #fff; 
            margin-bottom: 2px; 
            text-shadow: 1px 1px 2px #000;
        }
        
        .pna-detalles { 
            font-size: 13px; 
            color: #7a8596; 
        }

        /* Nivel a la derecha */
        .pna-nivel { 
            font-size: 15px; 
            color: #a0aab8; 
            font-weight: 500;
        }
    </style>
</head>
<body>

    <div class="panel-nuevos-aventureros">
        <!-- El diamante superior -->
        <div class="adorno-superior">◆</div>

        <div class="pna-cabecera">
            <h2>Nuevos Aventureros</h2>
            <span class="unete">Únete 🔒</span>
        </div>

        <div class="pna-lista">
            <?php if ($resNuevos && $resNuevos->num_rows > 0): ?>
                <?php while($p = $resNuevos->fetch_assoc()): ?>
                    <?php 
                        $raza = $razas[$p['race']] ?? 'Raza Desconocida'; 
                        $clase = $clases[$p['class']] ?? 'Clase Desconocida';
                        $color = $coloresClase[$p['class']] ?? '#555';
                        $inicial = substr($p['name'], 0, 1);
                    ?>
                    
                    <!-- Enlazamos a la armería pasando el GUID -->
                    <a href="armeria.php?guid=<?= $p['guid'] ?>" class="pna-fila">
                        <div class="pna-avatar" style="border-color: <?= $color ?>; color: <?= $color ?>;">
                            <?= $inicial ?>
                        </div>
                        <div class="pna-info">
                            <div class="pna-nombre"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="pna-detalles"><?= $raza ?> · <?= $clase ?></div>
                        </div>
                        <div class="pna-nivel">Nv. <?= $p['level'] ?></div>
                    </a>

                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:#666; padding: 20px;">Aún no hay personajes creados en el servidor.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
<?php
if (isset($conn)) { $conn->close(); }
?>
