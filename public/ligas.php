<?php
require_once __DIR__ . '/../private/funciones_auth.php';
require_once __DIR__ . '/../private/iconos.php';
requiereAutenticacion();

$pdo       = getDB();
$idUsuario = (int) $_SESSION['id_usuario'];

$equipo = obtenerOCrearEquipo($idUsuario, $pdo);
$idEquipo = (int) $equipo['id_equipo'];

$flash    = null;
$vistaLiga = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        if (strlen($nombre) < 3) {
            $flash = ['tipo' => 'error', 'msg' => 'El nombre debe tener al menos 3 caracteres.'];
        } else {
            do {
                $codigo = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                $chk = $pdo->prepare("SELECT id_liga FROM ligas WHERE codigo_invitacion = ?");
                $chk->execute([$codigo]);
            } while ($chk->fetch());

            $pdo->prepare("INSERT INTO ligas (nombre, descripcion, codigo_invitacion, id_creador) VALUES (?, ?, ?, ?)")
                ->execute([$nombre, $descripcion ?: null, $codigo, $idUsuario]);
            $idLigaNueva = (int) $pdo->lastInsertId();
            $pdo->prepare("INSERT IGNORE INTO liga_miembros (id_liga, id_equipo) VALUES (?, ?)")
                ->execute([$idLigaNueva, $idEquipo]);
            $flash = ['tipo' => 'ok', 'msg' => '✓ Liga "' . htmlspecialchars($nombre) . '" creada. Código de invitación: <strong>' . $codigo . '</strong>'];
        }
    }

    if ($accion === 'unirse') {
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        if (strlen($codigo) < 6) {
            $flash = ['tipo' => 'error', 'msg' => 'Introduce un código válido.'];
        } else {
            $stmtL = $pdo->prepare("SELECT id_liga, nombre FROM ligas WHERE codigo_invitacion = ? AND activa = 1");
            $stmtL->execute([$codigo]);
            $liga = $stmtL->fetch();
            if (!$liga) {
                $flash = ['tipo' => 'error', 'msg' => 'Código no válido o liga inactiva.'];
            } else {
                $stmtYa = $pdo->prepare("SELECT id FROM liga_miembros WHERE id_liga = ? AND id_equipo = ?");
                $stmtYa->execute([$liga['id_liga'], $idEquipo]);
                if ($stmtYa->fetch()) {
                    $flash = ['tipo' => 'error', 'msg' => 'Ya eres miembro de "' . htmlspecialchars($liga['nombre']) . '".'];
                } else {
                    $pdo->prepare("INSERT INTO liga_miembros (id_liga, id_equipo) VALUES (?, ?)")->execute([$liga['id_liga'], $idEquipo]);
                    $flash = ['tipo' => 'ok', 'msg' => '✓ Te has unido a "' . htmlspecialchars($liga['nombre']) . '".'];
                }
            }
        }
    }

    if ($accion === 'salir') {
        $idLiga = (int) ($_POST['id_liga'] ?? 0);
        $stmtC = $pdo->prepare("SELECT id_creador FROM ligas WHERE id_liga = ?");
        $stmtC->execute([$idLiga]);
        $ld = $stmtC->fetch();
        if ($ld && (int)$ld['id_creador'] === $idUsuario) {
            $flash = ['tipo' => 'error', 'msg' => 'Eres el creador. Elimina la liga si quieres salir.'];
        } else {
            $pdo->prepare("DELETE FROM liga_miembros WHERE id_liga = ? AND id_equipo = ?")->execute([$idLiga, $idEquipo]);
            $flash = ['tipo' => 'ok', 'msg' => '✓ Has salido de la liga.'];
        }
    }

    if ($accion === 'eliminar') {
        $idLiga = (int) ($_POST['id_liga'] ?? 0);
        $stmtC = $pdo->prepare("SELECT id_liga FROM ligas WHERE id_liga = ? AND id_creador = ?");
        $stmtC->execute([$idLiga, $idUsuario]);
        if ($stmtC->fetch()) {
            $pdo->prepare("DELETE FROM liga_miembros WHERE id_liga = ?")->execute([$idLiga]);
            $pdo->prepare("DELETE FROM ligas WHERE id_liga = ?")->execute([$idLiga]);
            $flash = ['tipo' => 'ok', 'msg' => '✓ Liga eliminada.'];
        } else {
            $flash = ['tipo' => 'error', 'msg' => 'No tienes permiso para eliminar esta liga.'];
        }
    }
}

