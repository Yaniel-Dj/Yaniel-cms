<?php
require_once __DIR__ . '/config.php';

// ==========================================
// 1. LÓGICA DE BÚSQUEDA (AJAX - Como FusionCMS)
// ==========================================
if (isset($_GET['ajax']) && $_GET['ajax'] == 'search') {
    header('Content-Type: application/json');
    
    // Parámetros de DataTables
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $table = $_POST['table'] ?? 'items';
    $search = trim($_POST['search'] ?? '');

    // Conexiones a tus bases de datos usando tu config.php
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, CHAR_DB_NAME, (int)DB_PORT);
    $worldConn = new mysqli(DB_HOST, DB_USER, DB_PASS, WORLD_DB_NAME, (int)DB_PORT);
    $conn->set_charset("utf8mb4");
    $worldConn->set_charset("utf8mb4");

    $result = [];
    $total = 0;

    // --- BÚSQUEDA DE ÍTEMS (acore_world) ---
    if ($table === 'items') {
        $searchParam = "%$search%";
        // Buscamos en item_template
        $stmt = $worldConn->prepare("SELECT entry as id, name, ItemLevel as level, RequiredLevel as required, InventoryType, Quality FROM item_template WHERE name LIKE ? ORDER BY ItemLevel DESC LIMIT ?, ?");
        $stmt->bind_param("sii", $searchParam, $start, $length);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while ($row = $res->fetch_assoc()) {
            // Simulamos el icono con el nombre (ya que no cargamos imágenes)
            $row['icon'] = '<img src="Img/armeria/iconos/inv_misc_questionmark.jpg" width="20">'; 
            $row['type'] = 'Tipo ' . $row['InventoryType'];
            $result[] = $row;
        }

        $stmtCount = $worldConn->prepare("SELECT COUNT(*) as total FROM item_template WHERE name LIKE ?");
        $stmtCount->bind_param("s", $searchParam);
        $stmtCount->execute();
        $total = $stmtCount->get_result()->fetch_assoc()['total'];
    }
    
    // --- BÚSQUEDA DE PERSONAJES (acore_characters) ---
    elseif ($table === 'characters') {
        $searchParam = "%$search%";
        $stmt = $conn->prepare("SELECT guid, name, class, race, level FROM characters WHERE name LIKE ? ORDER BY level DESC LIMIT ?, ?");
        $stmt->bind_param("sii", $searchParam, $start, $length);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while ($row = $res->fetch_assoc()) {
            // Determinar facción (1 = Alianza, 2 = Horda)
            $esHorda = in_array((int)$row['race'], [2, 5, 6, 8, 10]);
            $row['faction'] = $esHorda ? '2' : '1';
            $row['avatar'] = 'default'; // Nombre del avatar
            $result[] = $row;
        }

        $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM characters WHERE name LIKE ?");
        $stmtCount->bind_param("s", $searchParam);
        $stmtCount->execute();
        $total = $stmtCount->get_result()->fetch_assoc()['total'];
    }

    // --- BÚSQUEDA DE HERMANDADES (acore_characters) ---
    elseif ($table === 'guilds') {
        $searchParam = "%$search%";
        $stmt = $conn->prepare("SELECT guildid as id, name, GuildMemberCount as members, leaderName FROM guild WHERE name LIKE ? ORDER BY GuildMemberCount DESC LIMIT ?, ?");
        $stmt->bind_param("sii", $searchParam, $start, $length);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while ($row = $res->fetch_assoc()) {
            $result[] = $row;
        }

        $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM guild WHERE name LIKE ?");
        $stmtCount->bind_param("s", $searchParam);
        $stmtCount->execute();
        $total = $stmtCount->get_result()->fetch_assoc()['total'];
    }

    // Devolvemos el JSON que espera DataTables
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $total,
        "recordsFiltered" => $total,
        "data" => $result
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armería - Fusion Style</title>
    
    <!-- DataTables y jQuery (Como FusionCMS) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Tu CSS -->
    <link rel="stylesheet" href="css/armeria.css">
</head>
<body>

<div class="container">
    <!-- BARRA DE BÚSQUEDA (Idéntica a FusionCMS) -->
    <div class="page-subbody">
        <form onSubmit="Search.show_data();return false;">
            <div class="input-group">
                <input class="form-control" type="text" id="search_field" name="search_field" placeholder="Buscar...">
                
                <select class="form-control" id="table" name="table" style="max-width: 200px;" onchange="Search.toggle();">
                    <option value="characters">Personajes</option>
                    <option value="guilds">Hermandades</option>
                    <option value="items">Ítems</option>
                </select>
                
                <button class="nice_button" type="button" onclick="Search.show_data();">Buscar</button>
            </div>
        </form>
    </div>

    <!-- TABLA DE RESULTADOS (DataTables) -->
    <div class="table-responsive" id="search_box" style="display: none;">
        <table class="nice_table" id="search_results">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Nivel</th>
                    <th>Faccción</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
// El JavaScript de búsqueda de FusionCMS adaptado
var Search = {
    show_data: function() {
        var table = $('#table').val();
        var search = $('#search_field').val();

        if (search.length > 2) {
            Search.init_table();
        } else {
            Swal.fire({ icon: 'error', title: 'Oops...', text: 'Escribe al menos 3 letras.', background: '#151a24', color: '#fff' });
        }
    },
    
    toggle: function() {
        if ($.fn.DataTable.isDataTable('#search_results')) {
            $('#search_results').DataTable().destroy();
        }
        $('#search_box').hide();
    },

    init_table: function() {
        var table = $('#table').val();
        var search = $('#search_field').val();
        
        $('#search_box').show();
        $('#search_results').DataTable({
            "destroy": true,
            "processing": true,
            "serverSide": true,
            "searching": false,
            "ordering": false,
            "lengthChange": false,
            "ajax": {
                "url": "armeria.php?ajax=search",
                "type": "POST",
                "data": function(d) {
                    d.table = table;
                    d.search = search;
                    d.start = d.start;
                    d.length = d.length;
                }
            },
            "columns": [
                { 
                    "data": "name", 
                    "render": function(data, type, row) {
                        if (table === 'characters') {
                            return `<span class="color-c${row.class}">${data}</span>`;
                        }
                        if (table === 'items') {
                            return row.icon + ' <span class="q' + (row.level % 5) + '">' + data + '</span>';
                        }
                        return data;
                    }
                },
                { "data": "level" },
                { 
                    "data": "faction", 
                    "render": function(data) {
                        if (data) return `<span class="faction-${data}"></span>`;
                        return '';
                    }
                },
                { 
                    "data": null, 
                    "render": function(data, type, row) {
                        if (table === 'characters') {
                            // ENLACE AL PERFIL DEL PERSONAJE (El que arma las ranuras)
                            return `<a href="perfil.php?guid=${row.guid}" class="nice_button" style="padding:5px 10px;font-size:12px;">Ver Perfil</a>`;
                        }
                        return '';
                    }
                }
            ]
        });
    }
};
</script>

</body>
</html>
