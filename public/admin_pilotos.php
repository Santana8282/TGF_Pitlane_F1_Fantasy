<?php
require_once __DIR__ . '/../private/funciones_auth.php';
require_once __DIR__ . '/../private/iconos.php';
requiereAdmin();

$pdo   = getDB();
$flash = null;
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($accion === 'guardar_edicion') {
        $id     = (int) ($_POST['id_piloto'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $numero = (int) ($_POST['numero'] ?? 0);
        $nac    = trim($_POST['nacionalidad'] ?? '');
        $escId  = (int) ($_POST['id_escuderia'] ?? 0);
        $precio = (int) (floatval(str_replace(',', '.', $_POST['precio'] ?? 0)) * 1_000_000);
        $img    = trim($_POST['imagen_url'] ?? '');

        if ($id > 0 && $nombre && $escId > 0 && $precio > 0) {

            $stmtPrecAnt = $pdo->prepare("SELECT precio FROM pilotos WHERE id_piloto = ?");
            $stmtPrecAnt->execute([$id]);
            $precioAnterior = (int) $stmtPrecAnt->fetchColumn();
            $pdo->prepare("UPDATE pilotos SET nombre=?,numero=?,nacionalidad=?,id_escuderia=?,precio=?,precio_anterior=?,imagen_url=? WHERE id_piloto=?")
                ->execute([$nombre, $numero, $nac, $escId, $precio, $precioAnterior, $img ?: null, $id]);

            if ($precio !== $precioAnterior) {
                $pdo->prepare("INSERT INTO historial_precios (id_piloto, precio) VALUES (?, ?)")->execute([$id, $precio]);
            }
            $flash = ['tipo' => 'ok', 'msg' => '✓ Piloto "' . htmlspecialchars($nombre) . '" actualizado.'];
        } else {
            $flash = ['tipo' => 'error', 'msg' => 'Faltan datos obligatorios.'];
        }

    } elseif ($accion === 'nuevo_piloto') {
        $nombre = trim($_POST['nombre'] ?? '');
        $numero = (int) ($_POST['numero'] ?? 0);
        $nac    = trim($_POST['nacionalidad'] ?? '');
        $escId  = (int) ($_POST['id_escuderia'] ?? 0);
        $precio = (int) (floatval(str_replace(',', '.', $_POST['precio'] ?? 0)) * 1_000_000);
        $img    = trim($_POST['imagen_url'] ?? '');

        if ($nombre && $escId > 0 && $precio > 0) {
            $pdo->prepare("INSERT INTO pilotos (nombre,numero,nacionalidad,id_escuderia,precio,precio_inicial,precio_anterior,imagen_url) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$nombre, $numero, $nac, $escId, $precio, $precio, $precio, $img ?: null]);
            $flash = ['tipo' => 'ok', 'msg' => '✓ Piloto "' . htmlspecialchars($nombre) . '" añadido al mercado.'];
        } else {
            $flash = ['tipo' => 'error', 'msg' => 'Faltan datos obligatorios.'];
        }

    } elseif ($accion === 'eliminar') {
        $id = (int) ($_POST['id_piloto'] ?? 0);
        if ($id > 0) {
            $st = $pdo->prepare("SELECT nombre FROM pilotos WHERE id_piloto = ?");
            $st->execute([$id]);
            $nombreBorrado = $st->fetchColumn() ?: 'Piloto';
            $pdo->prepare("DELETE FROM pilotos_equipo_fantasy WHERE id_piloto = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM resultados_carrera WHERE id_piloto = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM puntos_fantasy WHERE id_piloto = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM historial_precios WHERE id_piloto = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM historial_plantilla WHERE id_piloto = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM pilotos WHERE id_piloto = ?")->execute([$id]);
            $flash = ['tipo' => 'ok', 'msg' => '✓ "' . htmlspecialchars($nombreBorrado) . '" eliminado del mercado.'];
        }
    }
}

$pilotos = $pdo->query("
    SELECT p.*, e.nombre AS escuderia
    FROM pilotos p
    JOIN escuderias e ON e.id_escuderia = p.id_escuderia
    ORDER BY e.nombre ASC, p.precio DESC
")->fetchAll();

$escuderias = $pdo->query("SELECT id_escuderia, nombre FROM escuderias ORDER BY nombre ASC")->fetchAll();

$coloresEscuderia = [
    'McLaren Mastercard F1 Team'         => '#FF8700',
    'Mercedes-AMG Petronas F1 Team'      => '#00D4B4',
    'Oracle Red Bull Racing'             => '#3B5BDB',
    'Scuderia Ferrari HP'                => '#DC0000',
    'Atlassian Williams F1 Team'         => '#00CFFF',
    'Visa Cash App Racing Bulls F1 Team' => '#8B5CF6',
    'Aston Martin Aramco F1 Team'        => '#00A693',
    'TGR Haas F1 Team'                   => '#B0B0B0',
    'Audi Revolut F1 Team'               => '#C0C0C0',
    'BWT Alpine F1 Team'                 => '#FF87BC',
    'Cadillac Formula 1 Team'            => '#C9A84C',
];

$tituloPagina = 'Admin Pilotos';
include __DIR__ . '/../private/header.php';
?>

<?php if ($flash): ?>
<div class="flash <?= $flash['tipo'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<div class="encabezado">
    <p class="panel">Panel de Administración</p>
    <h2>Gestión de Pilotos</h2>
</div>

<div class="admin-toolbar">
    <a href="mercado.php" class="admin-btn-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Volver al Mercado
    </a>
    <button class="admin-btn-nuevo" onclick="abrirModalNuevo()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Añadir Piloto
    </button>
    <span class="admin-count"><?= count($pilotos) ?> pilotos en el mercado</span>
</div>

<div class="admin-tabla-wrap">
    <table class="admin-tabla">
        <thead>
            <tr>
                <th style="width:60px">#</th>
                <th>Piloto</th>
                <th>Escudería</th>
                <th style="width:110px">Precio</th>
                <th style="width:160px">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pilotos as $p):
            $color = $coloresEscuderia[$p['escuderia']] ?? '#888888';
            $hex   = ltrim($color, '#');
            $r     = hexdec(substr($hex,0,2));
            $g     = hexdec(substr($hex,2,2));
            $b     = hexdec(substr($hex,4,2));
            $lum   = (0.299*$r + 0.587*$g + 0.114*$b) / 255;
            $textoBadge = $lum > 0.5 ? '#111111' : '#ffffff';
        ?>
            <tr class="admin-fila">
                <td>
                    <span class="admin-num-badge" style="background:<?= $color ?>;color:<?= $textoBadge ?>">
                        <?= (int)$p['numero'] ?>
                    </span>
                </td>
                <td>
                    <div class="admin-piloto-info">
                        <span class="admin-esc-stripe" style="background:<?= $color ?>"></span>
                        <div>
                            <span class="admin-piloto-nombre"><?= htmlspecialchars($p['nombre']) ?></span>
                            <?php if ($p['nacionalidad']): ?>
                                <span class="admin-nac"><?= htmlspecialchars($p['nacionalidad']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td class="admin-td-esc">
                    <span class="admin-esc-badge" style="border-color:<?= $color ?>;background:<?= $color ?>22">
                        <?= htmlspecialchars($p['escuderia']) ?>
                    </span>
                </td>
                <td class="admin-td-precio">
                    <?= number_format($p['precio'] / 1_000_000, 1, ',', '.') ?> M€
                </td>
                <td class="admin-td-acciones">
                    <button class="admin-btn-edit"
                        onclick="abrirModalEditar(<?= htmlspecialchars(json_encode([
                            'id_piloto'    => $p['id_piloto'],
                            'nombre'       => $p['nombre'],
                            'numero'       => $p['numero'],
                            'nacionalidad' => $p['nacionalidad'] ?? '',
                            'id_escuderia' => $p['id_escuderia'],
                            'precio'       => $p['precio'] / 1_000_000,
                            'imagen_url'   => $p['imagen_url'] ?? '',
                        ]), ENT_QUOTES) ?>)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Editar
                    </button>
                    <button class="admin-btn-del"
                        onclick="confirmarEliminar(
                            <?= (int)$p['id_piloto'] ?>,
                            '<?= htmlspecialchars(addslashes($p['nombre'])) ?>',
                            <?= (int)$p['numero'] ?>,
                            '<?= htmlspecialchars(addslashes($p['escuderia'])) ?>',
                            '<?= htmlspecialchars($color) ?>'
                        )">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        Eliminar
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ═══════════════════ MODAL EDITAR / AÑADIR ═══════════════════ -->
<div id="admin-modal-overlay" class="ap-overlay">
    <div class="ap-modal" id="ap-modal-box">
        <button class="ap-modal-close" onclick="cerrarModal()">&times;</button>
        <div class="ap-modal-header">
            <div class="ap-modal-header-icon" id="ap-header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <h3 id="modal-titulo">Editar Piloto</h3>
        </div>

        <form method="POST" action="admin_pilotos.php" id="modal-form" autocomplete="off">
            <input type="hidden" name="accion" id="modal-accion" value="guardar_edicion">
            <input type="hidden" name="id_piloto" id="modal-id" value="">

            <div class="ap-form-grid">
                <div class="ap-field ap-field--full">
                    <label>Nombre completo <span class="ap-req">*</span></label>
                    <input type="text" name="nombre" id="modal-nombre" required placeholder="Ej: Max Verstappen">
                </div>
                <div class="ap-field">
                    <label>Dorsal</label>
                    <input type="number" name="numero" id="modal-numero" min="0" max="99" placeholder="1">
                </div>
                <div class="ap-field">
                    <label>Nacionalidad</label>
                    <input type="text" name="nacionalidad" id="modal-nac" placeholder="Ej: Español">
                </div>
                <div class="ap-field">
                    <label>Escudería <span class="ap-req">*</span></label>
                    <select name="id_escuderia" id="modal-esc" required>
                        <option value="">— Selecciona —</option>
                        <?php foreach ($escuderias as $e): ?>
                        <option value="<?= $e['id_escuderia'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ap-field">
                    <label>Precio (M€) <span class="ap-req">*</span></label>
                    <input type="number" name="precio" id="modal-precio" step="0.5" min="0.5" required placeholder="14.5">
                </div>
                <div class="ap-field ap-field--full">
                    <label>URL imagen <span class="ap-opt">(opcional)</span></label>
                    <input type="url" name="imagen_url" id="modal-img" placeholder="https://...">
                </div>
            </div>

            <div class="ap-modal-footer">
                <button type="button" class="ap-btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="ap-btn-save" id="modal-save-btn">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════ MODAL ELIMINAR ═══════════════════ -->
