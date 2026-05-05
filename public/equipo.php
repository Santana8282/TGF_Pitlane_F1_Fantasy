<?php

require_once __DIR__ . '/../private/funciones_auth.php';
require_once __DIR__ . '/../private/puntuacion.php';
requiereAutenticacion();

$pdo        = getDB();
$idUsuario  = (int) $_SESSION['id_usuario'];

$equipo = obtenerOCrearEquipo($idUsuario, $pdo);

$idEquipo = (int) $equipo['id_equipo'];

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion   = $_POST['accion']    ?? '';
    $idPiloto = (int) ($_POST['id_piloto'] ?? 0);
    $slot     = (int) ($_POST['slot']      ?? 0);  

    if ($accion === 'añadir' && $idPiloto > 0 && in_array($slot, [1, 2])) {

        $stmtP = $pdo->prepare("SELECT id_piloto, nombre FROM pilotos WHERE id_piloto = ?");
        $stmtP->execute([$idPiloto]);
        $piloto = $stmtP->fetch();

        if (!$piloto) {
            $flash = ['tipo' => 'error', 'msg' => 'Piloto no encontrado.'];

        } else {
            $stmtCheck = $pdo->prepare("SELECT id_piloto_equipo FROM pilotos_equipo_fantasy WHERE id_equipo = ? AND id_piloto = ?");
            $stmtCheck->execute([$idEquipo, $idPiloto]);

            if (!$stmtCheck->fetch()) {
                $flash = ['tipo' => 'error', 'msg' => 'Ese piloto no está en tu plantilla. Fíchalo primero.'];
            } else {
                $otroSlot = $slot === 1 ? 2 : 1;
                $stmtOtro = $pdo->prepare("SELECT id_piloto FROM pilotos_equipo_fantasy WHERE id_equipo = ? AND slot = ?");
                $stmtOtro->execute([$idEquipo, $otroSlot]);
                $idOtroSlot = (int) ($stmtOtro->fetchColumn() ?: 0);
                if ($idOtroSlot === $idPiloto) {
                    $flash = ['tipo' => 'error', 'msg' => 'Ese piloto ya ocupa el otro slot.'];
                } else {

                    $pdo->prepare("UPDATE pilotos_equipo_fantasy SET slot = NULL, es_capitan = 0 WHERE id_equipo = ? AND slot = ?")->execute([$idEquipo, $slot]);

                    $pdo->prepare("UPDATE pilotos_equipo_fantasy SET slot = ?, es_capitan = ? WHERE id_equipo = ? AND id_piloto = ?")->execute([$slot, ($slot === 1 ? 1 : 0), $idEquipo, $idPiloto]);
                    $flash = ['tipo' => 'ok', 'msg' => htmlspecialchars($piloto['nombre']) . ' asignado al Slot ' . $slot . '!'];
                }
            }
        }

    } elseif ($accion === 'quitar' && in_array($slot, [1, 2])) {

        $stmtSlot = $pdo->prepare("SELECT pef.id_piloto, p.nombre FROM pilotos_equipo_fantasy pef JOIN pilotos p ON p.id_piloto = pef.id_piloto WHERE pef.id_equipo = ? AND pef.slot = ?");
        $stmtSlot->execute([$idEquipo, $slot]);
        $rowSlot = $stmtSlot->fetch();
        if ($rowSlot) {
            $pdo->prepare("UPDATE pilotos_equipo_fantasy SET slot = NULL, es_capitan = 0 WHERE id_equipo = ? AND slot = ?")->execute([$idEquipo, $slot]);
            $flash = ['tipo' => 'ok', 'msg' => htmlspecialchars($rowSlot['nombre']) . ' retirado del Slot ' . $slot . '.'];
        } else {
            $flash = ['tipo' => 'error', 'msg' => 'El slot ya estaba vacio.'];
        }

    } elseif ($accion === 'fichar' && $idPiloto > 0) {

        if (!ventanaMercadoAbierta($pdo)) {
            $flash = ['tipo' => 'error', 'msg' => 'El mercado está cerrado en este momento.'];
        } else {
        $proxCarrera   = getProximaCarrera();
        $idCarreraProx = $proxCarrera ? (int)$proxCarrera['id_carrera'] : 0;

        if ($idCarreraProx === 0) {
            $flash = ['tipo' => 'error', 'msg' => 'No hay carreras próximas. El mercado está cerrado.'];
        } else {
            $res = ficharPiloto($idEquipo, $idPiloto, $idCarreraProx);
            if ($res['ok']) {
                $stmtNom = $pdo->prepare("SELECT nombre FROM pilotos WHERE id_piloto = ?");
                $stmtNom->execute([$idPiloto]);
                $nomPiloto = $stmtNom->fetchColumn();
                $costeMsg  = $res['coste'] > 0 ? ' (-' . $res['coste'] . ' pts en la próxima carrera)' : '';
                $equipo['presupuesto'] -= 0; 
                $flash = ['tipo' => 'ok', 'msg' => '¡' . htmlspecialchars($nomPiloto) . ' fichado!' . $costeMsg];
            } else {
                $flash = ['tipo' => 'error', 'msg' => $res['error']];
            }
        }
        } 

    } elseif ($accion === 'liberar' && $idPiloto > 0) {

        $proxCarrera   = getProximaCarrera();
        $idCarreraProx = $proxCarrera ? (int)$proxCarrera['id_carrera'] : 0;

        if ($idCarreraProx === 0) {

            $stmtP = $pdo->prepare("SELECT nombre, precio FROM pilotos WHERE id_piloto = ?");
            $stmtP->execute([$idPiloto]);
            $piloto = $stmtP->fetch();
            $stmtDel = $pdo->prepare("DELETE FROM pilotos_equipo_fantasy WHERE id_equipo = ? AND id_piloto = ?");
            $stmtDel->execute([$idEquipo, $idPiloto]);
            if ($stmtDel->rowCount() > 0 && $piloto) {
                $dev = (int)round($piloto['precio'] * 0.8);
                $pdo->prepare("UPDATE equipos_fantasy SET presupuesto = presupuesto + ? WHERE id_equipo = ?")->execute([$dev, $idEquipo]);

                $pdo->prepare("UPDATE pilotos_equipo_fantasy SET slot = NULL, es_capitan = 0 WHERE id_equipo = ? AND id_piloto = ?")->execute([$idEquipo, $idPiloto]);
                $flash = ['tipo' => 'ok', 'msg' => htmlspecialchars($piloto['nombre']) . ' liberado. +' . number_format($dev / 1000000, 1) . 'M€ (80%).'];
            } else {
                $flash = ['tipo' => 'error', 'msg' => 'No se pudo liberar al piloto.'];
            }
        } else {
            $res = liberarPiloto($idEquipo, $idPiloto, $idCarreraProx);
            if ($res['ok']) {

                $pdo->prepare("UPDATE pilotos_equipo_fantasy SET slot = NULL, es_capitan = 0 WHERE id_equipo = ? AND id_piloto = ?")->execute([$idEquipo, $idPiloto]);
                $flash = ['tipo' => 'ok', 'msg' => 'Piloto liberado. +' . number_format($res['devolucion'] / 1000000, 1) . 'M€ devueltos (80%).'];
            } else {
                $flash = ['tipo' => 'error', 'msg' => $res['error']];
            }
        }

    } elseif ($accion === 'fichar_escuderia') {
        $idEsc = (int) ($_POST['id_escuderia'] ?? 0);
        if ($idEsc > 0) {
            $res = ficharEscuderia($idEquipo, $idEsc);
            if ($res['ok']) {
                $stmtNomEsc = $pdo->prepare("SELECT nombre FROM escuderias WHERE id_escuderia = ?");
                $stmtNomEsc->execute([$idEsc]);
                $nomEsc = $stmtNomEsc->fetchColumn() ?: 'Escudería';
                $flash = ['tipo' => 'ok', 'msg' => '¡' . htmlspecialchars($nomEsc) . ' fichada por ' . number_format($res['precio'] / 1000000, 1) . 'M€!'];
            } else {
                $flash = ['tipo' => 'error', 'msg' => $res['error']];
            }
        }

    } elseif ($accion === 'liberar_escuderia') {
        $idEsc = (int) ($_POST['id_escuderia'] ?? 0);
        if ($idEsc > 0) {
            $res = liberarEscuderia($idEquipo, $idEsc);
            if ($res['ok']) {
                $flash = ['tipo' => 'ok', 'msg' => htmlspecialchars($res['nombre']) . ' liberada. +' . number_format($res['devolucion'] / 1000000, 1) . 'M€ devueltos (80%).'];
            } else {
                $flash = ['tipo' => 'error', 'msg' => $res['error']];
            }
        }
    }
}

