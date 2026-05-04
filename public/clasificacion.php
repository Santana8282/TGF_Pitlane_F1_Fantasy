<?php
require_once __DIR__ . '/../private/funciones_auth.php';
require_once __DIR__ . '/../private/iconos.php';
requiereAutenticacion();

$pdo       = getDB();
$idUsuario = (int) $_SESSION['id_usuario'];

$stmtClasif = $pdo->query("
    SELECT
        ef.id_equipo,
        ef.nombre_equipo,
        u.nombre        AS nombre_usuario,
        ef.presupuesto,
        COALESCE(SUM(pf.puntos), 0) AS puntos_totales,
        COUNT(DISTINCT pf.id_carrera)  AS carreras_puntuadas
    FROM equipos_fantasy ef
    JOIN usuarios u ON u.id_usuario = ef.id_usuario
    LEFT JOIN puntos_fantasy pf ON pf.id_equipo = ef.id_equipo
    GROUP BY ef.id_equipo, ef.nombre_equipo, u.nombre, u.id_usuario, ef.presupuesto
    ORDER BY puntos_totales DESC
");
$clasificacion = $stmtClasif->fetchAll();

$stmtMiEq = $pdo->prepare("SELECT id_equipo FROM equipos_fantasy WHERE id_usuario = ? LIMIT 1");
$stmtMiEq->execute([$idUsuario]);
$miEquipo = $stmtMiEq->fetchColumn();

$tituloPagina = 'Clasificación';
include __DIR__ . '/../private/header.php';
?>

<div class="encabezado">
    <p class="panel">Liga Fantasy F1</p>
    <h2>CLASIFICACIÓN GENERAL</h2>
</div>

<div class="clasif-resumen">
    <?php
    $miPosicion  = 0;
    $misPuntos   = 0;
    $totalEquipos = count($clasificacion);
    foreach ($clasificacion as $i => $fila) {
        if ((int)$fila['id_equipo'] === (int)$miEquipo) {
            $miPosicion = $i + 1;
            $misPuntos  = $fila['puntos_totales'];
        }
    }
    $lider = $clasificacion[0] ?? null;
    $diferencia = $lider ? $lider['puntos_totales'] - $misPuntos : 0;
    ?>
    <div class="resumen-card resumen-pos">
        <p class="resumen-label">Tu Posición</p>
        <div class="resumen-valor">#<?= $miPosicion ?: '—' ?></div>
        <span class="resumen-sub">de <?= $totalEquipos ?> equipos</span>
    </div>
    <div class="resumen-card resumen-pts">
        <p class="resumen-label">Tus Puntos</p>
        <div class="resumen-valor"><?= number_format($misPuntos, 1, ',', '.') ?></div>
        <span class="resumen-sub">puntos acumulados</span>
    </div>
    <div class="resumen-card resumen-dif">
        <p class="resumen-label">Diferencia con el 1º</p>
        <div class="resumen-valor"><?= $diferencia > 0 ? '-' . number_format($diferencia, 1, ',', '.') : '—' ?></div>
        <span class="resumen-sub">puntos al líder</span>
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
        $puntosLider = $clasificacion[0]['puntos_totales'] ?? 0;
        foreach ($clasificacion as $i => $fila):
            $pos       = $i + 1;
            $esMio     = (int)$fila['id_equipo'] === (int)$miEquipo;
            $dif       = $puntosLider - $fila['puntos_totales'];
            $rowClass  = $esMio ? 'fila-mia' : '';
            if ($pos === 1) $rowClass .= ' fila-lider';
        ?>
            <tr class="<?= $rowClass ?>">
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
                <td class="col-pts">
                    <span class="pts-valor"><?= number_format($fila['puntos_totales'], 1, ',', '.') ?></span>
                </td>
                <td class="col-dif">
                    <?= $pos === 1 ? '<span class="lider-tag">LÍDER</span>' : '<span class="dif-neg">-' . number_format($dif, 1, ',', '.') . '</span>' ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (empty($clasificacion)): ?>
        <div class="clasif-vacia">
            <p>Todavía no hay equipos en la clasificación.<br>¡Sé el primero en puntuar!</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../private/footer.php'; ?>