$verLiga = (int) ($_GET['liga'] ?? 0);
if ($verLiga > 0) {
    $stmtV = $pdo->prepare("SELECT l.*, u.nombre AS nombre_creador FROM ligas l JOIN usuarios u ON u.id_usuario = l.id_creador WHERE l.id_liga = ?");
    $stmtV->execute([$verLiga]);
    $vistaLiga = $stmtV->fetch();
    if ($vistaLiga) {
        $stmtM = $pdo->prepare("SELECT id FROM liga_miembros WHERE id_liga = ? AND id_equipo = ?");
        $stmtM->execute([$verLiga, $idEquipo]);
        if (!$stmtM->fetch()) $vistaLiga = null;
    }
}

$stmtMisLigas = $pdo->prepare("
    SELECT l.id_liga, l.nombre, l.descripcion, l.codigo_invitacion, l.id_creador, l.fecha_creacion,
           (SELECT COUNT(*) FROM liga_miembros lm2 WHERE lm2.id_liga = l.id_liga) AS total_miembros
    FROM ligas l
    JOIN liga_miembros lm ON lm.id_liga = l.id_liga
    WHERE lm.id_equipo = ?
    ORDER BY l.fecha_creacion DESC
");
$stmtMisLigas->execute([$idEquipo]);
$misLigas = $stmtMisLigas->fetchAll();

$miembrosLiga = [];
if ($vistaLiga) {
    $stmtMiembros = $pdo->prepare("
        SELECT ef.id_equipo, ef.nombre_equipo, u.nombre AS nombre_usuario,
               COALESCE(SUM(pf.puntos), 0) AS puntos_totales,
               COUNT(DISTINCT pf.id_carrera) AS carreras_puntuadas
        FROM liga_miembros lm
        JOIN equipos_fantasy ef ON ef.id_equipo = lm.id_equipo
        JOIN usuarios u ON u.id_usuario = ef.id_usuario
        LEFT JOIN puntos_fantasy pf ON pf.id_equipo = ef.id_equipo
        WHERE lm.id_liga = ?
        GROUP BY ef.id_equipo, ef.nombre_equipo, u.nombre, u.id_usuario
        ORDER BY puntos_totales DESC
    ");
    $stmtMiembros->execute([$vistaLiga['id_liga']]);
    $miembrosLiga = $stmtMiembros->fetchAll();
}

$tituloPagina = 'Ligas';
include __DIR__ . '/../private/header.php';
?>

<?php if ($flash): ?>
<div class="flash <?= $flash['tipo'] ?>"><?= $flash['msg'] ?></div>
<?php endif; ?>

<?php if ($vistaLiga): ?>
<div class="encabezado">
    <p class="panel"><a href="ligas.php" class="volver-link">← Mis Ligas</a></p>
    <h2><?= htmlspecialchars($vistaLiga['nombre']) ?></h2>
    <?php if ($vistaLiga['descripcion']): ?>
        <p class="liga-desc-header"><?= htmlspecialchars($vistaLiga['descripcion']) ?></p>
    <?php endif; ?>
</div>

<div class="liga-detalle-meta">
    <div class="liga-meta-item">
        <span class="liga-meta-label">Código de invitación</span>
        <span class="liga-codigo-grande"><?= htmlspecialchars($vistaLiga['codigo_invitacion']) ?></span>
        <span class="liga-meta-hint">Compártelo para que otros se unan</span>
    </div>
    <div class="liga-meta-item">
        <span class="liga-meta-label">Creada por</span>
        <span class="liga-meta-valor"><?= htmlspecialchars($vistaLiga['nombre_creador']) ?></span>
    </div>
    <div class="liga-meta-item">
        <span class="liga-meta-label">Participantes</span>
        <span class="liga-meta-valor"><?= count($miembrosLiga) ?> equipo<?= count($miembrosLiga) !== 1 ? 's' : '' ?></span>
    </div>
    <div class="liga-meta-acciones">
        <?php if ((int)$vistaLiga['id_creador'] === $idUsuario): ?>
            <form method="POST" action="ligas.php" onsubmit="return confirm('¿Eliminar esta liga permanentemente?')">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_liga" value="<?= $vistaLiga['id_liga'] ?>">
                <button type="submit" class="btn-liga-peligro">Eliminar liga</button>
            </form>
        <?php else: ?>
            <form method="POST" action="ligas.php" onsubmit="return confirm('¿Salir de esta liga?')">
                <input type="hidden" name="accion" value="salir">
                <input type="hidden" name="id_liga" value="<?= $vistaLiga['id_liga'] ?>">
                <button type="submit" class="btn-liga-secundario">Salir de la liga</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="clasif-tabla-wrap">
    <table class="clasif-tabla">
        <thead>
            <tr>
                <th class="col-pos">POS</th>
                <th class="col-equipo">Equipo</th>
                <th class="col-piloto">Manager</th>
                <th class="col-carreras">Carreras</th>
                <th class="col-pts">Puntos</th>
                <th class="col-dif">Diferencia</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $puntosLider = $miembrosLiga[0]['puntos_totales'] ?? 0;
        foreach ($miembrosLiga as $i => $fila):
            $pos      = $i + 1;
            $esMio    = (int)$fila['id_equipo'] === $idEquipo;
            $dif      = $puntosLider - $fila['puntos_totales'];
            $rowClass = $esMio ? 'fila-mia' : '';
            if ($pos === 1) $rowClass .= ' fila-lider';
        ?>
            <tr class="<?= trim($rowClass) ?>">
                <td class="col-pos">
                    <?php if ($pos === 1): ?>
                        <span class="trofeo"><?= icono('trofeo', 'icono-trofeo', 22) ?></span>
                    <?php elseif ($pos === 2): ?>
                        <span class="medalla plata">2</span>
                    <?php elseif ($pos === 3): ?>
                        <span class="medalla bronce">3</span>
                    <?php else: ?>
                        <span class="num-pos"><?= $pos ?></span>
                    <?php endif; ?>
                </td>
                <td class="col-equipo">
                    <div class="equipo-nombre-wrap">
                        <?php if ($esMio): ?><span class="badge-yo">TÚ</span><?php endif; ?>
                        <strong><?= htmlspecialchars($fila['nombre_equipo']) ?></strong>
                    </div>
                </td>
                <td class="col-piloto"><?= htmlspecialchars($fila['nombre_usuario']) ?></td>
                <td class="col-carreras"><?= (int)$fila['carreras_puntuadas'] ?></td>
                <td class="col-pts"><span class="pts-valor"><?= number_format($fila['puntos_totales'], 0, ',', '.') ?></span></td>
                <td class="col-dif">
                    <?= $pos === 1
                        ? '<span class="lider-tag">LÍDER</span>'
                        : '<span class="dif-neg">-' . number_format($dif, 0, ',', '.') . '</span>' ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($miembrosLiga)): ?>
            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--color-texto-muted)">Sin participantes todavía.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php else: ?>
