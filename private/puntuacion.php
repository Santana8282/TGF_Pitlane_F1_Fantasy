<?php

require_once __DIR__ . '/database.php';

define('PTS_POS',         [1=>25,2=>18,3=>15,4=>12,5=>10,6=>8,7=>6,8=>4,9=>2,10=>1]);
define('PTS_11_14',       2);
define('PTS_POLE',        10);
define('PTS_Q3',          5);
define('PTS_Q2',          2);
define('PTS_VUELTA_RAP',  10);
define('PTS_SECTOR',      5);
define('PTS_TERMINAR',    2);
define('PTS_ADELANTO',    3);
define('PTS_ADELANTO_MAX',15);
define('PTS_RETROCESO',   -2);
define('PTS_RETROCESO_MAX',-10);
define('PTS_BONUS5',      10);
define('PTS_BONUS3',      5);
define('PTS_DNF',         -10);
define('PTS_DSQ',         -20);
define('PTS_BANDER_AM',   -5);
define('PTS_BANDER_ROJ',  -15);
define('PTS_PEN',         -5);
define('PTS_CAMBIO_EXTRA',-4);
define('ESC_DIVISOR',     5);
define('ESC_BONUS_AMBOS', 5);
define('ESC_BONUS_PODIO', 3);
define('ESC_BONUS_DOBLE', 8);
define('MAX_MISMA_ESC',   2);
define('MAX_PILOTOS',     5);
define('CAMBIOS_GRATIS',  3);

define('PEN_ALIN_NADA',       0);   
define('PEN_ALIN_UNO',       -10);  
define('PEN_ALIN_DOS',       -20);  
define('PEN_ALIN_TRES',      -30);  

function calcularPuntosResultado(array $r): array
{
    $desglose = [];
    $total    = 0;

    $pos    = (int)($r['posicion']        ?? 0);
    $salida = (int)($r['posicion_salida'] ?? 0);
    $estado = strtolower(trim($r['estado'] ?? 'finished'));
    $dnf    = in_array($estado, ['dnf','ret','accident','collision','mechanical']);
    $dsq    = ($estado === 'dsq');

    if (!$dnf && !$dsq && $pos >= 1) {
        $tabPts = PTS_POS;
        if (isset($tabPts[$pos]))           $pts = $tabPts[$pos];
        elseif ($pos >= 11 && $pos <= 14)   $pts = PTS_11_14;
        else                                $pts = 0;

        $desglose[] = ['criterio'=>'posicion', 'puntos'=>$pts, 'descripcion'=>"P{$pos} en carrera"];
        $total += $pts;
    }

    if (!empty($r['pole_position'])) {
        $desglose[] = ['criterio'=>'pole', 'puntos'=>PTS_POLE, 'descripcion'=>'Pole position'];
        $total += PTS_POLE;
    }

    if ($salida >= 1 && empty($r['pole_position'])) {
        if ($salida <= 10) {
            $desglose[] = ['criterio'=>'q3', 'puntos'=>PTS_Q3, 'descripcion'=>"Q3 — P{$salida} parrilla"];
            $total += PTS_Q3;
        } elseif ($salida <= 15) {
            $desglose[] = ['criterio'=>'q2', 'puntos'=>PTS_Q2, 'descripcion'=>"Q2 — P{$salida} parrilla"];
            $total += PTS_Q2;
        }
    }

    if (!$dnf && !$dsq && $pos >= 1) {
        $desglose[] = ['criterio'=>'termino', 'puntos'=>PTS_TERMINAR, 'descripcion'=>'Completó la carrera'];
        $total += PTS_TERMINAR;
    } elseif ($dnf) {
        $desglose[] = ['criterio'=>'abandono', 'puntos'=>PTS_DNF, 'descripcion'=>'Abandono / DNF'];
        $total += PTS_DNF;
    } elseif ($dsq) {
        $desglose[] = ['criterio'=>'dsq', 'puntos'=>PTS_DSQ, 'descripcion'=>'Descalificación'];
        $total += PTS_DSQ;
    }

    if (!empty($r['vuelta_rapida'])) {
        $desglose[] = ['criterio'=>'vuelta_rapida', 'puntos'=>PTS_VUELTA_RAP, 'descripcion'=>'Vuelta rápida (+' . PTS_VUELTA_RAP . ' pts)'];
        $total += PTS_VUELTA_RAP;
    }

    if (!empty($r['mejor_sector'])) {
        $desglose[] = ['criterio'=>'mejor_sector', 'puntos'=>PTS_SECTOR, 'descripcion'=>'Mejor sector (+' . PTS_SECTOR . ' pts)'];
        $total += PTS_SECTOR;
    }

    if ($salida > 0 && $pos > 0 && !$dnf && !$dsq) {
        $diff = $salida - $pos;

        if ($diff > 0) {
            $pts = min($diff * PTS_ADELANTO, PTS_ADELANTO_MAX);
            $desglose[] = ['criterio'=>'adelantamiento', 'puntos'=>$pts,
                           'descripcion'=>"{$diff} pos. ganada(s) (+{$pts} pts)"];
            $total += $pts;

            if ($diff >= 5) {
                $desglose[] = ['criterio'=>'bonus_supero', 'puntos'=>PTS_BONUS5,
                               'descripcion'=>'Bonus: superó expectativas ≥5 pos (+' . PTS_BONUS5 . ' pts)'];
                $total += PTS_BONUS5;
            } elseif ($diff >= 3) {
                $desglose[] = ['criterio'=>'bonus_supero', 'puntos'=>PTS_BONUS3,
                               'descripcion'=>'Bonus: superó expectativas 3-4 pos (+' . PTS_BONUS3 . ' pts)'];
                $total += PTS_BONUS3;
            }
        } elseif ($diff < 0) {
            $perdidas = abs($diff);
            $pts = max($perdidas * PTS_RETROCESO, PTS_RETROCESO_MAX);
            $desglose[] = ['criterio'=>'retroceso', 'puntos'=>$pts,
                           'descripcion'=>"{$perdidas} pos. perdida(s) ({$pts} pts)"];
            $total += $pts;
        }
    }

    $bAm = (int)($r['banderas_amarillas'] ?? 0);
    if ($bAm > 0) {
        $pts = $bAm * PTS_BANDER_AM;
        $desglose[] = ['criterio'=>'bandera_amarilla', 'puntos'=>$pts,
                       'descripcion'=>"{$bAm} bandera(s) amarilla(s) causada(s) ({$pts} pts)"];
        $total += $pts;
    }
    $bRoj = (int)($r['banderas_rojas'] ?? 0);
    if ($bRoj > 0) {
        $pts = $bRoj * PTS_BANDER_ROJ;
        $desglose[] = ['criterio'=>'bandera_roja', 'puntos'=>$pts,
                       'descripcion'=>"{$bRoj} bandera(s) roja(s) causada(s) ({$pts} pts)"];
        $total += $pts;
    }
    $pen = (int)($r['penalizaciones'] ?? 0);
    if ($pen > 0) {
        $pts = $pen * PTS_PEN;
        $desglose[] = ['criterio'=>'penalizacion', 'puntos'=>$pts,
                       'descripcion'=>"{$pen} penalización(es) ({$pts} pts)"];
        $total += $pts;
    }

    return ['puntos_total' => $total, 'desglose' => $desglose];
}

