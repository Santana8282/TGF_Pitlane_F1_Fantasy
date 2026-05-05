<?php
require_once __DIR__ . '/../private/funciones_auth.php';
require_once __DIR__ . '/../private/iconos.php';
require_once __DIR__ . '/../private/puntuacion.php';
requiereAutenticacion();

$pdo       = getDB();
$idUsuario = (int) $_SESSION['id_usuario'];

$puntosTotalesPilotos = getPuntosTotalesPilotos();

$equipo = obtenerOCrearEquipo($idUsuario, $pdo);

$idEquipo = (int) $equipo['id_equipo'];
$flash    = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'fichar') {
    $idPiloto = (int) ($_POST['id_piloto'] ?? 0);

    if ($idPiloto > 0) {

        if (!ventanaMercadoAbierta($pdo)) {
            $flash = ['tipo' => 'error', 'msg' => 'El mercado de fichajes está cerrado en este momento.'];
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
                $flash = ['tipo' => 'ok', 'msg' => '✓ ' . htmlspecialchars($nomPiloto) . ' fichado.' . $costeMsg];
            } else {
                $flash = ['tipo' => 'error', 'msg' => $res['error']];
            }
        }

        } // cierre else ventanaMercadoAbierta
    }

    $stmtPres = $pdo->prepare("SELECT presupuesto FROM equipos_fantasy WHERE id_equipo = ?");
    $stmtPres->execute([$idEquipo]);
    $equipo['presupuesto'] = (int) $stmtPres->fetchColumn();
}

$stmtFichados = $pdo->prepare("SELECT id_piloto FROM pilotos_equipo_fantasy WHERE id_equipo = ?");
$stmtFichados->execute([$idEquipo]);
$idsFichados  = array_column($stmtFichados->fetchAll(), 'id_piloto');

