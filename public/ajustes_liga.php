<?php
require_once __DIR__ . '/../private/funciones_auth.php';
require_once __DIR__ . '/../private/iconos.php';
requiereAutenticacion();

$pdo       = getDB();
$idUsuario = (int) $_SESSION['id_usuario'];

$equipo   = obtenerOCrearEquipo($idUsuario, $pdo);
$idEquipo = (int) $equipo['id_equipo'];

$idLiga = (int) ($_GET['liga'] ?? 0);
if ($idLiga === 0) {
    $stmtPrim = $pdo->prepare("SELECT l.id_liga FROM ligas l JOIN liga_miembros lm ON lm.id_liga = l.id_liga WHERE lm.id_equipo = ? ORDER BY l.id_creador = ? DESC, l.fecha_creacion ASC LIMIT 1");
    $stmtPrim->execute([$idEquipo, $idUsuario]);
    $prim = $stmtPrim->fetchColumn();
    if ($prim) { header("Location: ajustes_liga.php?liga=$prim"); exit; }
    else        { header('Location: ligas.php'); exit; }
}

$stmtL = $pdo->prepare("SELECT l.*, u.nombre AS nombre_creador FROM ligas l JOIN usuarios u ON u.id_usuario = l.id_creador WHERE l.id_liga = ?");
$stmtL->execute([$idLiga]);
$liga = $stmtL->fetch();
if (!$liga) { header('Location: ligas.php'); exit; }

$stmtMem = $pdo->prepare("SELECT id FROM liga_miembros WHERE id_liga = ? AND id_equipo = ?");
$stmtMem->execute([$idLiga, $idEquipo]);
if (!$stmtMem->fetch()) { header('Location: ligas.php'); exit; }

$esCreador = ((int)$liga['id_creador'] === $idUsuario);
$flash     = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'editar' && $esCreador) {
        $nuevoNombre = trim($_POST['nombre'] ?? '');
        $nuevaDesc   = trim($_POST['descripcion'] ?? '');
        if (strlen($nuevoNombre) < 3) {
            $flash = ['tipo' => 'error', 'msg' => 'El nombre debe tener al menos 3 caracteres.'];
        } else {
            $pdo->prepare("UPDATE ligas SET nombre = ?, descripcion = ? WHERE id_liga = ? AND id_creador = ?")->execute([$nuevoNombre, $nuevaDesc ?: null, $idLiga, $idUsuario]);
            $liga['nombre']      = $nuevoNombre;
            $liga['descripcion'] = $nuevaDesc;
            $flash = ['tipo' => 'ok', 'msg' => 'Ajustes guardados correctamente.'];
        }
    }

    if ($accion === 'regenerar_codigo' && $esCreador) {
        do {
            $nuevoCodigo = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $chk = $pdo->prepare("SELECT id_liga FROM ligas WHERE codigo_invitacion = ?");
            $chk->execute([$nuevoCodigo]);
        } while ($chk->fetch());
        $pdo->prepare("UPDATE ligas SET codigo_invitacion = ? WHERE id_liga = ? AND id_creador = ?")->execute([$nuevoCodigo, $idLiga, $idUsuario]);
        $liga['codigo_invitacion'] = $nuevoCodigo;
        $flash = ['tipo' => 'ok', 'msg' => 'Codigo de invitacion regenerado.'];
    }

    if ($accion === 'expulsar' && $esCreador) {
        $idEquipoExp = (int) ($_POST['id_equipo'] ?? 0);
        $stmtEqExp   = $pdo->prepare("SELECT ef.id_usuario, ef.nombre_equipo FROM equipos_fantasy ef WHERE ef.id_equipo = ?");
        $stmtEqExp->execute([$idEquipoExp]);
        $prop = $stmtEqExp->fetch();
        if ($prop && $idEquipoExp !== $idEquipo && (int)$prop['id_usuario'] !== $idUsuario) {
            $pdo->prepare("DELETE FROM liga_miembros WHERE id_liga = ? AND id_equipo = ?")->execute([$idLiga, $idEquipoExp]);
            $flash = ['tipo' => 'ok', 'msg' => htmlspecialchars($prop['nombre_equipo']) . ' expulsado de la liga.'];
        } else {
            $flash = ['tipo' => 'error', 'msg' => 'No puedes expulsarte a ti mismo.'];
        }
    }

    if ($accion === 'transferir_admin' && $esCreador) {
        $idEquipoNuevo = (int) ($_POST['id_equipo_nuevo'] ?? 0);
        $stmtN = $pdo->prepare("SELECT ef.id_usuario FROM equipos_fantasy ef WHERE ef.id_equipo = ?");
        $stmtN->execute([$idEquipoNuevo]);
        $nuevoAdmin = $stmtN->fetch();
        if ($nuevoAdmin && $idEquipoNuevo !== $idEquipo) {
            $pdo->prepare("UPDATE ligas SET id_creador = ? WHERE id_liga = ?")->execute([$nuevoAdmin['id_usuario'], $idLiga]);
            $liga['id_creador'] = $nuevoAdmin['id_usuario'];
            $esCreador = false;
            $flash = ['tipo' => 'ok', 'msg' => 'Administracion transferida correctamente.'];
        }
    }
