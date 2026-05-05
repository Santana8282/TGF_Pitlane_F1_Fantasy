<?php
require_once __DIR__ . '/../private/funciones_auth.php';
require_once __DIR__ . '/../private/iconos.php';
require_once __DIR__ . '/../private/puntuacion.php';
requiereAutenticacion();

$pdo     = getDB();
$mensaje = null;
$tipo    = 'ok';
$isAdmin = esAdmin();

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // Limpieza de puntos mal asignados
    if (($_POST['accion'] ?? '') === 'limpiar_puntos') {
        $borrados = limpiarPuntosMalAsignados();
        $flash = ['tipo' => 'ok', 'msg' => "Limpieza completada. Registros incorrectos eliminados: {$borrados}. Puntos recalculados."];
    }

    if (($_POST['accion'] ?? '') === 'sincronizar') {
        $idCarrera = (int)($_POST['id_carrera'] ?? 0);
        if ($idCarrera > 0) {
            $res     = sincronizarDesdeErgast($idCarrera);
            $mensaje = $res['mensaje'];
            $tipo    = $res['ok'] ? 'ok' : 'error';
        }
    }

    if (($_POST['accion'] ?? '') === 'guardar_resultado') {
        $idCarrera = (int)($_POST['id_carrera'] ?? 0);
        $idPiloto  = (int)($_POST['id_piloto']  ?? 0);

        if ($idCarrera > 0 && $idPiloto > 0) {
            $pdo->prepare("DELETE FROM resultados_carrera WHERE id_carrera=? AND id_piloto=?")
                ->execute([$idCarrera, $idPiloto]);

            $pdo->prepare("
                INSERT INTO resultados_carrera
                    (id_carrera, id_piloto, posicion, puntos_oficiales, vuelta_rapida, estado,
                     posicion_salida, adelantamientos, banderas_amarillas, banderas_rojas,
                     penalizaciones, mejor_sector, pole_position, fuente_datos)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'manual')
            ")->execute([
                $idCarrera, $idPiloto,
                (int)($_POST['posicion']           ?? 0),
                (int)($_POST['puntos_oficiales']   ?? 0),
                (int)($_POST['vuelta_rapida']      ?? 0),
                $_POST['estado']                   ?? 'finished',
                (int)($_POST['posicion_salida']    ?? 0),
                (int)($_POST['adelantamientos']    ?? 0),
                (int)($_POST['banderas_amarillas'] ?? 0),
                (int)($_POST['banderas_rojas']     ?? 0),
                (int)($_POST['penalizaciones']     ?? 0),
                (int)($_POST['mejor_sector']       ?? 0),
                (int)($_POST['pole_position']      ?? 0),
            ]);

            $stmt = $pdo->prepare("SELECT * FROM resultados_carrera WHERE id_carrera=? AND id_piloto=?");
            $stmt->execute([$idCarrera, $idPiloto]);
            $r    = $stmt->fetch();
            $calc = calcularPuntosResultado($r);
            $partes  = array_map(function($d) { return $d['descripcion']; }, $calc['desglose']);
            $detalle = implode(' | ', $partes);
            guardarPuntosEnEquipos(
                (int)$r['id_resultado'], $idPiloto, $idCarrera,
                $calc['puntos_total'], $calc['desglose'], $detalle
            );
            foreach ($pdo->query("SELECT id_escuderia FROM escuderias")->fetchAll(PDO::FETCH_COLUMN) as $idEsc) {
                try { guardarPuntosEscuderiaEnEquipos((int)$idEsc, $idCarrera); } catch (Exception $e) { }
            }
            recalcularResumenJornada($idCarrera);
            $mensaje = "✓ Resultado guardado y puntos recalculados. Total fantasy piloto: {$calc['puntos_total']} pts";
            $tipo    = 'ok';
        }
    }

    if (($_POST['accion'] ?? '') === 'recalcular') {
        $idCarrera = (int)($_POST['id_carrera'] ?? 0);
        if ($idCarrera > 0) {
            $res     = procesarCarrera($idCarrera);
            $mensaje = "Recalculados {$res['procesados']} resultados. Errores: {$res['errores']}";
            $tipo    = $res['errores'] > 0 ? 'error' : 'ok';
        }
    }
}

