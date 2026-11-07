<?php
// Verificamos si la conexión a la base de datos ya existe (por si lo llamas desde index.php).
// Si no existe, la creamos usando las constantes de tu config.php.
if (!isset($conn)) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, CHAR_DB_NAME, (int)DB_PORT);
    if ($conn->connect_error) {
        die("Error de conexión a la base de datos.");
    }
    $conn->set_charset("utf8");
}

// Diccionarios de AzerothCore
$razas = [1=>'Humano', 2=>'Orco', 3=>'Enano', 4=>'Elfo de la Noche', 5=>'No-muerto', 6=>'Tauren', 7=>'Gnomo', 8=>'Trol', 10=>'Elfo de Sangre', 11=>'Draenei'];
$clases = [1=>'Guerrero', 2=>'Paladín', 3=>'Cazador', 4=>'Pícaro', 5=>'Sacerdote', 6=>'Caballero de la Muerte', 7=>'Chamán', 8=>'Mago', 9=>'Brujo', 11=>'Druida'];
$coloresClase = [1=>'#C79C6E', 2=>'#F58CBA', 3=>'#ABD473', 4=>'#FFF569', 5=>'#FFFFFF', 6=>'#C41F3B', 7=>'#0070DE', 8=>'#69CCF0', 9=>'#9482C9', 11=>'#FF7D0A'];

// 1. TOP HERMANDADES (Top 2 por miembros)
$hermandades = [];
$resH = $conn->query("SELECT g.name, COUNT(gm.guid) as miembros FROM guild g LEFT JOIN guild_member gm ON g.guildid = gm.guildid GROUP BY g.guildid ORDER BY miembros DESC LIMIT 2");
if($resH) { while($row = $resH->fetch_assoc()) $hermandades[] = $row; }

// 2. MAYOR NIVEL (Top 2)
$topNivel = [];
$resN = $conn->query("SELECT name, race, class, level FROM characters ORDER BY level DESC LIMIT 2");
if($resN) { while($row = $resN->fetch_assoc()) $topNivel[] = $row; }

// 3. TOP JcJ (Arena 2v2 - Usando matchmakerRating de AzerothCore)
$topPvP = [];
$resP = $conn->query("SELECT c.name, c.race, c.class, a.matchmakerRating FROM character_arena_stats a JOIN characters c ON a.guid = c.guid WHERE a.slot = 0 ORDER BY a.matchmakerRating DESC LIMIT 2");
if($resP) { while($row = $resP->fetch_assoc()) $topPvP[] = $row; }

// 4. CLASES MÁS JUGADAS (Top 5)
$statsClases = [];
$totalPersonajes = 0;
$resC = $conn->query("SELECT class, COUNT(*) as cantidad FROM characters GROUP BY class ORDER BY cantidad DESC");
if($resC) { 
    while($row = $resC->fetch_assoc()) { 
        $statsClases[] = $row; 
        $totalPersonajes += $row['cantidad'];
    } 
}
?>

