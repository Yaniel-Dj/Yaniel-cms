<?php
// 1. LLAMADA AL CONFIG (Ruta corregida)
require_once __DIR__ . '/config.php';

// 2. Conexión a la base de datos de personajes
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, CHAR_DB_NAME, (int)DB_PORT);
if ($conn->connect_error) {
    die("Error de conexión a la base de datos.");
}
$conn->set_charset("utf8");

// 3. Consultas para Alianza
$ali_total = $conn->query("SELECT COUNT(*) as total FROM characters WHERE race IN (1, 3, 4, 7, 11)")->fetch_assoc()['total'];
$ali_online = $conn->query("SELECT COUNT(*) as total FROM characters WHERE online = 1 AND race IN (1, 3, 4, 7, 11)")->fetch_assoc()['total'];

// 4. Consultas para Horda
$horda_total = $conn->query("SELECT COUNT(*) as total FROM characters WHERE race IN (2, 5, 6, 8, 10)")->fetch_assoc()['total'];
$horda_online = $conn->query("SELECT COUNT(*) as total FROM characters WHERE online = 1 AND race IN (2, 5, 6, 8, 10)")->fetch_assoc()['total'];

// 5. Cálculos
$total_facciones = $ali_total + $horda_total;
$porc_ali = $total_facciones > 0 ? round(($ali_total / $total_facciones) * 100) : 50;
$porc_horda = $total_facciones > 0 ? 100 - $porc_ali : 50;
?>