$stmtPres = $pdo->prepare("SELECT presupuesto FROM equipos_fantasy WHERE id_equipo = ?");
$stmtPres->execute([$idEquipo]);
$presupuesto = (int) $stmtPres->fetchColumn();

$stmtMis = $pdo->prepare("
    SELECT p.id_piloto, p.nombre, p.numero, p.precio, p.imagen_url,
           p.id_escuderia, e.nombre AS escuderia
    FROM pilotos_equipo_fantasy pef
    JOIN pilotos    p ON p.id_piloto    = pef.id_piloto
    JOIN escuderias e ON e.id_escuderia = p.id_escuderia
    WHERE pef.id_equipo = ?
    ORDER BY pef.fecha_inclusion ASC
");
$stmtMis->execute([$idEquipo]);
$misPilotos = $stmtMis->fetchAll();

$puntosTotalesPilotos = getPuntosTotalesPilotos();

$stmtMiEsc = $pdo->prepare("
    SELECT e.id_escuderia, e.nombre, e.precio_base, e.logo_url
    FROM escuderia_equipo_fantasy ef
    JOIN escuderias e ON e.id_escuderia = ef.id_escuderia
    WHERE ef.id_equipo = ? LIMIT 1
");
$stmtMiEsc->execute([$idEquipo]);
$miEscuderia = $stmtMiEsc->fetch() ?: null;

$puntosEscuderia = $miEscuderia ? getPuntosEscuderiaEquipo($idEquipo) : 0;

$todasEscuderias = $pdo->query("SELECT id_escuderia, nombre, precio_base, logo_url FROM escuderias ORDER BY nombre ASC")->fetchAll();

$todosLos = $pdo->query("
    SELECT p.id_piloto, p.nombre, p.numero, p.precio, p.imagen_url,
           e.nombre AS escuderia
    FROM pilotos p
    JOIN escuderias e ON e.id_escuderia = p.id_escuderia
    ORDER BY p.precio DESC
")->fetchAll();

$idsFichados = array_column($misPilotos, 'id_piloto');

$stmtAlin = $pdo->prepare(
    "SELECT slot, id_piloto FROM pilotos_equipo_fantasy
      WHERE id_equipo = ? AND slot IN (1, 2)"
);
$stmtAlin->execute([$idEquipo]);
$alineacion = [];
foreach ($stmtAlin->fetchAll() as $row) {
    $alineacion[(int)$row['slot']] = (int)$row['id_piloto'];
}
$slot1Id = $alineacion[1] ?? null;
$slot2Id = $alineacion[2] ?? null;

function pilotoPorId(array $lista, int $id): ?array {
    foreach ($lista as $p) {
        if ((int)$p['id_piloto'] === $id) return $p;
    }
    return null;
}

$pilotSlot1 = $slot1Id ? pilotoPorId($todosLos, $slot1Id) : null;
$pilotSlot2 = $slot2Id ? pilotoPorId($todosLos, $slot2Id) : null;

$coloresEscuderia = [
    'McLaren Mastercard F1 Team'         => '#FF8700',
    'Mercedes-AMG Petronas F1 Team'      => '#27F4D2',
    'Oracle Red Bull Racing'             => '#3671C6',
    'Scuderia Ferrari HP'                => '#E8002D',
    'Atlassian Williams F1 Team'         => '#005AFF',
    'Visa Cash App Racing Bulls F1 Team' => '#6692FF',
    'Aston Martin Aramco F1 Team'        => '#006F62',
    'TGR Haas F1 Team'                   => '#B6BABD',
    'Audi Revolut F1 Team'               => '#D0D0D0',
    'BWT Alpine F1 Team'                 => '#FF87BC',
    'Cadillac Formula 1 Team'            => '#C8102E',

    'McLaren Mercedes'       => '#FF8700',
    'Mercedes AMG Petronas'  => '#27F4D2',
    'Williams Racing'        => '#005AFF',
    'Visa Cash App RB'       => '#6692FF',
    'Aston Martin Aramco'    => '#006F62',
    'MoneyGram Haas F1 Team' => '#B6BABD',
    'Audi F1 Team'           => '#D0D0D0',
    'Cadillac F1 Team'       => '#C8102E',
];

$logosEscuderia = [
    'Oracle Red Bull Racing'             => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2025/red-bull-racing.png',
    'Scuderia Ferrari HP'                => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2025/ferrari.png',
    'Mercedes-AMG Petronas F1 Team'      => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2025/mercedes.png',
    'McLaren Mastercard F1 Team'         => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2025/mclaren.png',
    'Aston Martin Aramco F1 Team'        => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2025/aston-martin.png',
    'BWT Alpine F1 Team'                 => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2025/alpine.png',
    'Atlassian Williams F1 Team'         => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2025/williams.png',
    'Visa Cash App Racing Bulls F1 Team' => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2025/racing-bulls.png',
    'TGR Haas F1 Team'                   => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2025/haas.png',
    'Audi Revolut F1 Team'               => 'https://media.formula1.com/image/upload/c_lfill,w_512/q_auto/d_common:f1:2026:fallback:car:2026fallbackcarright.webp/v1740000001/common/f1/2026/audi/2026audicarright.webp',
    'Cadillac Formula 1 Team'            => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2026/cadillac.png',
];

function colorEscuderia(string $nombre, array $mapa): string {
    return $mapa[$nombre] ?? '#888888';
}

$stmtCarrera = $pdo->query("SELECT nombre, fecha FROM carreras WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1");
$proximaCarrera = $stmtCarrera->fetch();
$tsProximaCarrera = $proximaCarrera ? strtotime($proximaCarrera['fecha'] . ' 14:00:00') : 0;

$presupuestoInicial = 40000000;
$gastado            = $presupuestoInicial - $presupuesto;
$porcentajeGastado  = max(0, min(100, round(($gastado / $presupuestoInicial) * 100)));

function formatNombre(string $nombre): string {
    $partes   = explode(' ', trim($nombre));
    $apellido = end($partes);
    $inicial  = isset($partes[0]) ? $partes[0][0] . '.' : '';
    return $inicial . ' ' . $apellido;
}

$tituloPagina = 'Mi Equipo';
include __DIR__ . '/../private/header.php';
?>

<?php if ($flash): ?>
<div class="flash <?= $flash['tipo'] ?>">
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="equipo-encabezado">
    <p class="panel">// Garaje Principal</p>
    <h2>MI EQUIPO</h2>
    <p class="subtitulo">Temporada 2026 &nbsp;·&nbsp; <?= htmlspecialchars($equipo['nombre_equipo']) ?></p>
</div>

<div class="stats-bar">

    <div class="stat-card">
        <p class="stat-label">Presupuesto Disponible</p>
        <div class="stat-valor"><?= number_format($presupuesto / 1000000, 1) ?> <small>M€</small></div>
        <div class="stat-delta <?= $presupuesto >= $presupuestoInicial * 0.5 ? 'up' : 'down' ?>">
            <?= $presupuesto >= $presupuestoInicial * 0.5 ? '▲' : '▼' ?> <?= $porcentajeGastado ?>% gastado
        </div>
        <div class="stat-barra">
            <div class="stat-barra-fill" style="width: <?= 100 - $porcentajeGastado ?>%"></div>
        </div>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pilotos en Plantilla</p>
        <div class="stat-valor"><?= count($misPilotos) ?> <small>/ 10</small></div>
    </div>

    <div class="stat-card">
        <p class="stat-label">Slot 1 — Capitán ×2</p>
        <div class="stat-valor" style="font-size:18px; margin-top:4px;">
            <?= $pilotSlot1 ? htmlspecialchars(formatNombre($pilotSlot1['nombre'])) : '<span style="color:#333;">Vacío</span>' ?>
        </div>
    </div>

    <div class="stat-card">
        <p class="stat-label">Slot 2 — Segundo Piloto</p>
        <div class="stat-valor" style="font-size:18px; margin-top:4px;">
            <?= $pilotSlot2 ? htmlspecialchars(formatNombre($pilotSlot2['nombre'])) : '<span style="color:#333;">Vacío</span>' ?>
        </div>
    </div>

    <?php if ($proximaCarrera): ?>
    <div class="stat-card stat-proximo">
        <p class="stat-label">Próxima Carrera</p>
        <div class="stat-valor" style="font-size:15px;"><?= htmlspecialchars($proximaCarrera['nombre']) ?></div>
        <div class="stat-countdown" id="countdown" data-ts="<?= $tsProximaCarrera ?>">--D : --H : --M : --S</div>
    </div>
    <?php endif; ?>

</div>

<div class="seccion-titulo" style="margin-bottom: 28px;">
    <div class="linea"></div>
    <h3>Garaje Principal <span class="seccion-sub">// Alineación activa</span></h3>
</div>

<div class="garaje-grid-tres">

<?php foreach ([1 => 'Capitán ×2', 2 => 'Segundo Piloto'] as $numSlot => $rolSlot):
    $pilotSlot = $numSlot === 1 ? $pilotSlot1 : $pilotSlot2;
?>

    <?php if ($pilotSlot): ?>
    <div class="slot-piloto ocupado">
        <?php if ($pilotSlot['imagen_url']): ?>
        <div class="slot-bg-img" style="background-image: url('<?= htmlspecialchars($pilotSlot['imagen_url']) ?>')"></div>
        <?php endif; ?>
        <div class="slot-overlay"></div>
        <div class="badge-activo">Activo</div>

        <form method="post" style="position:absolute; top:14px; left:14px; z-index:10;">
            <input type="hidden" name="accion"    value="quitar">
            <input type="hidden" name="slot"      value="<?= $numSlot ?>">
            <input type="hidden" name="id_piloto" value="<?= (int)$pilotSlot['id_piloto'] ?>">
            <button type="submit" class="btn-quitar">✕ Quitar del slot</button>
        </form>

        <div class="slot-info">
            <div class="slot-rol">Piloto <?= $numSlot ?> // <?= $rolSlot ?></div>
            <div class="slot-piloto-row">
                <div style="display:flex; align-items:center;">
                    <?php if ($pilotSlot['imagen_url']): ?>
                    <img src="<?= htmlspecialchars($pilotSlot['imagen_url']) ?>"
                         alt="<?= htmlspecialchars($pilotSlot['nombre']) ?>"
                         class="slot-avatar">
                    <?php endif; ?>
                    <div>
                        <div class="slot-nombre"><?= htmlspecialchars(formatNombre($pilotSlot['nombre'])) ?></div>
                        <div class="slot-equipo-row">
                            <div class="slot-equipo-color"
                                 style="background-color: <?= colorEscuderia($pilotSlot['escuderia'], $coloresEscuderia) ?>;"></div>
                            <span class="slot-equipo-nombre"><?= htmlspecialchars($pilotSlot['escuderia']) ?></span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="slot-forma-label">Nº</div>
                    <div class="slot-forma-valor"><?= (int)$pilotSlot['numero'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <div class="slot-piloto vacio" onclick="abrirModal(<?= $numSlot ?>)" title="Clic para asignar piloto">
        <div class="slot-bg-pattern"></div>
        <div class="slot-placeholder">
            <div class="slot-rol">Piloto <?= $numSlot ?> // <?= $rolSlot ?></div>
            <div class="slot-add-row">
                <div class="slot-add-box">+</div>
                <div class="slot-add-texto">
                    <h4>Seleccionar Piloto</h4>
                    <p>Haz clic para asignar desde tu plantilla</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php endforeach; ?>


    <?php if ($miEscuderia): ?>
    <?php
        $escColor = $coloresEscuderia[$miEscuderia['nombre']] ?? '#888888';
        $escLogo  = $logosEscuderia[$miEscuderia['nombre']] ?? null;
    ?>
    <div class="slot-piloto slot-escuderia ocupado" style="border-color: <?= $escColor ?>66;">
        <div class="slot-overlay" style="background: linear-gradient(to top, <?= $escColor ?>33 0%, transparent 60%);"></div>
        <div class="badge-activo" style="background:<?= $escColor ?>;">Escudería</div>

        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:40px 12px;">
            <?php if ($escLogo): ?>
            <img src="<?= htmlspecialchars($escLogo) ?>"
                 alt="<?= htmlspecialchars($miEscuderia['nombre']) ?>"
                 style="max-width:100%;max-height:100px;object-fit:contain;"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
            <span style="display:none;font-size:28px;font-weight:900;font-style:italic;color:<?= $escColor ?>;opacity:0.5;text-align:center;">
                <?= htmlspecialchars(implode(' ', array_slice(explode(' ', $miEscuderia['nombre']), 0, 2))) ?>
            </span>
            <?php else: ?>
            <span style="font-size:28px;font-weight:900;font-style:italic;color:<?= $escColor ?>;opacity:0.5;text-align:center;">
                <?= htmlspecialchars(implode(' ', array_slice(explode(' ', $miEscuderia['nombre']), 0, 2))) ?>
            </span>
            <?php endif; ?>
        </div>

        <form method="post" style="position:absolute; top:14px; left:14px; z-index:10;">
            <input type="hidden" name="accion" value="liberar_escuderia">
            <input type="hidden" name="id_escuderia" value="<?= (int)$miEscuderia['id_escuderia'] ?>">
            <button type="button" class="btn-quitar"
                onclick="abrirModalLiberarEscuderia(
                    <?= (int)$miEscuderia['id_escuderia'] ?>,
                    '<?= htmlspecialchars(addslashes($miEscuderia['nombre'])) ?>',
                    <?= number_format($miEscuderia['precio_base'] * 0.8 / 1000000, 1, '.', '') ?>
                )">✕ Liberar</button>
        </form>

        <div class="slot-info">
            <div class="slot-rol">Escudería // 1 permitida</div>
            <div class="slot-piloto-row" style="justify-content:space-between;">
                <div>
                    <div class="slot-nombre" style="font-size:13px;"><?= htmlspecialchars($miEscuderia['nombre']) ?></div>
                    <div class="slot-equipo-row" style="margin-top:4px;">
                        <div class="slot-equipo-color" style="background-color:<?= $escColor ?>;"></div>
                        <span class="slot-equipo-nombre"><?= number_format($miEscuderia['precio_base'] / 1000000, 1) ?>M€</span>
                    </div>
                </div>
                <div>
                    <div class="slot-forma-label">PTS</div>
                    <div class="slot-forma-valor" style="<?= $puntosEscuderia > 0 ? 'color:#4caf50' : ($puntosEscuderia < 0 ? 'color:var(--color-rojo)' : '') ?>">
                        <?= $puntosEscuderia > 0 ? '+' : '' ?><?= $puntosEscuderia ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <div class="slot-piloto slot-escuderia vacio" onclick="abrirModalFicharEscuderia()" title="Clic para fichar escudería">
        <div class="slot-bg-pattern"></div>
        <div class="slot-placeholder">
            <div class="slot-rol">Escudería // 1 permitida</div>
            <div class="slot-add-row">
                <div class="slot-add-box">🏎</div>
                <div class="slot-add-texto">
                    <h4>Fichar Escudería</h4>
                    <p>Haz clic para elegir tu escudería</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<div class="seccion-header">
    <div class="seccion-titulo" style="margin-bottom: 0;">
        <div class="linea" style="background-color: #333;"></div>
        <h3 style="font-size:18px;">
            Mis Pilotos <span class="seccion-sub">(<?= count($misPilotos) ?> / 10 en plantilla)</span>
        </h3>
    </div>
    <div style="display:flex; gap:10px;">
        <?php if (!$miEscuderia): ?>
        <button class="btn-comprar" style="background:#111; border:1px solid #333;" onclick="abrirModalFicharEscuderia()">🏎 Fichar Escudería</button>
        <?php endif; ?>
        <button class="btn-comprar" onclick="abrirModalFichar()">+ Fichar Piloto</button>
    </div>
</div>

<div class="pilotos-grid">

    <?php if ($miEscuderia): ?>
    <?php $escColor = $coloresEscuderia[$miEscuderia['nombre']] ?? '#888888'; ?>
    <div class="piloto-card piloto-card-escuderia" style="border-top: 3px solid <?= $escColor ?>;">
        <?php $escLogoCard = $logosEscuderia[$miEscuderia['nombre']] ?? null; ?>
        <div class="piloto-foto-wrap" style="background: linear-gradient(160deg, #0d0d0d 40%, <?= $escColor ?>18 100%); display:flex; align-items:center; justify-content:center;">
            <?php if ($escLogoCard): ?>
            <img src="<?= htmlspecialchars($escLogoCard) ?>"
                 alt="<?= htmlspecialchars($miEscuderia['nombre']) ?>"
                 style="width:80%; height:80%; object-fit:contain; padding:8px;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div style="display:none; width:100%; height:100%; align-items:center; justify-content:center; font-size:36px; font-weight:900; font-style:italic; color:<?= $escColor ?>; opacity:0.5;"><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $miEscuderia['nombre']), 0, 2))) ?></div>
            <?php else: ?>
            <div style="font-size:36px; font-weight:900; font-style:italic; color:<?= $escColor ?>; opacity:0.5;"><?= htmlspecialchars(implode(' ', array_slice(explode(' ', $miEscuderia['nombre']), 0, 2))) ?></div>
            <?php endif; ?>
            <div class="piloto-foto-overlay"></div>
            <div class="piloto-color-dot" style="background-color: <?= $escColor ?>;"></div>
            <div style="position:absolute;top:8px;left:8px;background:<?= $escColor ?>;color:#fff;font-size:9px;font-weight:900;padding:3px 7px;letter-spacing:1px;text-transform:uppercase;">
                ESCUDERÍA
            </div>
        </div>

        <h4 style="font-size:12px; line-height:1.3;"><?= htmlspecialchars($miEscuderia['nombre']) ?></h4>
        <div class="piloto-meta">
            <span class="piloto-escuderia">Activa</span>
            <span class="piloto-precio"><?= number_format($miEscuderia['precio_base'] / 1000000, 1) ?>M€</span>
        </div>

        <div class="piloto-pts-total <?= $puntosEscuderia > 0 ? 'pts-pos' : ($puntosEscuderia < 0 ? 'pts-neg' : '') ?>">
            <span class="piloto-pts-label">PTS TOTALES</span>
            <span class="piloto-pts-valor"><?= $puntosEscuderia > 0 ? '+' : '' ?><?= $puntosEscuderia ?></span>
        </div>

        <button type="button" class="btn-quitar-card" style="margin-top:10px; width:100%;"
                onclick="abrirModalLiberarEscuderia(
                    <?= (int)$miEscuderia['id_escuderia'] ?>,
                    '<?= htmlspecialchars(addslashes($miEscuderia['nombre'])) ?>',
                    <?= number_format($miEscuderia['precio_base'] * 0.8 / 1000000, 1, '.', '') ?>
                )">
            ✕ Liberar (<?= number_format($miEscuderia['precio_base'] * 0.8 / 1000000, 1) ?>M€)
        </button>
    </div>
    <?php else: ?>
    <div class="piloto-card-vacio" onclick="abrirModalFicharEscuderia()">
        <div class="add-icon">🏎</div>
        <span>Fichar Escudería</span>
    </div>
    <?php endif; ?>

    <?php foreach ($misPilotos as $p):
        $color    = colorEscuderia($p['escuderia'], $coloresEscuderia);
        $enSlot1  = ($slot1Id === (int)$p['id_piloto']);
        $enSlot2  = ($slot2Id === (int)$p['id_piloto']);
        $enSlot   = $enSlot1 || $enSlot2;
    ?>
    <div class="piloto-card">
        <div class="piloto-foto-wrap">
            <?php
                $eqPartes = explode(' ', trim($p['nombre']));
                $eqIni    = strtoupper(implode('', array_map(function($w) { return $w[0]; }, $eqPartes)));
                $eqIni    = substr($eqIni, 0, 3);
                $eqColor  = ltrim($color, '#');
                $eqSvgUrl = 'driver-img.php?num=' . (int)$p['numero'] . '&ini=' . urlencode($eqIni) . '&color=' . urlencode($eqColor);
            ?>
            <?php if ($p['imagen_url']): ?>
            <img src="<?= htmlspecialchars($p['imagen_url']) ?>"
                 alt="<?= htmlspecialchars($p['nombre']) ?>"
                 onerror="this.onerror=null;this.src='<?= htmlspecialchars($eqSvgUrl) ?>';">
            <?php else: ?>
            <img src="<?= htmlspecialchars($eqSvgUrl) ?>"
                 alt="<?= htmlspecialchars($p['nombre']) ?>">
            <?php endif; ?>
            <div class="piloto-foto-overlay"></div>
            <div class="piloto-color-dot" style="background-color: <?= $color ?>;"></div>
            <?php if ($enSlot): ?>
            <div style="position:absolute;top:8px;right:8px;background:var(--color-rojo);color:#fff;font-size:9px;font-weight:900;padding:3px 7px;letter-spacing:1px;text-transform:uppercase;">
                Slot <?= $enSlot1 ? '1' : '2' ?>
            </div>
            <?php endif; ?>
        </div>

        <h4><?= htmlspecialchars(formatNombre($p['nombre'])) ?></h4>
        <div class="piloto-meta">
            <span class="piloto-escuderia"><?= htmlspecialchars($p['escuderia']) ?></span>
            <span class="piloto-precio"><?= number_format($p['precio'] / 1000000, 1) ?>M€</span>
        </div>
        <?php
            $ptsPil = $puntosTotalesPilotos[(int)$p['id_piloto']] ?? 0;
        ?>
        <div class="piloto-pts-total <?= $ptsPil > 0 ? 'pts-pos' : ($ptsPil < 0 ? 'pts-neg' : '') ?>">
            <span class="piloto-pts-label">PTS TOTALES</span>
            <span class="piloto-pts-valor"><?= $ptsPil > 0 ? '+' : '' ?><?= $ptsPil ?></span>
        </div>

        <?php if (!$enSlot): ?>
        <div style="display:flex; gap:6px; margin-top:10px;">
            <?php if (!$slot1Id): ?>
            <form method="post" style="flex:1;">
                <input type="hidden" name="accion"    value="añadir">
                <input type="hidden" name="id_piloto" value="<?= (int)$p['id_piloto'] ?>">
                <input type="hidden" name="slot"      value="1">
                <button type="submit" class="btn-quitar-card">▲ Slot 1</button>
            </form>
            <?php endif; ?>
            <?php if (!$slot2Id): ?>
            <form method="post" style="flex:1;">
                <input type="hidden" name="accion"    value="añadir">
                <input type="hidden" name="id_piloto" value="<?= (int)$p['id_piloto'] ?>">
                <input type="hidden" name="slot"      value="2">
                <button type="submit" class="btn-quitar-card">▲ Slot 2</button>
            </form>
            <?php endif; ?>
            <?php if ($slot1Id && $slot2Id): ?>
            <span style="font-size:10px;color:#555;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:8px 0;">Slots llenos</span>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <form method="post" style="margin-top:10px;">
            <input type="hidden" name="accion"    value="quitar">
            <input type="hidden" name="slot"      value="<?= $enSlot1 ? '1' : '2' ?>">
            <input type="hidden" name="id_piloto" value="<?= (int)$p['id_piloto'] ?>">
            <button type="submit" class="btn-quitar-card" style="border-color:var(--color-rojo); color:var(--color-rojo);">
                ✕ Quitar de Slot <?= $enSlot1 ? '1' : '2' ?>
            </button>
        </form>
        <?php endif; ?>

        <button type="button" class="btn-quitar-card" style="margin-top:6px; width:100%;"
                onclick="abrirModalLiberar(
                    <?= (int)$p['id_piloto'] ?>,
                    '<?= htmlspecialchars(addslashes($p['nombre'])) ?>',
                    '<?= htmlspecialchars(addslashes($p['escuderia'])) ?>',
                    <?= number_format($p['precio'] * 0.8 / 1000000, 1, '.', '') ?>
                )">
            ✕ Liberar (<?= number_format($p['precio'] * 0.8 / 1000000, 1) ?>M€)
        </button>
    </div>
    <?php endforeach; ?>

    <?php for ($i = 0; $i < max(0, min(4, 5 - count($misPilotos))); $i++): ?>
    <div class="piloto-card-vacio" onclick="abrirModalFichar()">
        <div class="add-icon">＋</div>
        <span>Fichar Piloto</span>
    </div>
    <?php endfor; ?>

</div>

<div class="modal-overlay" id="modalSlot">
    <div class="modal">
        <div class="modal-header">
            <div>
                <div class="modal-slot-label" id="modalSlotLabel">Slot 1 // Capitán ×2</div>
                <h3>Seleccionar Piloto</h3>
            </div>
            <button class="modal-cerrar" onclick="cerrarModal('modalSlot')">✕</button>
        </div>
        <div class="modal-body">
            <?php if (empty($misPilotos)): ?>
                <p style="color:#555; font-size:13px; font-weight:600;">
                    No tienes pilotos en tu plantilla. Usa el botón <strong>"Fichar Piloto"</strong> primero.
                </p>
            <?php else: ?>
                <h4>Elige un piloto de tu plantilla</h4>
                <div class="modal-lista">
                    <?php foreach ($misPilotos as $p):
                        $color    = colorEscuderia($p['escuderia'], $coloresEscuderia);
                        $enSlotYa = in_array((int)$p['id_piloto'], array_values($alineacion), true);
                    ?>
                    <div class="modal-piloto-fila" style="<?= $enSlotYa ? 'opacity:0.35; pointer-events:none;' : '' ?>">
                        <div class="fila-color" style="background-color:<?= $color ?>;"></div>
                        <div class="fila-nombre">
                            <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                            <span><?= htmlspecialchars($p['escuderia']) ?></span>
                        </div>
                        <div class="fila-numero"><?= (int)$p['numero'] ?></div>
                        <?php if ($enSlotYa): ?>
                            <span style="font-size:10px;font-weight:900;text-transform:uppercase;color:#555;letter-spacing:1px;padding:8px 14px;">En slot</span>
                        <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="accion"    value="añadir">
                            <input type="hidden" name="id_piloto" value="<?= (int)$p['id_piloto'] ?>">
                            <input type="hidden" name="slot"      class="input-slot-modal" value="1">
                            <button type="submit" class="fila-btn-add">▲ Asignar</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalFichar">
    <div class="modal">
        <div class="modal-header">
            <div>
                <div class="modal-slot-label">Mercado de Fichajes</div>
                <h3>Fichar Piloto</h3>
            </div>
            <button class="modal-cerrar" onclick="cerrarModal('modalFichar')">✕</button>
        </div>
        <div class="modal-body">
            <h4>Presupuesto: <?= number_format($presupuesto / 1000000, 1) ?>M€ disponibles</h4>
            <div class="modal-lista">
                <?php foreach ($todosLos as $p):
                    $color     = colorEscuderia($p['escuderia'], $coloresEscuderia);
                    $yaFichado = in_array((int)$p['id_piloto'], array_map('intval', $idsFichados), true);
                    $sinDinero = $presupuesto < (int)$p['precio'];
                ?>
                <div class="modal-piloto-fila" style="<?= $yaFichado ? 'opacity:0.35; pointer-events:none;' : '' ?>">
                    <div class="fila-color" style="background-color:<?= $color ?>;"></div>
                    <div class="fila-nombre">
                        <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                        <span><?= htmlspecialchars($p['escuderia']) ?></span>
                    </div>
                    <div class="fila-numero" style="font-size:12px; color:#ccc; width:60px; font-style:normal;">
                        <?= number_format($p['precio'] / 1000000, 1) ?>M€
                    </div>
                    <?php if ($yaFichado): ?>
                        <span style="font-size:10px;font-weight:900;text-transform:uppercase;color:#555;letter-spacing:1px;padding:8px 14px;">Ya fichado</span>
                    <?php elseif ($sinDinero): ?>
                        <span style="font-size:10px;font-weight:900;text-transform:uppercase;color:var(--color-rojo);letter-spacing:1px;padding:8px 14px;">Sin fondos</span>
                    <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="accion"    value="fichar">
                            <input type="hidden" name="id_piloto" value="<?= (int)$p['id_piloto'] ?>">
                            <button type="submit" class="fila-btn-add">Fichar</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalLiberar">
    <div class="modal" style="max-width: 420px;">
        <div class="modal-header">
            <div>
                <div class="modal-slot-label">Liberar Piloto</div>
                <h3>¿Confirmar liberación?</h3>
            </div>
            <button class="modal-cerrar" onclick="cerrarModal('modalLiberar')">✕</button>
        </div>
        <div class="modal-body" style="padding: 28px;">

            <div style="display:flex; align-items:center; gap:14px; padding:16px; background:#0f0f0f; border:1px solid #1a1a1a; margin-bottom:24px;">
                <div style="width:5px; height:48px; background:var(--color-rojo); border-radius:2px; flex-shrink:0;" id="liberarColor"></div>
                <div>
                    <div style="font-size:18px; font-weight:900; font-style:italic; text-transform:uppercase; line-height:1;" id="liberarNombre">—</div>
                    <div style="font-size:11px; color:#888; font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-top:4px;" id="liberarEscuderia">—</div>
                </div>
            </div>

            <p style="font-size:13px; color:#aaa; line-height:1.6; margin-bottom:10px;">
                Este piloto será <strong style="color:#fff;">eliminado de tu plantilla</strong>.
                Recibirás el <strong style="color:var(--color-verde);">80% de su valor</strong> como compensación:
            </p>
            <div style="font-size:28px; font-weight:900; font-style:italic; color:var(--color-verde); margin-bottom:24px;">
                +<span id="liberarDevolucion">0</span>M€
            </div>

            <div style="display:flex; gap:10px;">
                <button type="button"
                        style="flex:1; padding:12px; background:none; border:1px solid #333; color:#888; font-family:inherit; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:1px; cursor:pointer; transition:all 0.2s;"
                        onmouseover="this.style.borderColor='#555';this.style.color='#fff';"
                        onmouseout="this.style.borderColor='#333';this.style.color='#888';"
                        onclick="cerrarModal('modalLiberar')">
                    Cancelar
                </button>
                <form method="post" style="flex:1;" id="formLiberar">
                    <input type="hidden" name="accion"    value="liberar">
                    <input type="hidden" name="id_piloto" id="liberarIdPiloto" value="">
                    <button type="submit"
                            style="width:100%; padding:12px; background:var(--color-rojo); border:none; color:#fff; font-family:inherit; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:1px; cursor:pointer; transition:opacity 0.2s;"
                            onmouseover="this.style.opacity='0.8';"
                            onmouseout="this.style.opacity='1';">
                        ✕ Confirmar Liberación
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal-overlay" id="modalFicharEscuderia">
    <div class="modal">
        <div class="modal-header">
            <div>
                <div class="modal-slot-label">Mercado de Escuderías</div>
                <h3>Fichar Escudería</h3>
            </div>
            <button class="modal-cerrar" onclick="cerrarModal('modalFicharEscuderia')">✕</button>
        </div>
        <div class="modal-body">
            <h4>Presupuesto: <?= number_format($presupuesto / 1000000, 1) ?>M€ disponibles</h4>
            <p style="font-size:12px; color:#888; margin-bottom:12px;">Solo puedes tener 1 escudería en tu equipo. Los puntos se calculan automáticamente tras cada carrera.</p>
            <div class="modal-lista">
                <?php foreach ($todasEscuderias as $esc):
                    $escColor = $coloresEscuderia[$esc['nombre']] ?? '#888888';
                    $yaTengo  = $miEscuderia && (int)$miEscuderia['id_escuderia'] === (int)$esc['id_escuderia'];
                    $sinDinero = $presupuesto < (int)$esc['precio_base'];
                ?>
                <div class="modal-piloto-fila" style="<?= ($yaTengo || ($miEscuderia && !$yaTengo)) ? 'opacity:0.35; pointer-events:none;' : '' ?>">
                    <div class="fila-color" style="background-color:<?= $escColor ?>;"></div>
                    <div class="fila-nombre">
                        <strong><?= htmlspecialchars($esc['nombre']) ?></strong>
                        <span><?= number_format($esc['precio_base'] / 1000000, 1) ?>M€</span>
                    </div>
                    <div class="fila-numero" style="font-size:12px; color:#ccc; width:60px; font-style:normal;">
                        <?= number_format($esc['precio_base'] / 1000000, 1) ?>M€
                    </div>
                    <?php if ($yaTengo): ?>
                        <span style="font-size:10px;font-weight:900;text-transform:uppercase;color:#555;letter-spacing:1px;padding:8px 14px;">Ya en equipo</span>
                    <?php elseif ($miEscuderia): ?>
                        <span style="font-size:10px;font-weight:900;text-transform:uppercase;color:#555;letter-spacing:1px;padding:8px 14px;">Libera la actual</span>
                    <?php elseif ($sinDinero): ?>
                        <span style="font-size:10px;font-weight:900;text-transform:uppercase;color:var(--color-rojo);letter-spacing:1px;padding:8px 14px;">Sin fondos</span>
                    <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="accion" value="fichar_escuderia">
                            <input type="hidden" name="id_escuderia" value="<?= (int)$esc['id_escuderia'] ?>">
                            <button type="submit" class="fila-btn-add">Fichar</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalLiberarEscuderia">
    <div class="modal" style="max-width: 420px;">
        <div class="modal-header">
            <div>
                <div class="modal-slot-label">Liberar Escudería</div>
                <h3>¿Confirmar liberación?</h3>
            </div>
            <button class="modal-cerrar" onclick="cerrarModal('modalLiberarEscuderia')">✕</button>
        </div>
        <div class="modal-body" style="padding: 28px;">
            <div style="display:flex; align-items:center; gap:14px; padding:16px; background:#0f0f0f; border:1px solid #1a1a1a; margin-bottom:24px;">
                <div style="width:5px; height:48px; background:var(--color-rojo); border-radius:2px; flex-shrink:0;"></div>
                <div>
                    <div style="font-size:18px; font-weight:900; font-style:italic; text-transform:uppercase; line-height:1;" id="liberarEscNombre">—</div>
                    <div style="font-size:11px; color:#888; font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">Escudería</div>
                </div>
            </div>
            <p style="font-size:13px; color:#aaa; line-height:1.6; margin-bottom:10px;">
                Esta escudería será <strong style="color:#fff;">eliminada de tu equipo</strong>.
                Recibirás el <strong style="color:var(--color-verde);">80% de su valor</strong>:
            </p>
            <div style="font-size:28px; font-weight:900; font-style:italic; color:var(--color-verde); margin-bottom:24px;">
                +<span id="liberarEscDevolucion">0</span>M€
            </div>
            <div style="display:flex; gap:10px;">
                <button type="button"
                        style="flex:1; padding:12px; background:none; border:1px solid #333; color:#888; font-family:inherit; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:1px; cursor:pointer;"
                        onclick="cerrarModal('modalLiberarEscuderia')">
                    Cancelar
                </button>
                <form method="post" style="flex:1;">
                    <input type="hidden" name="accion" value="liberar_escuderia">
                    <input type="hidden" name="id_escuderia" id="liberarEscId" value="">
                    <button type="submit"
                            style="width:100%; padding:12px; background:var(--color-rojo); border:none; color:#fff; font-family:inherit; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:1px; cursor:pointer;">
                        ✕ Confirmar Liberación
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../js/equipo.js"></script>

<?php include __DIR__ . '/../private/footer.php'; ?>
