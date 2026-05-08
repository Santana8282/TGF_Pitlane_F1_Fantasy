<?php
require_once __DIR__ . '/../private/funciones_auth.php';
require_once __DIR__ . '/../private/iconos.php';
requiereAutenticacion();

$pdo       = getDB();
$idUsuario = (int) $_SESSION['id_usuario'];

$stmtEq = $pdo->prepare("
    SELECT ef.presupuesto, COALESCE(SUM(pf.puntos), 0) AS puntos_totales
    FROM equipos_fantasy ef
    LEFT JOIN puntos_fantasy pf ON pf.id_equipo = ef.id_equipo
    WHERE ef.id_usuario = ?
    GROUP BY ef.id_equipo, ef.presupuesto LIMIT 1
");
$stmtEq->execute([$idUsuario]);
$stats = $stmtEq->fetch();

$puntos      = $stats ? number_format($stats['puntos_totales'], 1, ',', '.') : '0';
$presupuesto = $stats ? number_format($stats['presupuesto'] / 1000000, 1, ',', '.') : '100,0';

$stmtPos = $pdo->query("
    SELECT ef.id_equipo, COALESCE(SUM(pf.puntos), 0) AS total
    FROM equipos_fantasy ef
    LEFT JOIN puntos_fantasy pf ON pf.id_equipo = ef.id_equipo
    GROUP BY ef.id_equipo ORDER BY total DESC
");
$ranking    = $stmtPos->fetchAll();
$posicion   = '—';
$stmtMiEq   = $pdo->prepare("SELECT id_equipo FROM equipos_fantasy WHERE id_usuario = ? LIMIT 1");
$stmtMiEq->execute([$idUsuario]);
$miIdEquipo = $stmtMiEq->fetchColumn();
foreach ($ranking as $i => $r) {
    if ((int)$r['id_equipo'] === (int)$miIdEquipo) { $posicion = '#' . ($i + 1); break; }
}

$stmtGP = $pdo->query("SELECT nombre, circuito, fecha FROM carreras WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1");
$proximoGP = $stmtGP->fetch();

if ($proximoGP) {
    $nombreGP   = htmlspecialchars($proximoGP['nombre']);
    $circuitoGP = htmlspecialchars($proximoGP['circuito']);
    $tsGP       = (new DateTime($proximoGP['fecha'] . ' 14:00:00', new DateTimeZone('Europe/Madrid')))->getTimestamp();
} else {
    $calendario = [
        ['nombre'=>'Gran Premio de España',       'circuito'=>'Montmeló',          'fecha'=>'2026-06-07'],
        ['nombre'=>'Gran Premio de Canadá',       'circuito'=>'Gilles Villeneuve', 'fecha'=>'2026-06-21'],
        ['nombre'=>'Gran Premio de Austria',      'circuito'=>'Red Bull Ring',     'fecha'=>'2026-06-28'],
        ['nombre'=>'Gran Premio de Gran Bretaña', 'circuito'=>'Silverstone',       'fecha'=>'2026-07-05'],
        ['nombre'=>'Gran Premio de Bélgica',      'circuito'=>'Spa-Francorchamps', 'fecha'=>'2026-07-26'],
        ['nombre'=>'Gran Premio de Italia',       'circuito'=>'Monza',             'fecha'=>'2026-09-06'],
        ['nombre'=>'Gran Premio de Brasil',       'circuito'=>'Interlagos',        'fecha'=>'2026-11-08'],
        ['nombre'=>'Gran Premio de Abu Dabi',     'circuito'=>'Yas Marina',        'fecha'=>'2026-12-06'],
    ];
    $hoy = date('Y-m-d');
    $nombreGP = 'Gran Premio de Abu Dabi'; $circuitoGP = 'Yas Marina'; $tsGP = (new DateTime('2026-12-06 14:00:00', new DateTimeZone('Europe/Madrid')))->getTimestamp();
    foreach ($calendario as $gp) {
        if ($gp['fecha'] >= $hoy) { $nombreGP=$gp['nombre']; $circuitoGP=$gp['circuito']; $tsGP=(new DateTime($gp['fecha'].' 14:00:00', new DateTimeZone('Europe/Madrid')))->getTimestamp(); break; }
    }
}

$stmtMov = $pdo->query("
    SELECT p.nombre AS piloto, e.nombre AS escuderia, pf.puntos, pf.detalle, c.nombre AS carrera
    FROM puntos_fantasy pf
    JOIN pilotos p ON p.id_piloto = pf.id_piloto
    JOIN escuderias e ON e.id_escuderia = p.id_escuderia
    JOIN carreras c ON c.id_carrera = pf.id_carrera
    ORDER BY pf.id_punto DESC LIMIT 5
");
$movimientos = $stmtMov->fetchAll();

$tituloPagina = 'Inicio';
include __DIR__ . '/../private/header.php';
?>

<div class="encabezado">
    <p class="panel">Panel de Control</p>
    <h2>INICIO DE LA LIGA FANTASY F1</h2>

</div>

<div class="tarjetas">
    <div class="tarjeta">
        <p>Puntos Totales</p>
        <h3><?= $puntos ?></h3>
        <span class="positivo">Temporada actual</span>
    </div>
    <div class="tarjeta">
        <p>Posición Global</p>
        <h3><?= $posicion ?></h3>
        <span class="positivo">En la liga</span>
    </div>
    <div class="tarjeta">
        <p>Presupuesto Restante</p>
        <h3><?= $presupuesto ?> <small>M€</small></h3>
        <span class="negativo">Disponible</span>
    </div>
</div>

<div class="contenido">
    <div class="movimientos">
        <h3>Últimos Movimientos</h3>
        <table>
            <thead>
                <tr>
                    <th>Piloto / Escudería</th>
                    <th>Carrera</th>
                    <th>Detalle</th>
                    <th>Puntos</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($movimientos)): ?>
                <?php foreach ($movimientos as $mov): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($mov['piloto']) ?></strong><br>
                        <span><?= htmlspecialchars($mov['escuderia']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($mov['carrera']) ?></td>
                    <td><?= htmlspecialchars($mov['detalle'] ?? '—') ?></td>
                    <td class="<?= $mov['puntos'] >= 0 ? 'verde' : 'texto-rojo' ?>">
                        <?= $mov['puntos'] >= 0 ? '+' : '' ?><?= $mov['puntos'] ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;color:#555;padding:30px 12px;">
                        Todavía no hay puntos registrados.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="gran-premio">
        <div class="gp-contenido">
            <p>Próximo Gran Premio</p>
            <h3><?= $nombreGP ?></h3>
            <p class="gp-circuito"><?= $circuitoGP ?></p>

            <?php if ($tsGP && $tsGP > time()): ?>
            <div class="contador" id="countdown-index" data-ts="<?= $tsGP ?>">
                <div><span id="cd-dias">00</span><small>Días</small></div>
                <div><span id="cd-horas">00</span><small>Hrs</small></div>
                <div><span id="cd-min">00</span><small>Min</small></div>
                <div><span id="cd-seg">00</span><small>Seg</small></div>
            </div>
            <script src="../js/index.js"></script>
            <?php else: ?>
            <div class="contador">
                <div><span>—</span><small>Días</small></div>
                <div><span>—</span><small>Hrs</small></div>
                <div><span>—</span><small>Min</small></div>
                <div><span>—</span><small>Seg</small></div>
            </div>
            <?php endif; ?>

            <div class="clima"><?= icono('sol', 'icono-inline', 14) ?> Soleado | 28°C</div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../private/footer.php'; ?>