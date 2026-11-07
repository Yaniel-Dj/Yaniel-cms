<?php
// Incluido desde admin/index.php

$mensaje = '';
$tipo = '';

$usa_account_banned = false;
try {
    $pdo_probe = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $check = $pdo_probe->query("SHOW TABLES LIKE 'account_banned'");
    $usa_account_banned = ($check->rowCount() > 0);
    $pdo_probe = null;
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion  = $_POST['accion'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);

    if ($user_id > 0 && $accion !== '') {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // BAN
            if ($accion === 'ban') {
                $motivo = trim($_POST['motivo_ban'] ?? '');
                if ($motivo === '') {
                    $motivo = 'Baneado por administrador';
                }
                if (mb_strlen($motivo) > 250) {
                    $motivo = mb_substr($motivo, 0, 250);
                }
                $gm_nombre = $web_user ?? 'admin';

                if ($usa_account_banned) {
                    $stmt = $pdo->prepare("
                        INSERT INTO account_banned (id, bandate, unbandate, bannedby, banreason, active)
                        VALUES (?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP() + 315360000, ?, ?, 1)
                        ON DUPLICATE KEY UPDATE
                            bandate    = UNIX_TIMESTAMP(),
                            unbandate  = UNIX_TIMESTAMP() + 315360000,
                            bannedby   = VALUES(bannedby),
                            banreason  = VALUES(banreason),
                            active     = 1
                    ");
                    $stmt->execute([$user_id, $gm_nombre, $motivo]);
                }
                $stmt = $pdo->prepare("UPDATE account SET locked = 1 WHERE id = ?");
                $stmt->execute([$user_id]);

                $mensaje = t('saved_successfully'); $tipo = 'exito';
            }

            // UNBAN
            elseif ($accion === 'unban') {
                if ($usa_account_banned) {
                    $stmt = $pdo->prepare("UPDATE account_banned SET active = 0 WHERE id = ? AND active = 1");
                    $stmt->execute([$user_id]);
                }
                $stmt = $pdo->prepare("UPDATE account SET locked = 0 WHERE id = ?");
                $stmt->execute([$user_id]);

                $mensaje = t('saved_successfully'); $tipo = 'exito';
            }

            // RANGO
            elseif ($accion === 'rango') {
                $nuevo_rango = (int)($_POST['gmlevel'] ?? 0);

                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare("DELETE FROM account_access WHERE id = ?");
                    $stmt->execute([$user_id]);

                    if ($nuevo_rango > 0) {
                        $stmt = $pdo->prepare("INSERT INTO account_access (id, gmlevel, RealmID) VALUES (?, ?, -1)");
                        $stmt->execute([$user_id, $nuevo_rango]);
                    }
                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }

                $mensaje = t('saved_successfully'); $tipo = 'exito';
            }

        } catch (PDOException $e) {
            $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
        }
    }
}

$buscar = trim($_GET['buscar'] ?? '');

$por_pagina = 20;
$pagina     = max(1, (int)($_GET['pagina'] ?? 1));
$offset     = ($pagina - 1) * $por_pagina;

$usuarios       = [];
$total_usuarios = 0;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $where  = '';
    $params = [];
    if ($buscar !== '') {
        $where   = "WHERE a.username LIKE ?";
        $params[] = "%$buscar%";
    }

    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM account a $where");
    $stmt_count->execute($params);
    $total_usuarios = (int)$stmt_count->fetchColumn();

    if ($usa_account_banned) {
        $sql = "SELECT a.id, a.username, a.locked, a.last_login,
                       COALESCE(MAX(ac.gmlevel), 0) AS gmlevel,
                       (SELECT COUNT(*) FROM account_banned ab WHERE ab.id = a.id AND ab.active = 1) AS has_ban
                FROM account a
                LEFT JOIN account_access ac ON a.id = ac.id
                $where
                GROUP BY a.id
                ORDER BY a.id DESC
                LIMIT $por_pagina OFFSET $offset";
    } else {
        $sql = "SELECT a.id, a.username, a.locked, a.last_login,
                       COALESCE(MAX(ac.gmlevel), 0) AS gmlevel,
                       0 AS has_ban
                FROM account a
                LEFT JOIN account_access ac ON a.id = ac.id
                $where
                GROUP BY a.id
                ORDER BY a.id DESC
                LIMIT $por_pagina OFFSET $offset";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mensaje = t('db_error') . ': ' . $e->getMessage(); $tipo = 'error';
}

$total_paginas = (int)ceil($total_usuarios / $por_pagina);
?>