<div id="admin-confirm-overlay" class="ap-overlay">
    <div class="del-modal">
        <button class="ap-modal-close del-close-btn" onclick="cerrarConfirm()">&times;</button>

        <div class="del-header" id="del-header">
            <div class="del-header-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
            </div>
        </div>

        <div class="del-body">
            <p class="del-title">¿Eliminar piloto?</p>

            <div class="del-card" id="del-card">
                <span class="del-card-stripe" id="del-card-stripe"></span>
                <div class="del-card-inner">
                    <span class="del-card-num" id="del-card-num"></span>
                    <div class="del-card-text">
                        <strong id="del-card-nombre"></strong>
                        <span id="del-card-esc"></span>
                    </div>
                </div>
            </div>

            <div class="del-warn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Se eliminará también de los equipos fantasy que lo tengan fichado.
            </div>

            <form method="POST" action="admin_pilotos.php">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_piloto" id="confirm-id" value="">
                <div class="del-footer">
                    <button type="button" class="ap-btn-cancel" onclick="cerrarConfirm()">Cancelar</button>
                    <button type="submit" class="del-btn-confirm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        Sí, eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../css/admin_pilotos.css">

<script src="../js/admin_pilotos.js"></script>

<?php include __DIR__ . '/../private/footer.php'; ?>