<style>
    /* Estilos exclusivos del Panel de Facciones */
    .panel-facciones {
        width: 100%; max-width: 850px; margin: 40px auto; background: #060b14;
        border: 2px solid #b8860b; border-radius: 6px; position: relative; padding: 30px 40px;
        box-sizing: border-box; box-shadow: inset 0 0 0 1px #2a1e00, inset 0 0 20px rgba(0,0,0,1), 0 0 0 1px #000, 0 0 25px rgba(184, 134, 11, 0.15);
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    
    .panel-facciones::before, .panel-facciones::after { 
        content: ''; position: absolute; width: 16px; height: 16px; border: 2px solid #d4af37; z-index: 10; 
    }
    .panel-facciones::before { top: -2px; left: -2px; border-right: none; border-bottom: none; }
    .panel-facciones::after { bottom: -2px; right: -2px; border-left: none; border-top: none; }
    
    .adorno-superior { 
        position: absolute; top: -16px; left: 50%; transform: translateX(-50%); 
        color: #69CCF0; font-size: 24px; text-shadow: 0 0 15px #69CCF0, 0 0 5px #fff; 
        background: #060b14; padding: 0 12px; line-height: 1; z-index: 11; 
    }

    .fac-cabecera { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2a3a52; padding-bottom: 15px; margin-bottom: 25px; }
    .fac-titulo-grupo { display: flex; align-items: center; gap: 12px; }
    .fac-icono-titulo { width: 35px; height: 35px; background: #1a1a1a; border: 1px solid #c9a227; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .fac-cabecera h2 { font-size: 20px; font-weight: 400; letter-spacing: 2px; color: #e6e6e6; margin: 0; font-family: 'Georgia', serif; text-transform: uppercase; }
    .fac-estado { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 1px; }

    .conteo-facciones { display: flex; justify-content: space-between; align-items: center; padding: 0 50px; margin-bottom: 30px; }
    .fac-lado { display: flex; flex-direction: column; align-items: center; width: 150px; }

    @keyframes flotar {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
        100% { transform: translateY(0px); }
    }
    .escudo-forma {
        width: 100px; height: 115px; position: relative; display: flex; align-items: center; justify-content: center;
        margin-bottom: 15px; box-sizing: border-box; animation: flotar 4s ease-in-out infinite;
        clip-path: polygon(50% 0%, 100% 15%, 100% 75%, 50% 100%, 0% 75%, 0% 15%);
        background: #d4af37; padding: 3px; 
    }
    .escudo-forma.horda { animation-delay: -2s; }
    
    .escudo-forma .interior {
        width: 100%; height: 100%; clip-path: polygon(50% 0%, 100% 15%, 100% 75%, 50% 100%, 0% 75%, 0% 15%);
        position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    .escudo-forma.alianza .interior { background: radial-gradient(circle at 50% 30%, #4a88d4 0%, #1a3b6e 80%, #0a1a33 100%); }
    .escudo-forma.horda .interior { background: radial-gradient(circle at 50% 30%, #d44a4a 0%, #6e1a1a 80%, #330a0a 100%); }
    
    .escudo-forma .interior::before {
        content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
        background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.15) 50%, transparent 60%);
        transform: rotate(30deg);
    }
    
    .escudo-forma .joya {
        width: 24px; height: 24px; background: #111; border: 2px solid #d4af37;
        border-radius: 4px; transform: rotate(45deg); box-shadow: inset 0 0 10px rgba(0,0,0,0.8), 0 0 10px rgba(0,0,0,0.5);
        z-index: 2; display: flex; align-items: center; justify-content: center;
    }
    .escudo-forma.alianza .joya { box-shadow: inset 0 0 10px rgba(0,0,0,0.8), 0 0 15px rgba(105, 204, 240, 0.6); }
    .escudo-forma.horda .joya { box-shadow: inset 0 0 10px rgba(0,0,0,0.8), 0 0 15px rgba(255, 77, 77, 0.6); }
    .joya::after { content: ''; width: 10px; height: 10px; background: rgba(255,255,255,0.8); transform: rotate(-45deg); border-radius: 2px; }

    .numero-faccion { font-size: 36px; font-weight: 300; color: #fff; font-family: 'Georgia', serif; line-height: 1; margin-bottom: 5px; }
    .nombre-faccion { font-size: 18px; letter-spacing: 2px; text-transform: uppercase; font-family: 'Georgia', serif; margin-bottom: 8px; }
    .nombre-faccion.alianza { color: #8cb4ff; text-shadow: 0 0 10px rgba(140, 180, 255, 0.3); }
    .nombre-faccion.horda { color: #ff8c8c; text-shadow: 0 0 10px rgba(255, 140, 140, 0.3); }

    .en-linea { font-size: 12px; color: #aaa; display: flex; align-items: center; gap: 6px; font-weight: 500; }
    .punto-verde { width: 8px; height: 8px; border-radius: 50%; background: #1eff00; box-shadow: 0 0 8px #1eff00; animation: latido 2s infinite; }
    .punto-rojo { width: 8px; height: 8px; border-radius: 50%; background: #ff4d4d; }
    
    @keyframes latido {
        0% { box-shadow: 0 0 0 0 rgba(30, 255, 0, 0.7); }
        70% { box-shadow: 0 0 0 6px rgba(30, 255, 0, 0); }
        100% { box-shadow: 0 0 0 0 rgba(30, 255, 0, 0); }
    }

    .vs-circle {
        width: 70px; height: 70px; border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, #fff5cc 0%, #f0c94a 30%, #b8860b 70%, #5c4200 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: bold; color: #3e2a00; font-family: 'Georgia', serif;
        text-shadow: 0 1px 1px rgba(255,255,255,0.5);
        box-shadow: inset 0 0 15px rgba(0,0,0,0.4), inset 0 2px 5px rgba(255,255,255,0.8), 0 0 30px rgba(240, 201, 74, 0.4), 0 0 60px rgba(240, 201, 74, 0.2);
        border: 2px solid #ffdf73; z-index: 5;
    }

    .barra-progreso {
        width: 100%; height: 24px; background: #111; border-radius: 12px; overflow: hidden; display: flex;
        position: relative; border: 1px solid #000; box-shadow: inset 0 2px 5px rgba(0,0,0,0.8); margin-bottom: 15px;
    }
    .barra-porcion { height: 100%; display: flex; align-items: center; transition: width 0.8s ease-in-out; position: relative; }
    .barra-azul { background: linear-gradient(to right, #3b78c4, #24508a); justify-content: flex-start; padding-left: 10px; }
    .barra-roja { background: linear-gradient(to right, #b73d3d, #7a2222); justify-content: flex-end; padding-right: 10px; }
    
    .texto-barra { font-size: 12px; font-weight: bold; color: #fff; text-shadow: 1px 1px 2px #000; white-space: nowrap; }
    .texto-inferior { text-align: center; font-size: 13px; color: #888; }
    .texto-inferior span { color: #d4af37; font-weight: bold; }

    @media (max-width: 768px) { 
        .conteo-facciones { flex-direction: column; gap: 20px; padding: 0; }
        .vs-circle { transform: rotate(90deg); }
    }
</style>

<div class="panel-facciones">
    <div class="adorno-superior">◆</div>

    <div class="fac-cabecera">
        <div class="fac-titulo-grupo">
            <div class="fac-icono-titulo">⚔️</div>
            <h2>Alianza vs Horda</h2>
        </div>
        <span class="fac-estado">Estado del Reino 🔒</span>
    </div>

    <div class="conteo-facciones">
        <!-- Alianza -->
        <div class="fac-lado">
            <div class="escudo-forma alianza">
                <div class="interior"><div class="joya"></div></div>
            </div>
            <div class="numero-faccion"><?= $ali_total ?></div>
            <div class="nombre-faccion alianza">Alianza</div>
            
            <div class="en-linea">
                <?php if ($ali_online > 0): ?>
                    <div class="punto-verde"></div> 
                <?php else: ?>
                    <div class="punto-rojo"></div> 
                <?php endif; ?>
                <?= $ali_online ?> en línea
            </div>
        </div>

        <!-- VS -->
        <div class="vs-circle">VS</div>

        <!-- Horda -->
        <div class="fac-lado">
            <div class="escudo-forma horda">
                <div class="interior"><div class="joya"></div></div>
            </div>
            <div class="numero-faccion"><?= $horda_total ?></div>
            <div class="nombre-faccion horda">Horda</div>
            
            <div class="en-linea">
                <?php if ($horda_online > 0): ?>
                    <div class="punto-verde"></div> 
                <?php else: ?>
                    <div class="punto-rojo"></div> 
                <?php endif; ?>
                <?= $horda_online ?> en línea
            </div>
        </div>
    </div>

    <div class="barra-progreso">
        <div class="barra-porcion barra-azul" style="width: <?= $porc_ali ?>%;">
            <span class="texto-barra"><?= $porc_ali ?> %</span>
        </div>
        <div class="barra-porcion barra-roja" style="width: <?= $porc_horda ?>%;">
            <span class="texto-barra"><?= $porc_horda ?> %</span>
        </div>
    </div>

    <div class="texto-inferior">
        <span><?= $total_facciones ?></span> personajes creados en el reino. ¿De qué lado estás tú?
    </div>
</div>