<?php if ($mensaje): ?>
    <div class="alerta <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="admin-section">
    <h3>👥 <?= t('manage_users') ?> (<?= $total_usuarios ?>)</h3>

    <?php if ($usa_account_banned): ?>
        <p style="color:#8ff0a0; font-size:11px; text-align:center; margin-bottom:15px;">
            ✔ Modo <strong>AzerothCore moderno</strong>: se usa la tabla <code>account_banned</code>.
        </p>
    <?php else: ?>
        <p style="color:#f0c94a; font-size:11px; text-align:center; margin-bottom:15px;">
            ⚠ No se encontró <code>account_banned</code>. Se usará solo <code>account.locked</code>.
        </p>
    <?php endif; ?>

    <form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
        <input type="hidden" name="tab" value="usuarios">
        <input type="text" name="buscar" placeholder="<?= t('search_user') ?>"
               value="<?= htmlspecialchars($buscar) ?>"
               style="flex:1; margin:0; padding:10px;">
        <button type="submit" style="width:auto; margin:0; padding:10px 20px;"><?= t('search') ?></button>
        <?php if ($buscar): ?>
            <a href="?tab=usuarios" class="btn-borrar"
               style="padding:10px 20px; text-decoration:none; display:inline-flex; align-items:center;">
                <?= t('clear') ?>
            </a>
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
                    <?php foreach ($usuarios as $u):
                        $esta_baneado = (!empty($u['locked']) || (int)$u['has_ban'] > 0);
                    ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                            <td>
                                <?php if ($u['gmlevel'] >= 4): ?><span style="color:#ff5c5c;"><?= t('admin') ?></span>
                                <?php elseif ($u['gmlevel'] >= 3): ?><span style="color:#f0c94a;">GM</span>
                                <?php elseif ($u['gmlevel'] >= 2): ?><span style="color:#9dd8ff;"><?= t('mod') ?></span>
                                <?php else: ?><span style="color:#7a8590;"><?= t('player') ?></span><?php endif; ?>
                            </td>
                            <td>
                                <?php if ($esta_baneado): ?>
                                    <span style="color:#ff5c5c;"><?= t('banned') ?></span>
                                <?php else: ?>
                                    <span style="color:#5ac86b;"><?= t('active_user') ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : '-' ?></td>
                            <td>
                                <div style="display:flex; gap:5px; flex-wrap:wrap;">

                                    <?php if ($esta_baneado): ?>
                                        <form method="POST" style="margin:0;"
                                              onsubmit="return confirm('<?= t('are_you_sure') ?>');">
                                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                            <input type="hidden" name="accion" value="unban">
                                            <button type="submit" class="btn-borrar"
                                                    style="background:rgba(40,167,69,0.3); border-color:#28a745; color:#8ff0a0;">
                                                <?= t('unban') ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <details style="margin:0; display:inline-block;">
                                            <summary class="btn-borrar" style="cursor:pointer; list-style:none; display:inline-block; padding:5px 10px; background:rgba(220,53,69,0.3); border:1px solid #dc3545; color:#ff9aa4; border-radius:5px; font-size:11px;">
                                                <?= t('ban') ?>
                                            </summary>
                                            <div style="margin-top:8px; padding:10px; background:rgba(0,0,0,0.5); border:1px solid #dc3545; border-radius:8px; min-width:220px;">
                                                <form method="POST" style="margin:0;" onsubmit="return confirm('<?= t('are_you_sure') ?>');">
                                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                                    <input type="hidden" name="accion" value="ban">
                                                    <label style="font-size:10px; color:#ff9aa4; display:block; margin-bottom:4px;">Motivo del ban:</label>
                                                    <input type="text" name="motivo_ban" placeholder="Ej: Uso de hacks"
                                                           maxlength="250" style="width:100%; padding:6px; font-size:11px; margin-bottom:6px; background:rgba(0,0,0,0.6); color:#fff; border:1px solid #444; border-radius:4px;">
                                                    <button type="submit" class="btn-borrar" style="width:100%; padding:6px; font-size:11px;">
                                                        Confirmar ban
                                                    </button>
                                                </form>
                                            </div>
                                        </details>
                                    <?php endif; ?>

                                    <form method="POST" style="margin:0; display:flex; gap:5px;">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <input type="hidden" name="accion" value="rango">
                                        
                                        <select name="gmlevel"
                                                style="padding:4px; font-size:11px; width:auto; margin:0; background:rgba(0,0,0,0.5); color:#fff; border:1px solid #444;">
                                            <option value="0" <?= $u['gmlevel'] == 0 ? 'selected' : '' ?>><?= t('player') ?></option>
                                            <option value="2" <?= $u['gmlevel'] == 2 ? 'selected' : '' ?>><?= t('mod') ?></option>
                                            <option value="3" <?= $u['gmlevel'] == 3 ? 'selected' : '' ?>>GM</option>
                                            <option value="4" <?= $u['gmlevel'] == 4 ? 'selected' : '' ?>><?= t('admin') ?></option>
                                        </select>
                                        <button type="submit" class="btn-borrar"
                                                style="background:rgba(30,91,198,0.3); border-color:#1e5bc6; color:#9dd8ff;">
                                            <?= t('save') ?>
                                        </button>
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
                <a href="?tab=usuarios&buscar=<?= urlencode($buscar) ?>&pagina=<?= $pagina - 1 ?>"
                   class="btn-borrar" style="text-decoration:none; padding:6px 12px;">
                    <?= t('previous') ?>
                </a>
            <?php endif; ?>

            <span style="padding:6px 12px; color:#f0c94a;">
                <?= t('page') ?> <?= $pagina ?> <?= t('of') ?> <?= $total_paginas ?>
            </span>

            <?php if ($pagina < $total_paginas): ?>
                <a href="?tab=usuarios&buscar=<?= urlencode($buscar) ?>&pagina=<?= $pagina + 1 ?>"
                   class="btn-borrar" style="text-decoration:none; padding:6px 12px;">
                    <?= t('next') ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