function calcularPuntosEscuderia(int $idEscuderia, int $idCarrera): array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT rc.posicion, rc.puntos_oficiales, p.nombre
        FROM resultados_carrera rc
        JOIN pilotos p ON p.id_piloto = rc.id_piloto
        WHERE rc.id_carrera = ? AND p.id_escuderia = ?
        ORDER BY rc.posicion ASC
    ");
    $stmt->execute([$idCarrera, $idEscuderia]);
    $pilotos = $stmt->fetchAll();

    if (empty($pilotos)) return ['puntos' => 0, 'detalle' => 'Sin resultados'];

    $sumaPts = 0; $posiciones = []; $detalles = [];
    foreach ($pilotos as $p) {
        $pos      = (int)($p['posicion'] ?? 0);
        $ptsOf    = (int)($p['puntos_oficiales'] ?? 0);
        $sumaPts += $ptsOf;
        $posiciones[] = $pos;
        $detalles[]   = htmlspecialchars($p['nombre']) . " P{$pos} ({$ptsOf} pts F1)";
    }

    $ptsBase = (int)round($sumaPts / ESC_DIVISOR);
    $total   = $ptsBase;
    $bonuses = [];

    $top10 = array_filter($posiciones, function($p) { return $p >= 1 && $p <= 10; });
    if (count($top10) >= 2) { $total += ESC_BONUS_AMBOS; $bonuses[] = 'Ambos top10 (+' . ESC_BONUS_AMBOS . ')'; }

    $podio = array_filter($posiciones, function($p) { return $p >= 1 && $p <= 3; });
    if (count($podio) >= 2)    { $total += ESC_BONUS_DOBLE; $bonuses[] = 'Doble podio (+' . ESC_BONUS_DOBLE . ')'; }
    elseif (count($podio) === 1) { $total += ESC_BONUS_PODIO; $bonuses[] = 'Piloto en podio (+' . ESC_BONUS_PODIO . ')'; }

    $detalle = implode(' | ', $detalles);
    if ($bonuses) $detalle .= ' | ' . implode(' | ', $bonuses);
    $detalle .= " → {$total} pts fantasy";

    return ['puntos' => $total, 'detalle' => $detalle];
}