$carreras = $pdo->query("
    SELECT c.*, t.anio,
           COUNT(rc.id_resultado) AS tiene_resultados,
           MAX(sl.fecha_sync) AS ultima_sync
    FROM carreras c
    JOIN temporadas t ON t.id_temporada = c.id_temporada
    LEFT JOIN resultados_carrera rc ON rc.id_carrera = c.id_carrera
    LEFT JOIN sync_log sl ON sl.id_carrera = c.id_carrera
    GROUP BY c.id_carrera
    ORDER BY c.fecha ASC
")->fetchAll();

$pilotos = $pdo->query("
    SELECT p.*, e.nombre AS escuderia
    FROM pilotos p JOIN escuderias e ON e.id_escuderia = p.id_escuderia
    ORDER BY e.nombre, p.nombre
")->fetchAll();

if (isset($_GET['carrera'])) {
    $idCarreraVer = (int)$_GET['carrera'];
} else {
    $defaultId = 0;
    foreach (array_reverse($carreras) as $c) {
        if ((int)$c['tiene_resultados'] > 0) { $defaultId = (int)$c['id_carrera']; break; }
    }
    if (!$defaultId) {
        foreach (array_reverse($carreras) as $c) {
            if ($c['fecha'] <= date('Y-m-d')) { $defaultId = (int)$c['id_carrera']; break; }
        }
    }
    $idCarreraVer = $defaultId ?: ($carreras[0]['id_carrera'] ?? 0);
}

$carreraActual     = null;
$resultadosCarrera = [];
if ($idCarreraVer) {
    foreach ($carreras as $c) {
        if ((int)$c['id_carrera'] === $idCarreraVer) { $carreraActual = $c; break; }
    }
    $stmt = $pdo->prepare("
        SELECT rc.*, p.nombre AS piloto, p.numero, e.nombre AS escuderia
        FROM resultados_carrera rc
        JOIN pilotos p ON p.id_piloto = rc.id_piloto
        JOIN escuderias e ON e.id_escuderia = p.id_escuderia
        WHERE rc.id_carrera = ?
        ORDER BY CASE WHEN rc.posicion=0 OR rc.posicion IS NULL THEN 999 ELSE rc.posicion END
    ");
    $stmt->execute([$idCarreraVer]);
    $resultadosCarrera = $stmt->fetchAll();
}

$tituloPagina = 'Resultados';
include __DIR__ . '/../private/header.php';
?>

<div class="encabezado">
    <p class="panel"><?= $isAdmin ? 'Administración de Resultados' : 'Resultados' ?></p>
    <h2>RESULTADOS<?= $isAdmin ? ' Y PUNTUACIÓN' : '' ?></h2>
</div>

<?php if ($mensaje): ?>
    <div class="flash <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<div class="resultados-layout">

    <div class="res-sidebar">
        <h3 class="res-sidebar-titulo">Calendario 2026</h3>
        <?php foreach ($carreras as $c):
            $pasada = strtotime($c['fecha']) < time();
            $activa = (int)$c['id_carrera'] === $idCarreraVer;
        ?>
        <a href="resultados.php?carrera=<?= $c['id_carrera'] ?>"
           class="res-carrera-item <?= $activa ? 'activa' : '' ?> <?= $pasada ? 'pasada' : 'futura' ?>">
            <div class="res-carrera-num">R<?= (int)$c['numero_carrera'] ?></div>
            <div class="res-carrera-info">
                <strong><?= htmlspecialchars($c['nombre']) ?></strong>
                <span><?= date('d M', strtotime($c['fecha'])) ?></span>
            </div>
            <?php if ((int)$c['tiene_resultados'] > 0): ?>
                <span class="res-badge-ok"><?= icono('check', 'icono-inline', 12) ?></span>
            <?php elseif ($pasada): ?>
                <span class="res-badge-pend">—</span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="res-main">
        <?php if ($carreraActual): ?>

        <div class="res-carrera-header">
            <div>
                <p class="panel">R<?= (int)$carreraActual['numero_carrera'] ?> · <?= $carreraActual['anio'] ?></p>
                <h3><?= htmlspecialchars($carreraActual['nombre']) ?></h3>
                <p class="res-circuito"><?= htmlspecialchars($carreraActual['circuito']) ?> · <?= date('d/m/Y', strtotime($carreraActual['fecha'])) ?></p>
            </div>
            <?php if ($isAdmin): ?>
        <form method="POST" style="display:inline-block;margin-bottom:12px" onsubmit="return confirm('Esto eliminara puntos de equipos que ficharon pilotos despues de la carrera. Continuar?')">
            <input type="hidden" name="accion" value="limpiar_puntos">
            <button type="submit" class="btn-sync" style="background:rgba(225,6,0,.12);border:1px solid rgba(225,6,0,.35);color:#e10600">
                Limpiar puntos incorrectos
            </button>
        </form>
            <div class="res-carrera-header-fila">
                <?php if (strtotime($carreraActual['fecha']) < time()): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="accion"     value="sincronizar">
                    <input type="hidden" name="id_carrera" value="<?= $idCarreraVer ?>">
                    <button type="submit" class="btn-sync"
                            onclick="return confirm('¿Sincronizar resultados reales desde la API de F1?')">
                        ⟳ Sincronizar API
                    </button>
                </form>
                <?php if (!empty($resultadosCarrera)): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="accion"     value="recalcular">
                    <input type="hidden" name="id_carrera" value="<?= $idCarreraVer ?>">
                    <button type="submit" class="btn-recalc">↻ Recalcular Puntos</button>
                </form>
                <?php endif; ?>
                <?php else: ?>
                    <span class="res-futura-badge">Carrera pendiente</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($isAdmin && $carreraActual['ultima_sync']): ?>
            <p class="res-sync-info">Última sincronización: <?= $carreraActual['ultima_sync'] ?></p>
        <?php endif; ?>

        <?php if (!empty($resultadosCarrera)): ?>
        <div class="res-tabla-wrap">
            <table class="res-tabla">
                <thead>
                    <tr>
                        <th>POS</th>
                        <th>Piloto</th>
                        <th>Sal.</th>
                        <th>Estado</th>
                        <th>+Pos</th>
                        <th>VR</th>
                        <th>Pole</th>
                        <th>🟡</th>
                        <th>🔴</th>
                        <th>Pen.</th>
                        <th class="th-pts">Pts Fantasy</th>
                        <?php if ($isAdmin): ?><th></th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resultadosCarrera as $rc):
                    $pts = (int)($rc['puntos_fantasy'] ?? 0);
                    $ptsClass = $pts > 0 ? 'pts-pos' : ($pts < 0 ? 'pts-neg' : '');
                ?>
                    <tr>
                        <td><strong><?= $rc['posicion'] ?: '—' ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($rc['piloto']) ?></strong><br>
                            <span><?= htmlspecialchars($rc['escuderia']) ?></span>
                        </td>
                        <td><?= $rc['posicion_salida'] ?: '—' ?></td>
                        <td>
                            <span class="estado-badge estado-<?= strtolower($rc['estado'] ?? 'finished') ?>">
                                <?= strtoupper($rc['estado'] ?? 'FIN') ?>
                            </span>
                        </td>
                        <td><?= (int)$rc['adelantamientos'] ?: '—' ?></td>
                        <td><?= $rc['vuelta_rapida'] ? '✓' : '—' ?></td>
                        <td><?= $rc['pole_position'] ? '✓' : '—' ?></td>
                        <td><?= (int)$rc['banderas_amarillas'] ?: '—' ?></td>
                        <td><?= (int)$rc['banderas_rojas'] ?: '—' ?></td>
                        <td><?= (int)$rc['penalizaciones'] ?: '—' ?></td>
                        <td class="td-pts <?= $ptsClass ?>">
                            <?= $pts > 0 ? '+' : '' ?><?= $pts ?>
                        </td>
                        <?php if ($isAdmin): ?>
                        <td>
                            <button class="btn-editar-fila"
                                    onclick="abrirEditar(<?= htmlspecialchars(json_encode($rc)) ?>)">
                                ✎
                            </button>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p style="color:#555;padding:40px 0;">Aún no hay resultados para esta carrera.</p>
        <?php endif; ?>

        <?php if ($isAdmin && strtotime($carreraActual['fecha']) <= time() + 86400): ?>
        <div class="res-form-wrap">
            <h4 class="res-form-titulo">
                <?= empty($resultadosCarrera) ? 'Añadir Resultados' : '+ Añadir / Editar Piloto' ?>
            </h4>
            <form method="POST" action="resultados.php?carrera=<?= $idCarreraVer ?>" class="res-form" id="formResultado">
                <input type="hidden" name="accion"     value="guardar_resultado">
                <input type="hidden" name="id_carrera" value="<?= $idCarreraVer ?>">
                <div class="res-form-grid">
                    <div class="res-campo">
                        <label>Piloto</label>
                        <select name="id_piloto" required id="sel_piloto">
                            <option value="">— Selecciona —</option>
                            <?php foreach ($pilotos as $p): ?>
                            <option value="<?= $p['id_piloto'] ?>">
                                <?= htmlspecialchars($p['nombre']) ?> (<?= htmlspecialchars($p['escuderia']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="res-campo">
                        <label>Pos. Final</label>
                        <input type="number" name="posicion" min="0" max="25" placeholder="1-20" id="inp_pos">
                    </div>
                    <div class="res-campo">
                        <label>Pos. Salida</label>
                        <input type="number" name="posicion_salida" min="0" max="25" placeholder="1-20" id="inp_sal">
                    </div>
                    <div class="res-campo">
                        <label>Estado</label>
                        <select name="estado" id="inp_estado">
                            <option value="finished">Terminó</option>
                            <option value="dnf">DNF (Abandono)</option>
                            <option value="dsq">DSQ (Descalificado)</option>
                            <option value="dns">DNS (No salió)</option>
                        </select>
                    </div>
                    <div class="res-campo">
                        <label>Pts Oficiales F1</label>
                        <input type="number" name="puntos_oficiales" min="0" max="26" placeholder="0-26" id="inp_pto">
                    </div>
                    <div class="res-campo res-campo-check">
                        <label><input type="checkbox" name="vuelta_rapida" value="1" id="inp_vr"> Vuelta Rápida (+10)</label>
                    </div>
                    <div class="res-campo res-campo-check">
                        <label><input type="checkbox" name="pole_position" value="1" id="inp_pole"> Pole Position (+10)</label>
                    </div>
                    <div class="res-campo res-campo-check">
                        <label><input type="checkbox" name="mejor_sector" value="1" id="inp_sector"> Mejor Sector (+5)</label>
                    </div>
                    <div class="res-campo">
                        <label>Banderas 🟡</label>
                        <input type="number" name="banderas_amarillas" min="0" max="5" value="0" id="inp_ba">
                        <small>-5 pts c/u</small>
                    </div>
                    <div class="res-campo">
                        <label>Banderas 🔴</label>
                        <input type="number" name="banderas_rojas" min="0" max="2" value="0" id="inp_br">
                        <small>-15 pts c/u</small>
                    </div>
                    <div class="res-campo">
                        <label>Penalizaciones</label>
                        <input type="number" name="penalizaciones" min="0" max="10" value="0" id="inp_pen">
                        <small>-5 pts c/u</small>
                    </div>
                </div>
                <div class="res-preview">
                    <span class="res-preview-label">Puntos estimados:</span>
                    <span class="res-preview-valor" id="previewPts">—</span>
                    <span class="res-preview-detalle" id="previewDetalle"></span>
                </div>
                <button type="submit" class="boton-rojo" style="margin-top:16px;">↳ Guardar Resultado</button>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
        <div class="res-tabla-puntos">
            <h4>Tabla de Puntuación Fantasy</h4>
            <div class="puntos-grid">
                <div class="puntos-grupo">
                    <p class="puntos-grupo-titulo">Por Posición</p>
                    <div class="puntos-fila"><span>1º</span><strong class="pos">+25</strong></div>
                    <div class="puntos-fila"><span>2º</span><strong class="pos">+18</strong></div>
                    <div class="puntos-fila"><span>3º</span><strong class="pos">+15</strong></div>
                    <div class="puntos-fila"><span>4º</span><strong class="pos">+12</strong></div>
                    <div class="puntos-fila"><span>5º</span><strong class="pos">+10</strong></div>
                    <div class="puntos-fila"><span>6º</span><strong class="pos">+8</strong></div>
                    <div class="puntos-fila"><span>7º</span><strong class="pos">+6</strong></div>
                    <div class="puntos-fila"><span>8º</span><strong class="pos">+4</strong></div>
                    <div class="puntos-fila"><span>9-10º</span><strong class="pos">+2/+1</strong></div>
                    <div class="puntos-fila"><span>11-14º</span><strong class="pos">+2</strong></div>
                </div>
                <div class="puntos-grupo">
                    <p class="puntos-grupo-titulo">Bonus</p>
                    <div class="puntos-fila"><span>Terminar</span><strong class="pos">+2</strong></div>
                    <div class="puntos-fila"><span>Vuelta rápida</span><strong class="pos">+10</strong></div>
                    <div class="puntos-fila"><span>Pole</span><strong class="pos">+10</strong></div>
                    <div class="puntos-fila"><span>Mejor sector</span><strong class="pos">+5</strong></div>
                    <div class="puntos-fila"><span>Por pos. ganada</span><strong class="pos">+3</strong></div>
                </div>
                <div class="puntos-grupo">
                    <p class="puntos-grupo-titulo">Penalizaciones</p>
                    <div class="puntos-fila"><span>Abandono</span><strong class="neg">-10</strong></div>
                    <div class="puntos-fila"><span>Descalificado</span><strong class="neg">-20</strong></div>
                    <div class="puntos-fila"><span>Bandera 🟡</span><strong class="neg">-5</strong></div>
                    <div class="puntos-fila"><span>Bandera 🔴</span><strong class="neg">-15</strong></div>
                    <div class="puntos-fila"><span>Penalización</span><strong class="neg">-5</strong></div>
                    <div class="puntos-fila"><span>Por pos. perdida</span><strong class="neg">-2</strong></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
            <p style="color:#555;padding:40px;">Selecciona una carrera del calendario.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal-overlay" id="modalEditar">
    <div class="modal" style="max-width:520px;">
        <div class="modal-header">
            <div>
                <div class="modal-slot-label">Editar Resultado</div>
                <h3 id="modalEditarNombre">—</h3>
            </div>
            <button class="modal-cerrar" onclick="cerrarModal('modalEditar')">✕</button>
        </div>
        <div class="modal-body" style="padding:24px 28px;">
            <p style="color:#555;font-size:13px;margin-bottom:16px;">
                Modifica los datos y pulsa Guardar para recalcular los puntos fantasy.
            </p>
            <button class="boton-rojo" style="width:100%;padding:12px;" onclick="copiarAlFormulario()">
                Editar en el formulario principal
            </button>
        </div>
    </div>
</div>

<script src="../js/resultados.js"></script>

<div id="autosync-banner" style="display:none;position:fixed;bottom:20px;right:20px;background:#111;border:1px solid #333;border-left:3px solid var(--color-rojo);padding:12px 18px;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#ccc;z-index:999;max-width:320px;"></div>

<?php endif; ?>

<?php include __DIR__ . '/../private/footer.php'; ?>