<div class="encabezado">
    <p class="panel">Competición privada</p>
    <h2>LIGAS</h2>
</div>

<div class="mercado-stats">
    <div class="mercado-stat">
        <p>Mis ligas</p>
        <strong><?= count($misLigas) ?></strong>
    </div>
    <div class="mercado-stat">
        <p>Creadas por mí</p>
        <strong><?= count(array_filter($misLigas, function($l) use ($idUsuario) { return (int)$l['id_creador'] === $idUsuario; })) ?></strong>
    </div>
    <div class="mercado-stat">
        <p>Total participantes</p>
        <strong><?= array_sum(array_column($misLigas, 'total_miembros')) ?></strong>
    </div>
</div>

<div class="ligas-acciones-wrap">
    <div class="liga-accion-card">
        <div class="liga-accion-header">
            <span class="liga-accion-icono">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                    <path d="M4 22h16"/>
                    <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                    <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                    <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                </svg>
            </span>
            <h3>Crear liga</h3>
        </div>
        <p class="liga-accion-desc">Crea tu propia liga privada y comparte el código con tus amigos.</p>
        <form method="POST" action="ligas.php" class="liga-form">
            <input type="hidden" name="accion" value="crear">
            <div class="liga-form-field">
                <label>Nombre de la liga</label>
                <input type="text" name="nombre" maxlength="100" placeholder="Ej: Liga de la oficina" required>
            </div>
            <div class="liga-form-field">
                <label>Descripción <span class="opcional">(opcional)</span></label>
                <input type="text" name="descripcion" maxlength="255" placeholder="Una frase sobre tu liga...">
            </div>
            <button type="submit" class="btn-liga-crear">+ Crear liga</button>
        </form>
    </div>

    <div class="liga-accion-card">
        <div class="liga-accion-header">
            <span class="liga-accion-icono">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                </svg>
            </span>
            <h3>Unirse a una liga</h3>
        </div>
        <p class="liga-accion-desc">¿Te han enviado un código? Introdúcelo aquí para unirte.</p>
        <form method="POST" action="ligas.php" class="liga-form">
            <input type="hidden" name="accion" value="unirse">
            <div class="liga-form-field">
                <label>Código de invitación</label>
                <input type="text" name="codigo" maxlength="8" placeholder="Ej: AB12CD34"
                       style="text-transform:uppercase;letter-spacing:3px;font-weight:700" required>
            </div>
            <button type="submit" class="btn-liga-unirse">Unirse →</button>
        </form>
    </div>