function guardarPuntosEnEquipos(
    int $idResultado, int $idPiloto, int $idCarrera,
    int $puntos, array $desglose, string $detalle
): void {
    $pdo  = getDB();

    $pdo->prepare("UPDATE resultados_carrera SET puntos_fantasy=? WHERE id_resultado=?")
        ->execute([$puntos, $idResultado]);

    $stmt = $pdo->prepare("
        SELECT pef.id_equipo, pef.es_capitan
        FROM pilotos_equipo_fantasy pef
        JOIN carreras c ON c.id_carrera = ?
        WHERE pef.id_piloto = ?
          AND (pef.fecha_inclusion IS NULL OR DATE(pef.fecha_inclusion) <= DATE(c.fecha))
    ");
    $stmt->execute([$idCarrera, $idPiloto]);
    $equipos = $stmt->fetchAll();

    foreach ($equipos as $eq) {
        $idEquipo  = (int)$eq['id_equipo'];
        $esCapitan = (bool)$eq['es_capitan'];
        $ptsTotal  = $esCapitan ? $puntos * 2 : $puntos;

        $pdo->prepare("DELETE FROM puntos_fantasy WHERE id_equipo=? AND id_piloto=? AND id_carrera=?")
            ->execute([$idEquipo, $idPiloto, $idCarrera]);

        $pdo->prepare("
            INSERT INTO puntos_fantasy
                (id_equipo,id_piloto,id_carrera,puntos,detalle,es_capitan,puntos_base)
            VALUES (?,?,?,?,?,?,?)
        ")->execute([$idEquipo,$idPiloto,$idCarrera,$ptsTotal,$detalle,(int)$esCapitan,$puntos]);
    }

    $pdo->prepare("DELETE FROM puntos_desglose WHERE id_resultado=?")->execute([$idResultado]);
    $ins = $pdo->prepare("INSERT INTO puntos_desglose (id_resultado,criterio,puntos,descripcion) VALUES (?,?,?,?)");
    foreach ($desglose as $d) $ins->execute([$idResultado,$d['criterio'],$d['puntos'],$d['descripcion']]);
}

function guardarPuntosEscuderiaEnEquipos(int $idEscuderia, int $idCarrera): void
{
    $pdo  = getDB();
    $calc = calcularPuntosEscuderia($idEscuderia, $idCarrera);

    $stmt = $pdo->prepare("
        SELECT eef.id_equipo
        FROM escuderia_equipo_fantasy eef
        JOIN carreras c ON c.id_carrera = ?
        WHERE eef.id_escuderia = ?
          AND (eef.fecha_inclusion IS NULL OR DATE(eef.fecha_inclusion) <= DATE(c.fecha))
    ");
    $stmt->execute([$idCarrera, $idEscuderia]);
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $idEquipo) {
        $pdo->prepare("DELETE FROM puntos_escuderia_fantasy WHERE id_equipo=? AND id_escuderia=? AND id_carrera=?")
            ->execute([$idEquipo,$idEscuderia,$idCarrera]);
        $pdo->prepare("INSERT INTO puntos_escuderia_fantasy (id_equipo,id_escuderia,id_carrera,puntos,detalle) VALUES (?,?,?,?,?)")
            ->execute([$idEquipo,$idEscuderia,$idCarrera,$calc['puntos'],$calc['detalle']]);
    }
}

function recalcularResumenJornada(int $idCarrera): void
{
    $pdo     = getDB();
    $stmtEq = $pdo->prepare("
        SELECT ef.id_equipo FROM equipos_fantasy ef
        JOIN carreras c ON c.id_carrera = ?
        WHERE ef.fecha_creacion IS NULL OR DATE(ef.fecha_creacion) <= DATE(c.fecha)
    ");
    $stmtEq->execute([$idCarrera]);
    $equipos = $stmtEq->fetchAll(PDO::FETCH_COLUMN);

    foreach ($equipos as $idEquipo) {
        $stmtP = $pdo->prepare("
            SELECT COALESCE(SUM(puntos_base),0) AS brutos,
                   COALESCE(SUM(puntos-puntos_base),0) AS bonus_cap
            FROM puntos_fantasy WHERE id_equipo=? AND id_carrera=? AND es_escuderia=0
        ");
        $stmtP->execute([$idEquipo,$idCarrera]);
        $rowP = $stmtP->fetch();

        $stmtE = $pdo->prepare("SELECT COALESCE(SUM(puntos),0) FROM puntos_escuderia_fantasy WHERE id_equipo=? AND id_carrera=?");
        $stmtE->execute([$idEquipo,$idCarrera]);
        $ptsEsc = (int)$stmtE->fetchColumn();

        $stmtC = $pdo->prepare("SELECT COALESCE(SUM(coste_cambio),0) FROM historial_plantilla WHERE id_equipo=? AND id_carrera=?");
        $stmtC->execute([$idEquipo,$idCarrera]);
        $pen = abs((int)$stmtC->fetchColumn());

        $stmtSlots = $pdo->prepare("SELECT COUNT(*) FROM pilotos_equipo_fantasy WHERE id_equipo = ? AND slot IN (1,2)");
        $stmtSlots->execute([$idEquipo]);
        $slotsOcupados = (int)$stmtSlots->fetchColumn(); 
        $stmtTieneEsc = $pdo->prepare("SELECT COUNT(*) FROM escuderia_equipo_fantasy WHERE id_equipo = ?");
        $stmtTieneEsc->execute([$idEquipo]);
        $tieneEscuderia = (int)$stmtTieneEsc->fetchColumn(); 
        $huecos = (2 - $slotsOcupados) + (1 - $tieneEscuderia); 

        $penAlin = 0;
        if ($huecos === 3) {
            $penAlin = abs(PEN_ALIN_TRES);   
        } elseif ($huecos === 2) {
            $penAlin = abs(PEN_ALIN_DOS);    
        } elseif ($huecos === 1) {
            $penAlin = abs(PEN_ALIN_UNO);    
        }

        $pen += $penAlin;

        $brutos   = (int)($rowP['brutos'] ?? 0) + $ptsEsc;
        $bonusCap = (int)($rowP['bonus_cap'] ?? 0);
        $total    = $brutos + $bonusCap - $pen;

        $pdo->prepare("
            INSERT INTO puntos_fantasy_carrera (id_equipo,id_carrera,puntos_brutos,bonus_capitan,penalizacion,puntos_total)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
                puntos_brutos=VALUES(puntos_brutos), bonus_capitan=VALUES(bonus_capitan),
                penalizacion=VALUES(penalizacion),   puntos_total=VALUES(puntos_total)
        ")->execute([$idEquipo,$idCarrera,$brutos,$bonusCap,$pen,$total]);
    }

    $ranking = $pdo->prepare("SELECT id_equipo FROM puntos_fantasy_carrera WHERE id_carrera=? ORDER BY puntos_total DESC");
    $ranking->execute([$idCarrera]);
    $upd = $pdo->prepare("UPDATE puntos_fantasy_carrera SET posicion_jornada=? WHERE id_equipo=? AND id_carrera=?");
    foreach ($ranking->fetchAll(PDO::FETCH_COLUMN) as $i => $idEq) $upd->execute([$i+1,$idEq,$idCarrera]);

    $pdo->prepare("
        UPDATE equipos_fantasy ef
        JOIN puntos_fantasy_carrera pfc ON pfc.id_equipo=ef.id_equipo AND pfc.id_carrera=?
        SET ef.puntos_jornada=pfc.puntos_total
    ")->execute([$idCarrera]);
}

function procesarCarrera(int $idCarrera): array
{
    $pdo       = getDB();
    $stmt      = $pdo->prepare("SELECT * FROM resultados_carrera WHERE id_carrera=?");
    $stmt->execute([$idCarrera]);
    $resultados = $stmt->fetchAll();

    $procesados = $errores = 0;
    foreach ($resultados as $r) {
        try {
            $calc    = calcularPuntosResultado($r);
            $detalle = implode(' | ', array_map(function($d) { return $d['descripcion']; }, $calc['desglose']));
            guardarPuntosEnEquipos((int)$r['id_resultado'],(int)$r['id_piloto'],$idCarrera,$calc['puntos_total'],$calc['desglose'],$detalle);
            $procesados++;
        } catch (Exception $e) { $errores++; error_log("Error resultado {$r['id_resultado']}: ".$e->getMessage()); }
    }

    foreach ($pdo->query("SELECT id_escuderia FROM escuderias")->fetchAll(PDO::FETCH_COLUMN) as $idEsc) {
        try { guardarPuntosEscuderiaEnEquipos((int)$idEsc,$idCarrera); }
        catch (Exception $e) { error_log("Error escudería {$idEsc}: ".$e->getMessage()); }
    }

    recalcularResumenJornada($idCarrera);
    actualizarValoresMercado($idCarrera);
    $pdo->prepare("INSERT INTO sync_log (id_carrera,estado,mensaje) VALUES (?,'ok',?)")
        ->execute([$idCarrera,"Procesados: {$procesados}, Errores: {$errores}"]);

    return ['procesados' => $procesados, 'errores' => $errores];
}

function sincronizarDesdeErgast(int $idCarrera): array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT c.*, t.anio FROM carreras c JOIN temporadas t ON t.id_temporada=c.id_temporada WHERE c.id_carrera=?");
    $stmt->execute([$idCarrera]);
    $carrera = $stmt->fetch();

    if (!$carrera) return ['ok'=>false, 'mensaje'=>'Carrera no encontrada'];
    if (strtotime($carrera['fecha']) > time()) return ['ok'=>false, 'mensaje'=>'Carrera no disputada aún'];

    $anio  = $carrera['anio'];
    $ronda = $carrera['numero_carrera'];
    $ctx   = stream_context_create(['http'=>['timeout'=>20,'user_agent'=>'F1Fantasy/2.0']]);

    $jsonRes   = @file_get_contents("https://api.jolpi.ca/ergast/f1/{$anio}/{$ronda}/results.json",    false, $ctx);
    $jsonQuali = @file_get_contents("https://api.jolpi.ca/ergast/f1/{$anio}/{$ronda}/qualifying.json", false, $ctx);

    if (!$jsonRes) {

        sleep(2);
        $jsonRes   = @file_get_contents("https://api.jolpi.ca/ergast/f1/{$anio}/{$ronda}/results.json",    false, $ctx);
        $jsonQuali = @file_get_contents("https://api.jolpi.ca/ergast/f1/{$anio}/{$ronda}/qualifying.json", false, $ctx);
    }

    if (!$jsonRes) return ['ok'=>false, 'mensaje'=>"No se pudo conectar con la API (ronda {$ronda}/{$anio})"];

    $dataRes   = json_decode($jsonRes,  true);
    $dataQuali = $jsonQuali ? json_decode($jsonQuali, true) : null;
    $races     = $dataRes['MRData']['RaceTable']['Races'] ?? [];
    if (empty($races)) return ['ok'=>false, 'mensaje'=>'Sin resultados en la API'];

    $resultadosApi = $races[0]['Results'] ?? [];

    $posQuali = [];
    if ($dataQuali) {
        foreach ($dataQuali['MRData']['RaceTable']['Races'][0]['QualifyingResults'] ?? [] as $qr) {
            $posQuali[strtolower($qr['Driver']['familyName'])] = (int)$qr['position'];
        }
    }

    $pdo->prepare("DELETE FROM puntos_fantasy WHERE id_carrera = ?")->execute([$idCarrera]);
    $pdo->prepare("DELETE FROM puntos_escuderia_fantasy WHERE id_carrera = ?")->execute([$idCarrera]);
    $pdo->prepare("DELETE FROM puntos_fantasy_carrera WHERE id_carrera = ?")->execute([$idCarrera]);
    $pdo->prepare("DELETE FROM puntos_desglose WHERE id_resultado IN (SELECT id_resultado FROM resultados_carrera WHERE id_carrera = ?)")->execute([$idCarrera]);

    $mapa = [];
    foreach ($pdo->query("SELECT id_piloto, nombre FROM pilotos")->fetchAll() as $p) {
        $partes = explode(' ', trim($p['nombre']));
        $mapa[strtolower(end($partes))] = (int)$p['id_piloto'];
    }

    $insertados = 0;
    foreach ($resultadosApi as $res) {
        $familia  = strtolower($res['Driver']['familyName'] ?? '');
        $idPiloto = $mapa[$familia] ?? null;
        if (!$idPiloto) continue;

        $pos      = (int)($res['position'] ?? 0);
        $grid     = (int)($res['grid']     ?? 0);
        $vRapida  = (!empty($res['FastestLap']['rank']) && (int)$res['FastestLap']['rank'] === 1) ? 1 : 0;
        $ptsOf    = (int)($res['points']   ?? 0);
        $estadoR  = $res['status'] ?? 'Finished';

        $estadoNorm = 'finished';
        if (stripos($estadoR, 'Finished') === false && !ctype_digit(trim($estadoR, '+ Lap'))) {
            $estadoNorm = stripos($estadoR, 'Disqualified') !== false ? 'dsq' : 'dnf';
        }

        $pole        = ($grid === 1) ? 1 : 0;
        $adelantados = ($grid > 0 && $pos > 0 && $pos < $grid) ? ($grid - $pos) : 0;
        $posQual     = $posQuali[$familia] ?? $grid;

        $pdo->prepare("DELETE FROM resultados_carrera WHERE id_carrera=? AND id_piloto=?")->execute([$idCarrera,$idPiloto]);
        $pdo->prepare("
            INSERT INTO resultados_carrera
                (id_carrera,id_piloto,posicion,puntos_oficiales,vuelta_rapida,estado,
                 posicion_salida,adelantamientos,pole_position,fuente_datos)
            VALUES (?,?,?,?,?,?,?,?,?,'api_jolpica')
        ")->execute([$idCarrera,$idPiloto,$pos,$ptsOf,$vRapida,$estadoNorm,$posQual,$adelantados,$pole]);

        $insertados++;
    }

    $resultado = procesarCarrera($idCarrera);
    return ['ok'=>true, 'mensaje'=>"Sincronizados {$insertados} pilotos. Puntos calculados: {$resultado['procesados']}."];
}

function validarFichajePiloto(int $idEquipo, int $idPiloto): array
{
    $pdo = getDB();

    $s = $pdo->prepare("SELECT COUNT(*) FROM pilotos_equipo_fantasy WHERE id_equipo=? AND id_piloto=?");
    $s->execute([$idEquipo,$idPiloto]);
    if ($s->fetchColumn() > 0) return ['ok'=>false,'error'=>'Ese piloto ya está en tu plantilla.'];

    $s = $pdo->prepare("SELECT COUNT(*) FROM pilotos_equipo_fantasy WHERE id_equipo=?");
    $s->execute([$idEquipo]);
    if ($s->fetchColumn() >= MAX_PILOTOS) return ['ok'=>false,'error'=>'Plantilla completa (máx. '.MAX_PILOTOS.' pilotos).'];

    $s = $pdo->prepare("SELECT id_escuderia, precio FROM pilotos WHERE id_piloto=?");
    $s->execute([$idPiloto]);
    $piloto = $s->fetch();
    if (!$piloto) return ['ok'=>false,'error'=>'Piloto no encontrado.'];

    $s = $pdo->prepare("SELECT COUNT(*) FROM pilotos_equipo_fantasy pef JOIN pilotos p ON p.id_piloto=pef.id_piloto WHERE pef.id_equipo=? AND p.id_escuderia=?");
    $s->execute([$idEquipo,$piloto['id_escuderia']]);
    if ($s->fetchColumn() >= MAX_MISMA_ESC) return ['ok'=>false,'error'=>'Ya tienes '.MAX_MISMA_ESC.' pilotos de esa escudería (máximo).'];

    $s = $pdo->prepare("SELECT presupuesto FROM equipos_fantasy WHERE id_equipo=?");
    $s->execute([$idEquipo]);
    if ((int)$s->fetchColumn() < (int)$piloto['precio']) return ['ok'=>false,'error'=>'Presupuesto insuficiente.'];

    return ['ok'=>true,'error'=>''];
}

function _getCambiosYVentana(int $idEquipo, int $idCarreraProxima): array
{
    $pdo = getDB();

    $s = $pdo->prepare("SELECT COUNT(*) FROM historial_plantilla WHERE id_equipo=? AND id_carrera=?");
    $s->execute([$idEquipo,$idCarreraProxima]);
    $usados = (int)$s->fetchColumn();

    $s = $pdo->prepare("SELECT cambios_gratis, coste_extra FROM ventana_mercado WHERE id_carrera_hasta=? ORDER BY id DESC LIMIT 1");
    $s->execute([$idCarreraProxima]);
    $ventana = $s->fetch();
    $gratis  = $ventana ? (int)$ventana['cambios_gratis'] : CAMBIOS_GRATIS;
    $coste   = $ventana ? (int)$ventana['coste_extra']    : abs(PTS_CAMBIO_EXTRA);

    return ['usados'=>$usados,'gratis'=>$gratis,'coste'=>$coste,'libre'=>$usados < $gratis];
}

function ficharPiloto(int $idEquipo, int $idPiloto, int $idCarreraProxima): array
{
    $val = validarFichajePiloto($idEquipo,$idPiloto);
    if (!$val['ok']) return array_merge($val,['coste'=>0]);

    $pdo = getDB();
    $s   = $pdo->prepare("SELECT precio, nombre FROM pilotos WHERE id_piloto=?");
    $s->execute([$idPiloto]);
    $piloto = $s->fetch();

    $vent  = _getCambiosYVentana($idEquipo,$idCarreraProxima);
    $coste = $vent['libre'] ? 0 : $vent['coste'];

    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO pilotos_equipo_fantasy (id_equipo,id_piloto) VALUES (?,?)")->execute([$idEquipo,$idPiloto]);
        $pdo->prepare("UPDATE equipos_fantasy SET presupuesto=presupuesto-? WHERE id_equipo=?")->execute([$piloto['precio'],$idEquipo]);
        $pdo->prepare("INSERT INTO historial_plantilla (id_equipo,id_carrera,accion,id_piloto,coste_cambio) VALUES (?,?,'fichar',?,?)")
            ->execute([$idEquipo,$idCarreraProxima,$idPiloto,$coste]);
        $pdo->commit();
        return ['ok'=>true,'error'=>'','coste'=>$coste];
    } catch (Exception $e) { $pdo->rollBack(); return ['ok'=>false,'error'=>'Error al fichar.','coste'=>0]; }
}

function liberarPiloto(int $idEquipo, int $idPiloto, int $idCarreraProxima): array
{
    $pdo = getDB();
    $s   = $pdo->prepare("SELECT precio, nombre FROM pilotos WHERE id_piloto=?");
    $s->execute([$idPiloto]);
    $piloto = $s->fetch();
    if (!$piloto) return ['ok'=>false,'error'=>'Piloto no encontrado.'];

    $devolucion = (int)round($piloto['precio'] * 0.80);

    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE pilotos_equipo_fantasy SET es_capitan=0 WHERE id_equipo=? AND id_piloto=?")->execute([$idEquipo,$idPiloto]);
        $del = $pdo->prepare("DELETE FROM pilotos_equipo_fantasy WHERE id_equipo=? AND id_piloto=?");
        $del->execute([$idEquipo,$idPiloto]);
        if ($del->rowCount() === 0) { $pdo->rollBack(); return ['ok'=>false,'error'=>'El piloto no estaba en tu plantilla.']; }
        $pdo->prepare("UPDATE equipos_fantasy SET presupuesto=presupuesto+? WHERE id_equipo=?")->execute([$devolucion,$idEquipo]);
        $pdo->prepare("INSERT INTO historial_plantilla (id_equipo,id_carrera,accion,id_piloto,coste_cambio) VALUES (?,?,'liberar',?,0)")
            ->execute([$idEquipo,$idCarreraProxima,$idPiloto]);
        $pdo->commit();
        return ['ok'=>true,'error'=>'','devolucion'=>$devolucion];
    } catch (Exception $e) { $pdo->rollBack(); return ['ok'=>false,'error'=>'Error al liberar.']; }
}

function cambiarCapitan(int $idEquipo, int $idPilotoNuevo): array
{
    $pdo = getDB();
    $s   = $pdo->prepare("SELECT COUNT(*) FROM pilotos_equipo_fantasy WHERE id_equipo=? AND id_piloto=?");
    $s->execute([$idEquipo,$idPilotoNuevo]);
    if (!$s->fetchColumn()) return ['ok'=>false,'error'=>'Ese piloto no está en tu plantilla.'];

    $pdo->prepare("UPDATE pilotos_equipo_fantasy SET es_capitan=0 WHERE id_equipo=?")->execute([$idEquipo]);
    $pdo->prepare("UPDATE pilotos_equipo_fantasy SET es_capitan=1 WHERE id_equipo=? AND id_piloto=?")->execute([$idEquipo,$idPilotoNuevo]);

    $s = $pdo->prepare("SELECT MIN(id_carrera) FROM carreras WHERE fecha >= CURDATE()");
    $s->execute();
    $idPrxCarrera = (int)$s->fetchColumn();
    if ($idPrxCarrera) {
        $pdo->prepare("INSERT INTO historial_plantilla (id_equipo,id_carrera,accion,id_piloto,coste_cambio) VALUES (?,?,'cambiar_capitan',?,0)")
            ->execute([$idEquipo,$idPrxCarrera,$idPilotoNuevo]);
    }

    return ['ok'=>true,'error'=>''];
}

function getPuntosTotalesPilotos(): array
{
    $pdo  = getDB();
    $stmt = $pdo->query("
        SELECT p.id_piloto,
               COALESCE(SUM(
                   CASE
                       WHEN rc.estado IN ('dnf','ret','accident','collision','mechanical') THEN " . PTS_DNF . "
                       WHEN rc.estado = 'dsq' THEN " . PTS_DSQ . "
                       ELSE (
                           CASE
                               WHEN rc.posicion = 1  THEN 25
                               WHEN rc.posicion = 2  THEN 18
                               WHEN rc.posicion = 3  THEN 15
                               WHEN rc.posicion = 4  THEN 12
                               WHEN rc.posicion = 5  THEN 10
                               WHEN rc.posicion = 6  THEN 8
                               WHEN rc.posicion = 7  THEN 6
                               WHEN rc.posicion = 8  THEN 4
                               WHEN rc.posicion = 9  THEN 2
                               WHEN rc.posicion = 10 THEN 1
                               WHEN rc.posicion BETWEEN 11 AND 14 THEN 2
                               ELSE 0
                           END
                           + " . PTS_TERMINAR . "
                       )
                   END
                   + IF(rc.pole_position,  " . PTS_POLE . ", 0)
                   + IF(rc.vuelta_rapida,  " . PTS_VUELTA_RAP . ", 0)
                   + IF(rc.mejor_sector,   " . PTS_SECTOR . ", 0)
                   + IF(rc.posicion_salida > 0 AND rc.posicion > 0
                        AND rc.estado NOT IN ('dnf','ret','accident','collision','mechanical','dsq')
                        AND rc.posicion_salida > rc.posicion,
                        LEAST((rc.posicion_salida - rc.posicion) * " . PTS_ADELANTO . ", " . PTS_ADELANTO_MAX . "), 0)
                   + IF(rc.posicion_salida > 0 AND rc.posicion > 0
                        AND rc.estado NOT IN ('dnf','ret','accident','collision','mechanical','dsq')
                        AND rc.posicion_salida < rc.posicion,
                        GREATEST((rc.posicion_salida - rc.posicion) * " . abs(PTS_RETROCESO) . " * -1, " . PTS_RETROCESO_MAX . "), 0)
                   + COALESCE(rc.banderas_amarillas, 0) * " . PTS_BANDER_AM . "
                   + COALESCE(rc.banderas_rojas, 0)    * " . PTS_BANDER_ROJ . "
                   + COALESCE(rc.penalizaciones, 0)     * " . PTS_PEN . "
               ), 0) AS puntos_totales
        FROM pilotos p
        LEFT JOIN resultados_carrera rc ON rc.id_piloto = p.id_piloto
        GROUP BY p.id_piloto
    ");
    $resultado = [];
    foreach ($stmt->fetchAll() as $row) {
        $resultado[(int)$row['id_piloto']] = (int)$row['puntos_totales'];
    }
    return $resultado;
}

function getPlantillaEquipo(int $idEquipo): array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT p.id_piloto, p.nombre, p.numero, p.precio, p.imagen_url,
               e.nombre AS escuderia, e.id_escuderia,
               pef.es_capitan, pef.slot
        FROM pilotos_equipo_fantasy pef
        JOIN pilotos p     ON p.id_piloto    = pef.id_piloto
        JOIN escuderias e  ON e.id_escuderia = p.id_escuderia
        WHERE pef.id_equipo = ?
        ORDER BY pef.es_capitan DESC, pef.slot ASC, p.precio DESC
    ");
    $stmt->execute([$idEquipo]);
    $pilotos = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT e.id_escuderia, e.nombre, e.precio_base, e.logo_url
        FROM escuderia_equipo_fantasy ef
        JOIN escuderias e ON e.id_escuderia = ef.id_escuderia
        WHERE ef.id_equipo = ? LIMIT 1
    ");
    $stmt->execute([$idEquipo]);
    $escuderia = $stmt->fetch() ?: null;

    return ['pilotos' => $pilotos, 'escuderia' => $escuderia];
}

function getResumenPuntosEquipo(int $idEquipo): array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT pfc.*, c.nombre AS carrera, c.fecha
        FROM puntos_fantasy_carrera pfc
        JOIN carreras c ON c.id_carrera = pfc.id_carrera
        WHERE pfc.id_equipo = ?
        ORDER BY c.fecha DESC
    ");
    $stmt->execute([$idEquipo]);
    return $stmt->fetchAll();
}

function getProximaCarrera(): ?array
{
    $pdo  = getDB();
    $stmt = $pdo->query("SELECT * FROM carreras WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1");
    return $stmt->fetch() ?: null;
}

function ficharEscuderia(int $idEquipo, int $idEscuderia): array
{
    $pdo = getDB();

    $s = $pdo->prepare("SELECT COUNT(*) FROM escuderia_equipo_fantasy WHERE id_equipo = ?");
    $s->execute([$idEquipo]);
    if ($s->fetchColumn() > 0) return ['ok' => false, 'error' => 'Ya tienes una escudería. Libérala antes de fichar otra.'];

    $s = $pdo->prepare("SELECT id_escuderia, nombre, precio_base FROM escuderias WHERE id_escuderia = ?");
    $s->execute([$idEscuderia]);
    $esc = $s->fetch();
    if (!$esc) return ['ok' => false, 'error' => 'Escudería no encontrada.'];

    $s = $pdo->prepare("SELECT presupuesto FROM equipos_fantasy WHERE id_equipo = ?");
    $s->execute([$idEquipo]);
    $presupuesto = (int)$s->fetchColumn();
    if ($presupuesto < (int)$esc['precio_base']) return ['ok' => false, 'error' => 'Presupuesto insuficiente.'];

    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO escuderia_equipo_fantasy (id_equipo, id_escuderia) VALUES (?, ?)")->execute([$idEquipo, $idEscuderia]);
        $pdo->prepare("UPDATE equipos_fantasy SET presupuesto = presupuesto - ? WHERE id_equipo = ?")->execute([$esc['precio_base'], $idEquipo]);
        $pdo->commit();
        return ['ok' => true, 'error' => '', 'precio' => $esc['precio_base']];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'Error al fichar la escudería.'];
    }
}

function liberarEscuderia(int $idEquipo, int $idEscuderia): array
{
    $pdo = getDB();
    $s = $pdo->prepare("SELECT e.nombre, e.precio_base FROM escuderias e JOIN escuderia_equipo_fantasy ef ON ef.id_escuderia = e.id_escuderia WHERE ef.id_equipo = ? AND ef.id_escuderia = ?");
    $s->execute([$idEquipo, $idEscuderia]);
    $esc = $s->fetch();
    if (!$esc) return ['ok' => false, 'error' => 'Esa escudería no está en tu equipo.'];

    $devolucion = (int)round($esc['precio_base'] * 0.80);

    $pdo->beginTransaction();
    try {
        $del = $pdo->prepare("DELETE FROM escuderia_equipo_fantasy WHERE id_equipo = ? AND id_escuderia = ?");
        $del->execute([$idEquipo, $idEscuderia]);
        if ($del->rowCount() === 0) { $pdo->rollBack(); return ['ok' => false, 'error' => 'No se pudo liberar.']; }
        $pdo->prepare("UPDATE equipos_fantasy SET presupuesto = presupuesto + ? WHERE id_equipo = ?")->execute([$devolucion, $idEquipo]);
        $pdo->commit();
        return ['ok' => true, 'error' => '', 'devolucion' => $devolucion, 'nombre' => $esc['nombre']];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => 'Error al liberar la escudería.'];
    }
}