$stmtPilotos = $pdo->query("
    SELECT p.id_piloto, p.nombre, p.numero, p.precio, p.precio_anterior,
           p.precio_inicial, p.imagen_url,
           e.id_escuderia, e.nombre AS escuderia
    FROM pilotos p
    JOIN escuderias e ON e.id_escuderia = p.id_escuderia
    ORDER BY e.nombre ASC, p.precio DESC
");
$todosPilotos = $stmtPilotos->fetchAll();

$porEscuderia = [];
foreach ($todosPilotos as $p) {
    $porEscuderia[$p['escuderia']][] = $p;
}

$busqueda       = trim($_GET['buscar'] ?? '');
$filtroEscuderia = trim($_GET['escuderia'] ?? '');
$ordenar        = $_GET['orden'] ?? 'precio_desc';

if ($busqueda || $filtroEscuderia) {
    $todosPilotos = array_filter($todosPilotos, function($p) use ($busqueda, $filtroEscuderia) {
        $okNombre    = !$busqueda || stripos($p['nombre'], $busqueda) !== false || stripos($p['escuderia'], $busqueda) !== false;
        $okEscuderia = !$filtroEscuderia || $p['escuderia'] === $filtroEscuderia;
        return $okNombre && $okEscuderia;
    });
}

usort($todosPilotos, function($a, $b) use ($ordenar, $puntosTotalesPilotos) {
    switch ($ordenar) {
        case 'precio_asc':  return $a['precio'] - $b['precio'];
        case 'nombre_asc':  return strcmp($a['nombre'], $b['nombre']);
        case 'numero_asc':  return $a['numero'] - $b['numero'];
        case 'puntos_desc': return ($puntosTotalesPilotos[(int)$b['id_piloto']] ?? 0) - ($puntosTotalesPilotos[(int)$a['id_piloto']] ?? 0);
        default:            return $b['precio'] - $a['precio'];
    }
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accionEsc = $_POST['accion'] ?? '';

    if ($accionEsc === 'fichar_escuderia') {
        if (!ventanaMercadoAbierta($pdo)) {
            $flash = ['tipo' => 'error', 'msg' => 'El mercado de fichajes está cerrado en este momento.'];
        } else {
        $idEsc = (int) ($_POST['id_escuderia'] ?? 0);
        if ($idEsc > 0) {
            $res = ficharEscuderia($idEquipo, $idEsc);
            if ($res['ok']) {
                $stmtNomE = $pdo->prepare("SELECT nombre FROM escuderias WHERE id_escuderia = ?");
                $stmtNomE->execute([$idEsc]);
                $nomEsc = $stmtNomE->fetchColumn() ?: 'Escudería';
                $flash = ['tipo' => 'ok', 'msg' => '✓ ' . htmlspecialchars($nomEsc) . ' fichada por ' . number_format($res['precio'] / 1000000, 1) . 'M€.'];
            } else {
                $flash = ['tipo' => 'error', 'msg' => $res['error']];
            }
        }
        } // cierre else ventanaMercadoAbierta
    } elseif ($accionEsc === 'liberar_escuderia') {
        $idEsc = (int) ($_POST['id_escuderia'] ?? 0);
        if ($idEsc > 0) {
            $res = liberarEscuderia($idEquipo, $idEsc);
            if ($res['ok']) {
                $flash = ['tipo' => 'ok', 'msg' => '✓ ' . htmlspecialchars($res['nombre']) . ' liberada. +' . number_format($res['devolucion'] / 1000000, 1) . 'M€ devueltos (80%).'];
            } else {
                $flash = ['tipo' => 'error', 'msg' => $res['error']];
            }
        }
    }
}

$stmtPres = $pdo->prepare("SELECT presupuesto FROM equipos_fantasy WHERE id_equipo = ?");
$stmtPres->execute([$idEquipo]);
$presupuesto = (int) $stmtPres->fetchColumn();

$stmtTodas = $pdo->query("SELECT id_escuderia, nombre, precio_base FROM escuderias ORDER BY precio_base DESC");
$todasEscuderias = $stmtTodas->fetchAll();

$stmtEscFich = $pdo->prepare("SELECT id_escuderia FROM escuderia_equipo_fantasy WHERE id_equipo = ?");
$stmtEscFich->execute([$idEquipo]);
$idsEscFichadas = array_column($stmtEscFich->fetchAll(), 'id_escuderia');

$stmtPtsEsc = $pdo->query("
    SELECT p.id_escuderia, SUM(r.puntos_fantasy) AS total
    FROM resultados_carrera r
    JOIN pilotos p ON p.id_piloto = r.id_piloto
    GROUP BY p.id_escuderia
");
$puntosEscuderias = [];
foreach ($stmtPtsEsc->fetchAll() as $row) {
    $puntosEscuderias[(int)$row['id_escuderia']] = (int)$row['total'];
}

$coloresEscuderia = [
    'McLaren Mastercard F1 Team'        => '#FF8700', // naranja McLaren
    'Mercedes-AMG Petronas F1 Team'     => '#00F5C3', // turquesa brillante Mercedes
    'Oracle Red Bull Racing'            => '#0D1B8E', // azul marino Red Bull
    'Scuderia Ferrari HP'               => '#DC0000', // rojo Ferrari
    'Atlassian Williams F1 Team'        => '#00CFFF', // azul cielo claro Williams
    'Visa Cash App Racing Bulls F1 Team'=> '#8B5CF6', // violeta Racing Bulls
    'Aston Martin Aramco F1 Team'       => '#006F62', // verde botella Aston Martin
    'TGR Haas F1 Team'                  => '#9B9B9B', // gris grafito Haas
    'Audi Revolut F1 Team'              => '#1A1A1A', // negro Audi
    'BWT Alpine F1 Team'                => '#FF87BC', // rosa Alpine
    'Cadillac Formula 1 Team'           => '#C9A84C', // dorado Cadillac
];

$presupuesto = (int) $equipo['presupuesto'];
$totalPilotos = count($todosPilotos);
$fichados     = count($idsFichados);

$tituloPagina = 'Mercado';
include __DIR__ . '/../private/header.php';
?>

<?php if ($flash): ?>
<div class="flash <?= $flash['tipo'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<div class="encabezado">
    <p class="panel">Fichajes y Transferencias</p>
    <h2>Mercado de Pilotos</h2>
</div>

<?php if (esAdmin()): ?>
<div class="admin-acceso-mercado">
    <a href="admin_pilotos.php" class="admin-acceso-btn">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Gestionar pilotos
    </a>
</div>
<link rel="stylesheet" href="../css/mercado.css">
<?php endif; ?>

<div class="mercado-stats">
    <div class="mercado-stat">
        <p>Presupuesto</p>
        <strong><?= number_format($presupuesto / 1000000, 1, ',', '.') ?> <small>M€</small></strong>
    </div>
    <div class="mercado-stat">
        <p>Pilotos Fichados</p>
        <strong><?= $fichados ?> <small>/ <?= count($todosPilotos) + $fichados ?></small></strong>
    </div>
    <div class="mercado-stat">
        <p>Disponibles</p>
        <strong><?= $totalPilotos ?></strong>
    </div>
    <a href="../public/equipo.php" class="mercado-stat mercado-stat-link">
        <p>Ver mi equipo →</p>
        <strong><?= $fichados ?> pilotos</strong>
    </a>
</div>

<div class="mercado-filtros">
    <form method="GET" action="mercado.php" class="filtros-form">
        <input
            type="text"
            name="buscar"
            class="filtro-input"
            placeholder="Buscar piloto o escudería..."
            value="<?= htmlspecialchars($busqueda) ?>"
        >
        <select name="escuderia" class="filtro-select">
            <option value="">Todas las escuderías</option>
            <?php foreach (array_keys($porEscuderia) as $esc): ?>
            <option value="<?= htmlspecialchars($esc) ?>" <?= $filtroEscuderia === $esc ? 'selected' : '' ?>>
                <?= htmlspecialchars($esc) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <select name="orden" class="filtro-select">
            <option value="precio_desc"  <?= $ordenar === 'precio_desc'  ? 'selected' : '' ?>>Precio ↓</option>
            <option value="precio_asc"   <?= $ordenar === 'precio_asc'   ? 'selected' : '' ?>>Precio ↑</option>
            <option value="puntos_desc"  <?= $ordenar === 'puntos_desc'  ? 'selected' : '' ?>>Puntos ↓</option>
            <option value="nombre_asc"   <?= $ordenar === 'nombre_asc'   ? 'selected' : '' ?>>Nombre A-Z</option>
            <option value="numero_asc"   <?= $ordenar === 'numero_asc'   ? 'selected' : '' ?>>Número</option>
        </select>
        <button type="submit" class="filtro-btn">Filtrar</button>
        <?php if ($busqueda || $filtroEscuderia): ?>
        <a href="mercado.php" class="filtro-btn filtro-btn-reset">Limpiar</a>
        <?php endif; ?>
    </form>
</div>

<?php

$fotosPilotos = [
    'Max Verstappen'         => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/M/MAXVER01_Max_Verstappen/maxver01.png',
    'Liam Lawson'            => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/L/LIALAW01_Liam_Lawson/lialaw01.png',
    'Charles Leclerc'        => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/C/CHALEC01_Charles_Leclerc/chalec01.png',
    'Lewis Hamilton'         => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/L/LEWHAM01_Lewis_Hamilton/lewham01.png',
    'George Russell'         => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/G/GEORUS01_George_Russell/georus01.png',
    'Andrea Kimi Antonelli'  => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/A/ANDANT01_Andrea_Kimi_Antonelli/andant01.png',
    'Lando Norris'           => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/L/LANNOR01_Lando_Norris/lannor01.png',
    'Oscar Piastri'          => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/O/OSCPIA01_Oscar_Piastri/oscpia01.png',
    'Fernando Alonso'        => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/F/FERALO01_Fernando_Alonso/feralo01.png',
    'Lance Stroll'           => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/L/LANSTR01_Lance_Stroll/lanstr01.png',
    'Sergio Pérez'           => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/S/SERPER01_Sergio_Perez/serper01.png',
    'Jack Doohan'            => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/J/JACDOO01_Jack_Doohan/jacdoo01.png',
    'Arvid Lindblad'         => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/A/ARVLIN01_Arvid_Lindblad/arvlin01.png',
    'Carlos Sainz'           => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/C/CARSAI01_Carlos_Sainz/carsai01.png',
    'Carlos Sainz Jr.'       => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/C/CARSAI01_Carlos_Sainz/carsai01.png',
    'Yuki Tsunoda'           => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/Y/YUKTSU01_Yuki_Tsunoda/yuktsu01.png',
    'Isack Hadjar'           => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/I/ISAHAD01_Isack_Hadjar/isahad01.png',
    'Oliver Bearman'         => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/O/OLIBEA01_Oliver_Bearman/olibea01.png',
    'Franco Colapinto'       => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/F/FRACOL01_Franco_Colapinto/fracol01.png',
    'Nico Hülkenberg'        => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/N/NICHUL01_Nico_Hulkenberg/nichul01.png',
    'Valtteri Bottas'        => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/V/VALBOT01_Valtteri_Bottas/valbot01.png',
    'Alexander Albon'        => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/A/ALEALB01_Alexander_Albon/alealb01.png',
    'Pierre Gasly'           => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/P/PIEGAS01_Pierre_Gasly/piegas01.png',
    'Esteban Ocon'           => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/E/ESTOCO01_Esteban_Ocon/estoco01.png',
    'Gabriel Bortoleto'      => 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1200/content/dam/fom-website/drivers/G/GABBOR01_Gabriel_Bortoleto/gabbor01.png',
];
?>
<div class="mercado-grid">
<?php foreach ($todosPilotos as $p):
    $color     = $coloresEscuderia[$p['escuderia']] ?? '#888';
    $fichado   = in_array((int)$p['id_piloto'], array_map('intval', $idsFichados));
    $sinDinero = !$fichado && $presupuesto < (int)$p['precio'];
    $foto      = $p['imagen_url'] ?: ($fotosPilotos[$p['nombre']] ?? null);
    if ($foto) $foto = preg_replace('/w_\d+/', 'w_1200', $foto);
    $ptsPiloto = $puntosTotalesPilotos[(int)$p['id_piloto']] ?? 0;
    $ptsClass  = $ptsPiloto > 0 ? 'pts-pos' : ($ptsPiloto < 0 ? 'pts-neg' : 'pts-cero');
?>
    <div class="mercado-card <?= $fichado ? 'mercado-card-fichado' : '' ?>" style="border-left: 5px solid <?= htmlspecialchars($color) ?>;">

        <div class="mercado-foto-wrap">
            <?php
                $partes    = explode(' ', trim($p['nombre']));
                $iniciales = strtoupper(implode('', array_map(function($w) { return $w[0]; }, $partes)));
                $iniciales = substr($iniciales, 0, 3);
                $colorEsc  = ltrim($color, '#');
                $svgUrl    = 'driver-img.php?num=' . (int)$p['numero'] . '&ini=' . urlencode($iniciales) . '&color=' . urlencode($colorEsc);
            ?>
            <?php if ($foto): ?>
                <img src="<?= htmlspecialchars($foto) ?>"
                     alt="<?= htmlspecialchars($p['nombre']) ?>"
                     loading="lazy"
                     onerror="this.onerror=null;this.src='<?= htmlspecialchars($svgUrl) ?>';">
            <?php else: ?>
                <img src="<?= htmlspecialchars($svgUrl) ?>"
                     alt="<?= htmlspecialchars($p['nombre']) ?>"
                     loading="lazy">
            <?php endif; ?>
            <div class="mercado-foto-overlay"></div>
            <?php if ($fichado): ?>
                <span class="mercado-badge-sobre-foto fichado"><?= icono('check', 'icono-inline', 12) ?> En tu equipo</span>
            <?php endif; ?>
        </div>

        <div class="mercado-card-color" style="background-color:<?= $color ?>;"></div>

        <div class="mercado-card-body">

            <div class="mercado-card-top">
                <div class="mercado-info">
                    <p class="mercado-escuderia"><?= htmlspecialchars($p['escuderia']) ?></p>
                    <h4 class="mercado-nombre"><?= htmlspecialchars($p['nombre']) ?></h4>
                </div>
                <span class="mercado-num-badge">#<?= (int)$p['numero'] ?></span>
            </div>

            <div class="mercado-stats-row">
                <div class="mercado-stat-mini">
                    <span class="mercado-stat-label">Precio</span>
                    <span class="mercado-precio-val">
                        <?= number_format($p['precio'] / 1000000, 1, ',', '.') ?><small>M€</small>
                    </span>
                    <?php
                    $varPrecio = (int)$p['precio'] - (int)$p['precio_anterior'];
                    if ($varPrecio !== 0):
                        $varClass = $varPrecio > 0 ? 'precio-sube' : 'precio-baja';
                        $varFlecha = $varPrecio > 0 ? '▲' : '▼';
                        $varTexto = ($varPrecio > 0 ? '+' : '') . number_format($varPrecio / 1000000, 1, ',', '.') . 'M';
                    ?>
                    <span class="mercado-precio-var <?= $varClass ?>"><?= $varFlecha . ' ' . $varTexto ?></span>
                    <?php endif; ?>
                </div>
                <div class="mercado-divider-v"></div>
                <div class="mercado-stat-mini">
                    <span class="mercado-stat-label">Puntos temporada</span>
                    <span class="mercado-pts-val <?= $ptsClass ?>">
                        <?= $ptsPiloto !== 0 ? ($ptsPiloto > 0 ? '+' : '') . $ptsPiloto : '—' ?>
                    </span>
                </div>
            </div>

            <div class="mercado-card-footer">
                <?php if ($fichado): ?>
                    <span class="mercado-badge-fichado"><?= icono('check', 'icono-inline', 14) ?> Fichado</span>
                    <a href="../public/equipo.php" class="mercado-btn-ir">Ver equipo →</a>
                <?php elseif ($sinDinero): ?>
                    <span class="mercado-badge-fondos">Sin fondos</span>
                <?php else: ?>
                    <form method="POST" action="mercado.php<?= $busqueda || $filtroEscuderia || $ordenar !== 'precio_desc' ? '?' . http_build_query(['buscar' => $busqueda, 'escuderia' => $filtroEscuderia, 'orden' => $ordenar]) : '' ?>" style="flex:1;">
                        <input type="hidden" name="accion"    value="fichar">
                        <input type="hidden" name="id_piloto" value="<?= (int)$p['id_piloto'] ?>">
                        <button type="submit" class="mercado-btn-fichar">+ Fichar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php if (empty($todosPilotos)): ?>
<div class="mercado-vacio">
    <p>No se encontraron pilotos con ese filtro.</p>
    <a href="mercado.php">Ver todos</a>
</div>
<?php endif; ?>

<?php
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
    'Cadillac Formula 1 Team'            => 'https://media.formula1.com/image/upload/c_lfill,w_512/q_auto/d_common:f1:2026:fallback:car:2026fallbackcarright.webp/v1740000001/common/f1/2026/cadillac/2026cadillaccarright.webp',
];

?>

<div class="encabezado" style="margin-top:56px; padding-top:32px; border-top: 1px solid var(--color-borde);">
    <p class="panel">Constructores</p>
    <h2>MERCADO DE ESCUDERÍAS</h2>
</div>

<div class="mercado-grid">
<?php foreach ($todasEscuderias as $esc):
    $color     = $coloresEscuderia[$esc['nombre']] ?? '#888888';
    $fichada   = in_array((int)$esc['id_escuderia'], array_map('intval', $idsEscFichadas));
    $sinDinero = !$fichada && $presupuesto < (int)$esc['precio_base'];
    $ptsEsc    = $puntosEscuderias[(int)$esc['id_escuderia']] ?? 0;
    $ptsClass  = $ptsEsc > 0 ? 'pts-pos' : ($ptsEsc < 0 ? 'pts-neg' : 'pts-cero');
    $logo      = $logosEscuderia[$esc['nombre']] ?? null;

    $palabras  = explode(' ', $esc['nombre']);
    $abrev     = implode(' ', array_slice($palabras, 0, 2));
?>
    <div class="mercado-card <?= $fichada ? 'mercado-card-fichado' : '' ?>"
         style="border-left: 5px solid <?= $color ?>;">

        <div class="mercado-foto-wrap" style="background: linear-gradient(160deg, #0d0d0d 40%, <?= $color ?>18 100%);">
            <?php if ($logo): ?>
                <img src="<?= $logo ?>"
                     alt="<?= htmlspecialchars($esc['nombre']) ?>"
                     loading="lazy"
                     style="object-fit:contain; padding:16px;"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div style="display:none; align-items:center; justify-content:center; height:100%; width:100%; position:absolute; top:0; left:0;">
                    <span style="font-size:38px; font-weight:900; font-style:italic; color:<?= $color ?>; opacity:0.5; text-align:center; padding:10px; line-height:1.1;"><?= htmlspecialchars($abrev) ?></span>
                </div>
            <?php else: ?>
                <div style="display:flex; align-items:center; justify-content:center; height:100%;">
                    <span style="font-size:38px; font-weight:900; font-style:italic; color:<?= $color ?>; opacity:0.5; text-align:center; padding:10px; line-height:1.1;"><?= htmlspecialchars($abrev) ?></span>
                </div>
            <?php endif; ?>
            <div class="mercado-foto-overlay"></div>
            <?php if ($fichada): ?>
                <span class="mercado-badge-sobre-foto fichado"><?= icono('check', 'icono-inline', 12) ?> En tu equipo</span>
            <?php endif; ?>
        </div>

        <div class="mercado-card-color" style="background-color:<?= $color ?>;"></div>

        <div class="mercado-card-body">
            <div class="mercado-card-top">
                <div class="mercado-info">
                    <p class="mercado-escuderia">Constructor F1</p>
                    <h4 class="mercado-nombre" style="font-size:12px; line-height:1.3;">
                        <?= htmlspecialchars($esc['nombre']) ?>
                    </h4>
                </div>
            </div>

            <div class="mercado-stats-row">
                <div class="mercado-stat-mini">
                    <span class="mercado-stat-label">Precio</span>
                    <span class="mercado-precio-val">
                        <?= number_format($esc['precio_base'] / 1000000, 1, ',', '.') ?><small>M€</small>
                    </span>
                </div>
                <div class="mercado-divider-v"></div>
                <div class="mercado-stat-mini">
                    <span class="mercado-stat-label">Puntos temporada</span>
                    <span class="mercado-pts-val <?= $ptsClass ?>">
                        <?= $ptsEsc !== 0 ? ($ptsEsc > 0 ? '+' : '') . $ptsEsc : '—' ?>
                    </span>
                </div>
            </div>

            <div class="mercado-card-footer">
                <?php if ($fichada): ?>
                    <span class="mercado-badge-fichado"><?= icono('check', 'icono-inline', 14) ?> Fichada</span>
                    <form method="POST" style="flex:1;">
                        <input type="hidden" name="accion"       value="liberar_escuderia">
                        <input type="hidden" name="id_escuderia" value="<?= (int)$esc['id_escuderia'] ?>">
                        <button type="submit" class="mercado-btn-fichar"
                                style="background:#2a2a2a; color:#ccc; font-size:11px;"
                                onclick="return confirm('¿Liberar esta escudería? Recibirás el 80%.')">
                            Liberar
                        </button>
                    </form>
                <?php elseif ($sinDinero): ?>
                    <span class="mercado-badge-fondos">Sin fondos</span>
                <?php else: ?>
                    <form method="POST" style="flex:1;">
                        <input type="hidden" name="accion"       value="fichar_escuderia">
                        <input type="hidden" name="id_escuderia" value="<?= (int)$esc['id_escuderia'] ?>">
                        <button type="submit" class="mercado-btn-fichar">+ Fichar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php include __DIR__ . '/../private/footer.php'; ?>