if ($accion === 'eliminar' && $esCreador) {
        $pdo->prepare("DELETE FROM liga_miembros WHERE id_liga = ?")->execute([$idLiga]);
        $pdo->prepare("DELETE FROM ligas WHERE id_liga = ? AND id_creador = ?")->execute([$idLiga, $idUsuario]);
        header('Location: ligas.php?eliminada=1');
        exit;
    }

    if ($accion === 'salir' && !$esCreador) {
        $pdo->prepare("DELETE FROM liga_miembros WHERE id_liga = ? AND id_equipo = ?")->execute([$idLiga, $idEquipo]);
        header('Location: ligas.php'); exit;
    }

    $stmtL->execute([$idLiga]);
    $liga = $stmtL->fetch() ?: $liga;
}

$stmtMiembros = $pdo->prepare("SELECT ef.id_equipo, ef.nombre_equipo, u.nombre AS nombre_usuario, u.id_usuario, COALESCE(SUM(pf.puntos), 0) AS puntos_totales, lm.fecha_union FROM liga_miembros lm JOIN equipos_fantasy ef ON ef.id_equipo = lm.id_equipo JOIN usuarios u ON u.id_usuario = ef.id_usuario LEFT JOIN puntos_fantasy pf ON pf.id_equipo = ef.id_equipo WHERE lm.id_liga = ? GROUP BY ef.id_equipo, ef.nombre_equipo, u.nombre, u.id_usuario, lm.fecha_union ORDER BY puntos_totales DESC");
$stmtMiembros->execute([$idLiga]);
$miembros = $stmtMiembros->fetchAll();

$stmtMisLigas = $pdo->prepare("SELECT l.id_liga, l.nombre, l.id_creador FROM ligas l JOIN liga_miembros lm ON lm.id_liga = l.id_liga WHERE lm.id_equipo = ? ORDER BY l.fecha_creacion ASC");
$stmtMisLigas->execute([$idEquipo]);
$misLigas = $stmtMisLigas->fetchAll();

$tituloPagina  = 'Ajustes de Liga';
include __DIR__ . '/../private/header.php';
?>

<?php if ($flash): ?>
<div class="flash <?= $flash['tipo'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<div class="encabezado">
    <p class="panel"><a href="ligas.php" class="volver-link">&larr; Mis Ligas</a></p>
    <h2>AJUSTES DE LIGA</h2>
    <p class="subtitulo"><?= htmlspecialchars($liga['nombre']) ?></p>
</div>