function getPuntosEscuderiaEquipo(int $idEquipo): int
{
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(puntos), 0) FROM puntos_escuderia_fantasy WHERE id_equipo = ?");
    $stmt->execute([$idEquipo]);
    return (int)$stmt->fetchColumn();
}

function getCambiosRestantes(int $idEquipo): array
{
    $prox = getProximaCarrera();
    if (!$prox) return ['gratis'=>CAMBIOS_GRATIS,'usados'=>0,'restantes'=>CAMBIOS_GRATIS,'extras'=>0];
    $vent = _getCambiosYVentana($idEquipo,(int)$prox['id_carrera']);
    $restantes = max(0, $vent['gratis'] - $vent['usados']);
    $extras    = max(0, $vent['usados'] - $vent['gratis']);
    return ['gratis'=>$vent['gratis'],'usados'=>$vent['usados'],'restantes'=>$restantes,'extras'=>$extras,'coste_extra'=>$vent['coste']];
}

define('MERCADO_PRECIO_MIN',   5_000_000);   
define('MERCADO_PRECIO_MAX',  35_000_000);   
define('MERCADO_FACTOR',         200_000);   
define('MERCADO_MAX_VARIACION',3_000_000);   
define('MERCADO_CARRERAS_MEDIA',       3);   
function actualizarValoresMercado(int $idCarrera): void
{
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT p.id_piloto,
               p.precio_inicial AS precio_inicio_temporada,
               COALESCE(rc.puntos_fantasy, 0) AS puntos_carrera,
               hp_prev.precio   AS precio_base_carrera,
               hp_this.puntos_carrera AS puntos_guardados
        FROM pilotos p
        LEFT JOIN resultados_carrera rc
               ON rc.id_piloto = p.id_piloto AND rc.id_carrera = ?
        -- Precio al que cerró la carrera ANTERIOR (base real para este cálculo)
        LEFT JOIN historial_precios hp_prev
               ON hp_prev.id_piloto = p.id_piloto
              AND hp_prev.id_carrera = (
                    SELECT hp2.id_carrera
                    FROM historial_precios hp2
                    JOIN carreras c2 ON c2.id_carrera = hp2.id_carrera
                    JOIN carreras c_ref ON c_ref.id_carrera = ?
                    WHERE hp2.id_piloto = p.id_piloto
                      AND c2.fecha < c_ref.fecha
                    ORDER BY c2.fecha DESC
                    LIMIT 1
              )
        -- Historial ya guardado para esta carrera (para detectar si los puntos cambiaron)
        LEFT JOIN historial_precios hp_this
               ON hp_this.id_piloto = p.id_piloto AND hp_this.id_carrera = ?
    ");
    $stmt->execute([$idCarrera, $idCarrera, $idCarrera]);
    $pilotos = $stmt->fetchAll();

    foreach ($pilotos as $piloto) {
        $idPiloto   = (int)$piloto['id_piloto'];
        $ptsCarrera = (int)$piloto['puntos_carrera'];

        if ($piloto['puntos_guardados'] !== null &&
            (int)$piloto['puntos_guardados'] === $ptsCarrera) {
            continue;  
        }

        $precioBase = $piloto['precio_base_carrera'] !== null
            ? (int)$piloto['precio_base_carrera']
            : (int)$piloto['precio_inicio_temporada'];

        $stmtMedia = $pdo->prepare("
            SELECT AVG(rc2.puntos_fantasy) AS media
            FROM resultados_carrera rc2
            JOIN carreras c2 ON c2.id_carrera = rc2.id_carrera
            JOIN carreras c_ref ON c_ref.id_carrera = ?
            WHERE rc2.id_piloto = ?
              AND c2.fecha < c_ref.fecha
              AND rc2.puntos_fantasy IS NOT NULL
            ORDER BY c2.fecha DESC
            LIMIT " . MERCADO_CARRERAS_MEDIA
        );
        $stmtMedia->execute([$idCarrera, $idPiloto]);
        $media = (float)($stmtMedia->fetchColumn() ?? 0.0);

        $delta     = $ptsCarrera - $media;
        $variacion = (int)round($delta * MERCADO_FACTOR);
        $variacion = max(-MERCADO_MAX_VARIACION, min(MERCADO_MAX_VARIACION, $variacion));

        $nuevoPrecio = max(
            MERCADO_PRECIO_MIN,
            min(MERCADO_PRECIO_MAX, $precioBase + $variacion)
        );

        $pdo->prepare("
            UPDATE pilotos
            SET precio_anterior = ?, precio = ?
            WHERE id_piloto = ?
        ")->execute([$precioBase, $nuevoPrecio, $idPiloto]);

        $pdo->prepare("
            INSERT INTO historial_precios (id_piloto, id_carrera, precio, variacion, puntos_carrera)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                precio         = VALUES(precio),
                variacion      = VALUES(variacion),
                puntos_carrera = VALUES(puntos_carrera)
        ")->execute([$idPiloto, $idCarrera, $nuevoPrecio, $variacion, $ptsCarrera]);
    }
}


function limpiarPuntosMalAsignados(): int
{
    $pdo     = getDB();
    $borrados = 0;

    $stmt = $pdo->query("
        DELETE pf FROM puntos_fantasy pf
        JOIN pilotos_equipo_fantasy pef
            ON pef.id_equipo = pf.id_equipo AND pef.id_piloto = pf.id_piloto
        JOIN carreras c ON c.id_carrera = pf.id_carrera
        WHERE pef.fecha_inclusion IS NOT NULL
          AND DATE(pef.fecha_inclusion) > DATE(c.fecha)
          AND pf.es_escuderia = 0
    ");
    $borrados += $stmt->rowCount();

    $stmt2 = $pdo->query("
        DELETE pef2 FROM puntos_escuderia_fantasy pef2
        JOIN escuderia_equipo_fantasy eef
            ON eef.id_equipo = pef2.id_equipo AND eef.id_escuderia = pef2.id_escuderia
        JOIN carreras c ON c.id_carrera = pef2.id_carrera
        WHERE eef.fecha_inclusion IS NOT NULL
          AND DATE(eef.fecha_inclusion) > DATE(c.fecha)
    ");
    $borrados += $stmt2->rowCount();

    $stmt3 = $pdo->query("
        DELETE pf FROM puntos_fantasy pf
        JOIN equipos_fantasy ef ON ef.id_equipo = pf.id_equipo
        JOIN carreras c ON c.id_carrera = pf.id_carrera
        WHERE ef.fecha_creacion IS NOT NULL
          AND DATE(ef.fecha_creacion) > DATE(c.fecha)
    ");
    $borrados += $stmt3->rowCount();
    
    $carreras = $pdo->query("SELECT DISTINCT id_carrera FROM puntos_fantasy_carrera")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($carreras as $idC) {
        recalcularResumenJornada((int)$idC);
    }

    return $borrados;
}