<!-- ========================================== -->
<!-- ESTILOS CSS DE LOS PANELES                 -->
<!-- ========================================== -->
<style>
    .grid-paneles { 
        display: grid; 
        grid-template-columns: repeat(2, 1fr); 
        gap: 30px; 
        width: 100%; 
        max-width: 1000px; 
        margin: 40px auto; 
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    @media (max-width: 768px) { 
        .grid-paneles { grid-template-columns: 1fr; } 
    }

    /* Marco Dorado Biselado */
    .panel {
        background: #060b14; 
        border: 2px solid #b8860b; 
        border-radius: 6px; 
        position: relative; 
        padding: 25px;
        box-sizing: border-box; 
        box-shadow: inset 0 0 0 1px #2a1e00, inset 0 0 20px rgba(0,0,0,1), 0 0 0 1px #000, 0 0 25px rgba(184, 134, 11, 0.2);
        min-height: 280px; 
        display: flex; 
        flex-direction: column;
    }
    
    /* Esquinas Biseladas */
    .panel::before, .panel::after { 
        content: ''; position: absolute; width: 16px; height: 16px; border: 2px solid #d4af37; z-index: 10; 
    }
    .panel::before { top: -2px; left: -2px; border-right: none; border-bottom: none; }
    .panel::after { bottom: -2px; right: -2px; border-left: none; border-top: none; }
    
    /* Adorno superior con resplandor azul */
    .adorno-superior { 
        position: absolute; top: -16px; left: 50%; transform: translateX(-50%); 
        color: #69CCF0; font-size: 24px; text-shadow: 0 0 15px #69CCF0, 0 0 5px #fff; 
        background: #060b14; padding: 0 12px; line-height: 1; z-index: 11; 
    }
    
    /* Cabecera del panel */
    .panel-cabecera { 
        display: flex; justify-content: space-between; align-items: center; 
        border-bottom: 1px solid #2a3a52; padding-bottom: 12px; margin-bottom: 15px; 
    }
    .panel-titulo-grupo { display: flex; align-items: center; gap: 12px; }
    
    /* Icono del título (fondo dorado) */
    .panel-icono { 
        width: 32px; height: 32px; background: #c9a227; border-radius: 4px; 
        display: flex; align-items: center; justify-content: center; font-size: 16px; color: #000; 
        box-shadow: 0 0 10px rgba(201, 162, 39, 0.4); 
    }
    
    .panel h2 { 
        font-size: 18px; font-weight: 400; letter-spacing: 1px; color: #e6e6e6; 
        margin: 0; font-family: 'Georgia', serif; text-transform: uppercase; 
    }

    /* Mensajes de panel vacío */
    .panel-vacio { 
        display: flex; flex-direction: column; align-items: center; justify-content: center; 
        flex-grow: 1; color: #888; text-align: center; font-size: 14px; line-height: 1.6; 
    }
    .icono-vacio { font-size: 24px; margin-bottom: 10px; color: #444; }

    /* Lista de Ranking */
    .ranking-lista { display: flex; flex-direction: column; gap: 8px; }
    .ranking-item { display: flex; align-items: center; background: #0d1525; border-radius: 4px; padding: 8px 12px; }
    .ranking-num { 
        width: 24px; height: 24px; border-radius: 50%; background: #1f2937; color: #fff; 
        font-size: 12px; font-weight: bold; display: flex; align-items: center; justify-content: center; margin-right: 10px; 
    }
    .ranking-info { flex-grow: 1; }
    .ranking-nombre { font-size: 14px; font-weight: bold; color: #fff; }
    .ranking-sub { font-size: 11px; color: #888; }
    .ranking-nivel { text-align: right; }
    .ranking-nivel-num { font-size: 16px; font-weight: bold; color: #fff; }
    .ranking-nivel-txt { font-size: 9px; color: #888; text-transform: uppercase; }

    /* Lista de Clases (Barras de progreso) */
    .clases-lista { display: flex; flex-direction: column; gap: 10px; flex-grow: 1; justify-content: center; }
    .clase-item { display: flex; align-items: center; gap: 10px; }
    .clase-icono { 
        width: 24px; height: 24px; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; 
    }
    .clase-nombre { width: 90px; font-size: 13px; color: #ccc; }
    .clase-barra-fondo { flex-grow: 1; height: 8px; background: #1f2937; border-radius: 4px; overflow: hidden; }
    .clase-barra-relleno { height: 100%; border-radius: 4px; transition: width 1s ease-out; }
    .clase-cantidad { width: 20px; text-align: right; font-size: 13px; color: #888; font-weight: bold; }
</style>

<!-- ========================================== -->
<!-- ESTRUCTURA HTML DE LOS PANELES             -->
<!-- ========================================== -->
<div class="grid-paneles">
    
    <!-- 1. TOP HERMANDADES -->
    <div class="panel">
        <div class="adorno-superior">◆</div>
        <div class="panel-cabecera">
            <div class="panel-titulo-grupo"><div class="panel-icono">🛡️</div><h2>Top Hermandades</h2></div>
        </div>
        <?php if (empty($hermandades)): ?>
            <div class="panel-vacio"><div class="icono-vacio">⚔️</div><p>Todavía no hay hermandades. Funda la<br>primera y aparecerá aquí.</p></div>
        <?php else: ?>
            <div class="ranking-lista">
                <?php $i=1; foreach($hermandades as $h): ?>
                    <div class="ranking-item" style="border-left: 3px solid #c9a227;">
                        <div class="ranking-num"><?= $i++ ?></div>
                        <div class="ranking-info">
                            <div class="ranking-nombre"><?= htmlspecialchars($h['name']) ?></div>
                            <div class="ranking-sub">Miembros</div>
                        </div>
                        <div class="ranking-nivel">
                            <div class="ranking-nivel-num"><?= $h['miembros'] ?></div>
                            <div class="ranking-nivel-txt">Pjs</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 2. MAYOR NIVEL -->
    <div class="panel">
        <div class="adorno-superior">◆</div>
        <div class="panel-cabecera">
            <div class="panel-titulo-grupo"><div class="panel-icono">👑</div><h2>Mayor Nivel</h2></div>
        </div>
        <?php if (empty($topNivel)): ?>
            <div class="panel-vacio"><div class="icono-vacio">⚔️</div><p>No hay personajes.</p></div>
        <?php else: ?>
            <div class="ranking-lista">
                <?php $i=1; foreach($topNivel as $p): 
                    $raza = $razas[$p['race']] ?? 'Desc'; $clase = $clases[$p['class']] ?? 'Desc';
                    $color = $coloresClase[$p['class']] ?? '#555';
                ?>
                    <div class="ranking-item" style="border-left: 3px solid <?= $color ?>;">
                        <div class="ranking-num"><?= $i++ ?></div>
                        <div class="ranking-info">
                            <div class="ranking-nombre"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="ranking-sub"><?= $raza ?> · <?= $clase ?></div>
                        </div>
                        <div class="ranking-nivel">
                            <div class="ranking-nivel-num"><?= $p['level'] ?></div>
                            <div class="ranking-nivel-txt">Nivel</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 3. TOP JcJ -->
    <div class="panel">
        <div class="adorno-superior">◆</div>
        <div class="panel-cabecera">
            <div class="panel-titulo-grupo"><div class="panel-icono">⚔️</div><h2>Top JcJ</h2></div>
        </div>
        <?php if (empty($topPvP)): ?>
            <div class="panel-vacio"><div class="icono-vacio">🏆</div><p>Aún no hay combates JcJ. La primera<br>victoria honorable te pondrá aquí.</p></div>
        <?php else: ?>
            <div class="ranking-lista">
                <?php $i=1; foreach($topPvP as $p): 
                    $raza = $razas[$p['race']] ?? 'Desc'; $clase = $clases[$p['class']] ?? 'Desc';
                    $color = $coloresClase[$p['class']] ?? '#555';
                ?>
                    <div class="ranking-item" style="border-left: 3px solid <?= $color ?>;">
                        <div class="ranking-num"><?= $i++ ?></div>
                        <div class="ranking-info">
                            <div class="ranking-nombre"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="ranking-sub"><?= $raza ?> · <?= $clase ?></div>
                        </div>
                        <div class="ranking-nivel">
                            <div class="ranking-nivel-num"><?= $p['matchmakerRating'] ?></div>
                            <div class="ranking-nivel-txt">MMR</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 4. CLASES MÁS JUGADAS -->
    <div class="panel">
        <div class="adorno-superior">◆</div>
        <div class="panel-cabecera">
            <div class="panel-titulo-grupo"><div class="panel-icono">📊</div><h2>Clases más jugadas</h2></div>
        </div>
        <?php if (empty($statsClases)): ?>
            <div class="panel-vacio"><div class="icono-vacio">📊</div><p>No hay datos suficientes.</p></div>
        <?php else: ?>
            <div class="clases-lista">
                <?php 
                // Mostramos solo las 5 primeras
                $topClases = array_slice($statsClases, 0, 5); 
                foreach($topClases as $c): 
                    $nombreClase = $clases[$c['class']] ?? 'Desconocida';
                    $color = $coloresClase[$c['class']] ?? '#555';
                    $porcentaje = $totalPersonajes > 0 ? round(($c['cantidad'] / $totalPersonajes) * 100) : 0;
                    $porcentajeVisual = max(5, $porcentaje); // Mínimo 5% para que se vea la barra
                ?>
                    <div class="clase-item">
                        <div class="clase-icono" style="color: <?= $color ?>; border: 1px solid <?= $color ?>;">
                            <?= substr($nombreClase, 0, 1) ?>
                        </div>
                        <div class="clase-nombre"><?= $nombreClase ?></div>
                        <div class="clase-barra-fondo">
                            <div class="clase-barra-relleno" style="width: <?= $porcentajeVisual ?>%; background: <?= $color ?>;"></div>
                        </div>
                        <div class="clase-cantidad"><?= $c['cantidad'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