</div>

<div class="ligas-lista-header">
    <h3>Mis ligas</h3>
    <?php if (!empty($misLigas)): ?>
        <span class="ligas-count"><?= count($misLigas) ?> liga<?= count($misLigas) !== 1 ? 's' : '' ?></span>
    <?php endif; ?>
</div>

<?php if (empty($misLigas)): ?>
<div class="ligas-vacia">
    <p>Todavía no perteneces a ninguna liga.</p>
    <p>Crea una nueva o únete con un código de invitación.</p>
</div>
<?php else: ?>
<div class="ligas-grid">
    <?php foreach ($misLigas as $liga):
        $esCreadorLiga = ((int)$liga['id_creador'] === $idUsuario);
    $esMiLiga      = $esCreadorLiga;
    ?>
    <div class="liga-card">
        <div class="liga-card-top">
            <div>
                <h4 class="liga-card-nombre"><?= htmlspecialchars($liga['nombre']) ?></h4>
                <?php if ($liga['descripcion']): ?>
                    <p class="liga-card-desc"><?= htmlspecialchars($liga['descripcion']) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($esMiLiga): ?><span class="liga-badge-creador">Creador</span><?php endif; ?>
        </div>
        <div class="liga-card-stats">
            <div class="liga-stat">
                <span class="liga-stat-label">Participantes</span>
                <span class="liga-stat-val"><?= (int)$liga['total_miembros'] ?></span>
            </div>
            <div class="liga-stat">
                <span class="liga-stat-label">Código</span>
                <span class="liga-codigo"><?= htmlspecialchars($liga['codigo_invitacion']) ?></span>
            </div>
            <div class="liga-stat">
                <span class="liga-stat-label">Creada</span>
                <span class="liga-stat-val"><?= date('d/m/Y', strtotime($liga['fecha_creacion'])) ?></span>
            </div>
        </div>
        <div class="liga-card-footer">
            <a href="ligas.php?liga=<?= $liga['id_liga'] ?>" class="btn-liga-ver">Ver clasificación →</a>
            <?php if ($esCreadorLiga): ?>
                <a href="ajustes_liga.php?liga=<?= $liga['id_liga'] ?>" class="btn-liga-ajustes" title="Ajustes de la liga">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l-.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    Ajustes
                </a>
            <?php endif; ?>
            <?php if ($esMiLiga): ?>
                <form method="POST" action="ligas.php" onsubmit="return confirm('¿Eliminar esta liga?')" style="display:inline">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id_liga" value="<?= $liga['id_liga'] ?>">
                    <button type="submit" class="btn-liga-peligro-sm">Eliminar</button>
                </form>
            <?php else: ?>
                <form method="POST" action="ligas.php" onsubmit="return confirm('¿Salir de esta liga?')" style="display:inline">
                    <input type="hidden" name="accion" value="salir">
                    <input type="hidden" name="id_liga" value="<?= $liga['id_liga'] ?>">
                    <button type="submit" class="btn-liga-salir-sm">Salir</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../private/footer.php'; ?>

<link rel="stylesheet" href="../css/ligas.css">
