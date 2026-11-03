<?php
// Incluido desde admin/index.php

$mensaje = '';
$tipo = '';

// Acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);

    if ($user_id > 0) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            if ($accion === 'ban') {
                $stmt = $pdo->prepare("UPDATE account SET locked = 1 WHERE id = ?");
                $stmt->execute([$user_id]);
                $mensaje = t('saved_successfully'); $tipo = 'exito';
            } 
            elseif ($accion === 'unban') {
                $stmt = $pdo->prepare("UPDATE account SET locked = 0 WHERE id = ?");
                $stmt->execute([$user_id]);
                $mensaje = t('saved_successfully'); $tipo = 'exito';
            }
            elseif ($accion === 'rango') {
                $nuevo_rango = (int)($_POST['gmlevel'] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM account_access WHERE id = ?");
                $stmt->execute([$user_id]);
                if ($nuevo_rango > 0) {
                    $stmt = $pdo->prepare("INSERT INTO account_access (id, gmlevel, RealmID) VALUES (?, ?, -1)");
                    $stmt->execute([$user_id, $nuevo_rango]);
                }
                $mensaje = t('saved_successfully'); $tipo = 'exito';
            }
        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

// Filtro de búsqueda
$buscar = trim($_GET['buscar'] ?? '');

// Paginación
$por_pagina = 20;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($pagina - 1) * $por_pagina;

$usuarios = [];
$total_usuarios = 0;
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $where = '';
    $params = [];
    if ($buscar !== '') {
        $where = "WHERE a.username LIKE ?";
        $params[] = "%$buscar%";
    }

    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM account a $where");
    $stmt_count->execute($params);
    $total_usuarios = (int)$stmt_count->fetchColumn();

    $sql = "SELECT a.id, a.username, a.locked, a.last_login, 
                   COALESCE(MAX(ac.gmlevel), 0) AS gmlevel 
            FROM account a 
            LEFT JOIN account_access ac ON a.id = ac.id 
            $where
            GROUP BY a.id 
            ORDER BY a.id DESC
            LIMIT $por_pagina OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$total_paginas = ceil($total_usuarios / $por_pagina);
?>

<?php if ($mensaje): ?>
    <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="admin-section">
    <h3>👥 <?= t('manage_users') ?> (<?= $total_usuarios ?>)</h3>
    
    <form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
        <input type="hidden" name="tab" value="usuarios">
        <input type="text" name="buscar" placeholder="<?= t('search_user') ?>" value="<?= htmlspecialchars($buscar) ?>" style="flex:1; margin:0; padding:10px;">
        <button type="submit" style="width:auto; margin:0; padding:10px 20px;"><?= t('search') ?></button>
        <?php if ($buscar): ?>
            <a href="?tab=usuarios" class="btn-borrar" style="padding:10px 20px; text-decoration:none; display:inline-flex; align-items:center;"><?= t('clear') ?></a>
        <?php endif; ?>
    </form>

    <div style="overflow-x:auto;">
        <table class="admin-tabla" style="width:100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= t('username') ?></th>
                    <th><?= t('rank') ?></th>
                    <th><?= t('status') ?></th>
                    <th><?= t('last_login') ?></th>
                    <th><?= t('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:20px;"><?= t('no_users_found') ?></td></tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                            <td>
                                <?php if ($u['gmlevel'] >= 4): ?><span style="color:#ff5c5c;"><?= t('admin') ?></span>
                                <?php elseif ($u['gmlevel'] >= 3): ?><span style="color:#f0c94a;">GM</span>
                                <?php elseif ($u['gmlevel'] >= 2): ?><span style="color:#9dd8ff;"><?= t('mod') ?></span>
                                <?php else: ?><span style="color:#7a8590;"><?= t('player') ?></span><?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['locked']): ?>
                                    <span style="color:#ff5c5c;"><?= t('banned') ?></span>
                                <?php else: ?>
                                    <span style="color:#5ac86b;"><?= t('active_user') ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : '-' ?></td>
                            <td>
                                <div style="display:flex; gap:5px; flex-wrap:wrap;">
                                    <?php if ($u['locked']): ?>
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" name="accion" value="unban" class="btn-borrar" style="background:rgba(40,167,69,0.3); border-color:#28a745; color:#8ff0a0;"><?= t('unban') ?></button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="margin:0;" onsubmit="return confirm('<?= t('are_you_sure') ?>');">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" name="accion" value="ban" class="btn-borrar"><?= t('ban') ?></button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST" style="margin:0; display:flex; gap:5px;">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <select name="gmlevel" style="padding:4px; font-size:11px; width:auto; margin:0; background:rgba(0,0,0,0.5); color:#fff; border:1px solid #444;">
                                            <option value="0" <?= $u['gmlevel'] == 0 ? 'selected' : '' ?>><?= t('player') ?></option>
                                            <option value="2" <?= $u['gmlevel'] == 2 ? 'selected' : '' ?>><?= t('mod') ?></option>
                                            <option value="3" <?= $u['gmlevel'] == 3 ? 'selected' : '' ?>>GM</option>
                                            <option value="4" <?= $u['gmlevel'] == 4 ? 'selected' : '' ?>><?= t('admin') ?></option>
                                        </select>
                                        <button type="submit" name="accion" value="rango" class="btn-borrar" style="background:rgba(30,91,198,0.3); border-color:#1e5bc6; color:#9dd8ff;"><?= t('save') ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_paginas > 1): ?>
        <div style="display:flex; justify-content:center; gap:5px; margin-top:20px; flex-wrap:wrap;">
            <?php if ($pagina > 1): ?>
                <a href="?tab=usuarios&buscar=<?= urlencode($buscar) ?>&pagina=<?= $pagina - 1 ?>" class="btn-borrar" style="text-decoration:none; padding:6px 12px;"><?= t('previous') ?></a>
            <?php endif; ?>
            
            <span style="padding:6px 12px; color:#f0c94a;"><?= t('page') ?> <?= $pagina ?> <?= t('of') ?> <?= $total_paginas ?></span>
            
            <?php if ($pagina < $total_paginas): ?>
                <a href="?tab=usuarios&buscar=<?= urlencode($buscar) ?>&pagina=<?= $pagina + 1 ?>" class="btn-borrar" style="text-decoration:none; padding:6px 12px;"><?= t('next') ?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>