<?php if (count($misLigas) > 1): ?>
<div class="aj-liga-tabs">
    <?php foreach ($misLigas as $l): ?>
    <a href="ajustes_liga.php?liga=<?= $l['id_liga'] ?>" class="aj-liga-tab <?= $l['id_liga'] === $idLiga ? 'activo' : '' ?>">
        <?= htmlspecialchars($l['nombre']) ?>
        <?php if ((int)$l['id_creador'] === $idUsuario): ?>
            <span class="aj-badge-creador">Admin</span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="aj-grid">

    <div class="aj-col-left">

        <div class="aj-card">
            <h3 class="aj-card-titulo">Codigo de Invitacion</h3>
            <p class="aj-card-desc">Comparte este codigo para que otros se unan a tu liga.</p>
            <div class="aj-codigo-box">
                <span class="aj-codigo" id="ajCodigo"><?= htmlspecialchars($liga['codigo_invitacion']) ?></span>
                <button class="aj-btn-copiar" onclick="copiarCodigo()">Copiar</button>
            </div>
            <?php if ($esCreador): ?>
            <form method="POST" onsubmit="return confirm('El codigo anterior dejara de funcionar. Continuar?')">
                <input type="hidden" name="accion" value="regenerar_codigo">
                <button type="submit" class="aj-btn-secundario" style="margin-top:10px">Regenerar codigo</button>
            </form>
            <?php endif; ?>
        </div>

        <div class="aj-card">
            <h3 class="aj-card-titulo">Informacion</h3>
            <div class="aj-info-lista">
                <div class="aj-info-fila"><span class="aj-info-label">Creada por</span><span class="aj-info-val"><?= htmlspecialchars($liga['nombre_creador']) ?></span></div>
                <div class="aj-info-fila"><span class="aj-info-label">Fecha creacion</span><span class="aj-info-val"><?= date('d/m/Y', strtotime($liga['fecha_creacion'])) ?></span></div>
                <div class="aj-info-fila"><span class="aj-info-label">Participantes</span><span class="aj-info-val"><?= count($miembros) ?></span></div>
                <div class="aj-info-fila"><span class="aj-info-label">Estado</span><span class="aj-info-val" style="color:<?= $liga['activa'] ? '#4ade80' : '#888' ?>"><?= $liga['activa'] ? 'Activa' : 'Inactiva' ?></span></div>
            </div>
        </div>

        <?php if ($esCreador): ?>
        <div class="aj-card aj-card-danger">
            <h3 class="aj-card-titulo" style="color:#ff4040;">Zona de Peligro</h3>
            <p class="aj-card-desc">Eliminar la liga borrara todos los datos. Accion irreversible.</p>
            <form method="POST" onsubmit="return confirm('Eliminar la liga permanentemente? No se puede deshacer.')">
                <input type="hidden" name="accion" value="eliminar">
                <button type="submit" class="aj-btn-danger">Eliminar liga permanentemente</button>
            </form>
        </div>
        <?php else: ?>
        <div class="aj-card">
            <h3 class="aj-card-titulo">Salir de la liga</h3>
            <p class="aj-card-desc">Podras unirte de nuevo con el codigo si te lo facilitan.</p>
            <form method="POST" onsubmit="return confirm('Salir de la liga?')">
                <input type="hidden" name="accion" value="salir">
                <button type="submit" class="aj-btn-secundario">Salir de la liga</button>
            </form>
        </div>
        <?php endif; ?>

    </div>

    <div class="aj-col-right">

        <?php if ($esCreador): ?>
        <div class="aj-card">
            <h3 class="aj-card-titulo">Editar Liga</h3>
            <form method="POST" class="aj-form">
                <input type="hidden" name="accion" value="editar">
                <div class="aj-field">
                    <label>Nombre de la liga *</label>
                    <input type="text" name="nombre" maxlength="100" required value="<?= htmlspecialchars($liga['nombre']) ?>">
                </div>
                <div class="aj-field">
                    <label>Descripcion (opcional)</label>
                    <input type="text" name="descripcion" maxlength="255" placeholder="Una frase sobre tu liga..." value="<?= htmlspecialchars($liga['descripcion'] ?? '') ?>">
                </div>
                <button type="submit" class="aj-btn-guardar">Guardar cambios</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="aj-card">
            <h3 class="aj-card-titulo">Participantes (<?= count($miembros) ?>)</h3>
            <div class="aj-miembros-lista">
                <?php foreach ($miembros as $i => $m):
                    $esMio      = (int)$m['id_equipo'] === $idEquipo;
                    $esFundador = (int)$m['id_usuario'] === (int)$liga['id_creador'];
                ?>
                <div class="aj-miembro-fila">
                    <div class="aj-miembro-pos"><?= $i + 1 ?></div>
                    <div class="aj-miembro-info">
                        <div class="aj-miembro-nombre">
                            <?= htmlspecialchars($m['nombre_equipo']) ?>
                            <?php if ($esMio): ?><span class="aj-badge-yo">Tu</span><?php endif; ?>
                            <?php if ($esFundador): ?><span class="aj-badge-creador2">Admin</span><?php endif; ?>
                        </div>
                        <div class="aj-miembro-sub"><?= htmlspecialchars($m['nombre_usuario']) ?> &middot; Unido <?= date('d/m/Y', strtotime($m['fecha_union'])) ?></div>
                    </div>
                    <div class="aj-miembro-pts"><?= number_format($m['puntos_totales'], 0, ',', '.') ?> <small>pts</small></div>
                    <?php if ($esCreador && !$esMio && !$esFundador): ?>
                    <div class="aj-miembro-acciones">
                        <form method="POST" onsubmit="return confirm('Expulsar a <?= htmlspecialchars(addslashes($m['nombre_usuario'])) ?> de la liga?')" style="margin:0">
                            <input type="hidden" name="accion" value="expulsar">
                            <input type="hidden" name="id_equipo" value="<?= (int)$m['id_equipo'] ?>">
                            <button type="submit" class="aj-btn-expulsar" title="Expulsar">Expulsar</button>
                        </form>
                        <button type="button" class="aj-btn-transferir" title="Transferir admin" onclick="abrirTransferir(<?= (int)$m['id_equipo'] ?>, '<?= htmlspecialchars(addslashes($m['nombre_usuario'])) ?>')">Admin</button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($miembros)): ?>
                <p style="color:#666;font-size:0.85rem;padding:12px 0;">Aun no hay participantes.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<div class="modal-overlay" id="modalTransferir">
    <div class="modal" style="max-width:400px;">
        <div class="modal-header">
            <div>
                <div class="modal-slot-label">Administracion</div>
                <h3>Transferir admin</h3>
            </div>
            <button class="modal-cerrar" onclick="cerrarTransferir()">X</button>
        </div>
        <div class="modal-body" style="padding:28px;">
            <p style="font-size:0.88rem;color:#aaa;line-height:1.6;margin-bottom:20px;">
                Vas a transferir la administracion de la liga a
                <strong style="color:#fff;" id="transferirNombre"></strong>.
                Tu perderas los permisos de administrador.
            </p>
            <form method="POST" id="formTransferir">
                <input type="hidden" name="accion" value="transferir_admin">
                <input type="hidden" name="id_equipo_nuevo" id="transferirIdEquipo" value="">
                <div style="display:flex;gap:10px;">
                    <button type="button" style="flex:1;padding:12px;background:none;border:1px solid #333;color:#888;font-family:inherit;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:1px;cursor:pointer;" onclick="cerrarTransferir()">Cancelar</button>
                    <button type="submit" style="flex:1;padding:12px;background:#f59e0b;border:none;color:#000;font-family:inherit;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:1px;cursor:pointer;">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/ajustes_liga.css">

<script src="../js/ajustes_liga.js"></script>

<?php include __DIR__ . '/../private/footer.php'; ?>
