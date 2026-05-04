<?php
require_once __DIR__ . '/../private/funciones_auth.php';
require_once __DIR__ . '/../private/iconos.php';
requiereAutenticacion();

$tituloPagina = 'Ayuda';
include __DIR__ . '/../private/header.php';
?>

<div class="encabezado">
    <p class="panel">Guía del juego</p>
    <h2>CÓMO JUGAR</h2>
</div>

<div class="ayuda-contenedor">

    <!-- ── INTRO ── -->
    <div class="ayuda-hero">
        <div class="ayuda-hero-icono">🏎</div>
        <div class="ayuda-hero-texto">
            <h3>Bienvenido a TGF Pitlane F1 Fantasy</h3>
            <p>Forma tu equipo ideal con pilotos y escuderías de la Fórmula 1, acumula puntos carrera a carrera y demuestra que tienes el ojo de un director deportivo. El que más puntos acumule al final de la temporada, gana.</p>
        </div>
    </div>

    <!-- ── SECCIONES ── -->
    <div class="ayuda-grid">

        <!-- 1. El equipo -->
        <div class="ayuda-card">
            <div class="ayuda-card-header">
                <span class="ayuda-num">01</span>
                <h4>Forma tu equipo</h4>
            </div>
            <div class="ayuda-card-body">
                <p>Tu equipo se compone de <strong>5 pilotos</strong> y <strong>1 escudería</strong>. Tienes un presupuesto de <strong>40 millones de euros</strong> para ficharlo todo.</p>
                <ul>
                    <li>Máximo <strong>2 pilotos</strong> de la misma escudería.</li>
                    <li>Uno de tus pilotos puede ser nombrado <strong>Capitán</strong>: sus puntos se <em>multiplican por 2</em>.</li>
                    <li>La escudería suma puntos en función de sus resultados conjuntos.</li>
                </ul>
            </div>
        </div>

        <!-- 2. Presupuesto y mercado -->
        <div class="ayuda-card">
            <div class="ayuda-card-header">
                <span class="ayuda-num">02</span>
                <h4>Mercado y fichajes</h4>
            </div>
            <div class="ayuda-card-body">
                <p>Accede al <strong>Mercado</strong> para fichar y liberar pilotos. Cada piloto tiene un precio según su rendimiento esperado.</p>
                <ul>
                    <li>Tienes <strong>3 cambios gratuitos</strong> por ventana de mercado (entre carreras).</li>
                    <li>Cambios extra cuestan <strong>−4 puntos</strong> cada uno.</li>
                    <li>El presupuesto restante se actualiza automáticamente al fichar o liberar.</li>
                </ul>
            </div>
        </div>

        <!-- 3. Cómo se puntúa: pilotos -->
        <div class="ayuda-card ayuda-card--full">
            <div class="ayuda-card-header">
                <span class="ayuda-num">03</span>
                <h4>Sistema de puntos — Pilotos</h4>
            </div>
            <div class="ayuda-card-body">
                <div class="ayuda-pts-grid">

                    <div class="ayuda-pts-grupo">
                        <div class="ayuda-pts-titulo positivo">Puntos positivos</div>
                        <div class="ayuda-pts-tabla">
                            <div class="ayuda-pts-fila"><span>1.º puesto</span><strong class="pos">+25</strong></div>
                            <div class="ayuda-pts-fila"><span>2.º puesto</span><strong class="pos">+18</strong></div>
                            <div class="ayuda-pts-fila"><span>3.º puesto</span><strong class="pos">+15</strong></div>
                            <div class="ayuda-pts-fila"><span>4.º puesto</span><strong class="pos">+12</strong></div>
                            <div class="ayuda-pts-fila"><span>5.º puesto</span><strong class="pos">+10</strong></div>
                            <div class="ayuda-pts-fila"><span>6.º puesto</span><strong class="pos">+8</strong></div>
                            <div class="ayuda-pts-fila"><span>7.º puesto</span><strong class="pos">+6</strong></div>
                            <div class="ayuda-pts-fila"><span>8.º puesto</span><strong class="pos">+4</strong></div>
                            <div class="ayuda-pts-fila"><span>9.º puesto</span><strong class="pos">+2</strong></div>
                            <div class="ayuda-pts-fila"><span>10.º puesto</span><strong class="pos">+1</strong></div>
                            <div class="ayuda-pts-fila"><span>P11–P14</span><strong class="pos">+2</strong></div>
                            <div class="ayuda-pts-fila"><span>Terminar carrera</span><strong class="pos">+2</strong></div>
                            <div class="ayuda-pts-fila"><span>Pole Position</span><strong class="pos">+10</strong></div>
                            <div class="ayuda-pts-fila"><span>Clasificar Q3 (sin pole)</span><strong class="pos">+5</strong></div>
                            <div class="ayuda-pts-fila"><span>Clasificar Q2</span><strong class="pos">+2</strong></div>
                            <div class="ayuda-pts-fila"><span>Vuelta rápida</span><strong class="pos">+10</strong></div>
                            <div class="ayuda-pts-fila"><span>Mejor sector</span><strong class="pos">+5</strong></div>
                            <div class="ayuda-pts-fila"><span>Posición ganada (×3, máx.)</span><strong class="pos">+15</strong></div>
                            <div class="ayuda-pts-fila"><span>Bonus ≥5 posiciones ganadas</span><strong class="pos">+10</strong></div>
                            <div class="ayuda-pts-fila"><span>Bonus 3–4 posiciones ganadas</span><strong class="pos">+5</strong></div>
                        </div>
                    </div>

                    <div class="ayuda-pts-grupo">
                        <div class="ayuda-pts-titulo negativo">Puntos negativos</div>
                        <div class="ayuda-pts-tabla">
                            <div class="ayuda-pts-fila"><span>Abandono / DNF</span><strong class="neg">−10</strong></div>
                            <div class="ayuda-pts-fila"><span>Descalificación / DSQ</span><strong class="neg">−20</strong></div>
                            <div class="ayuda-pts-fila"><span>Posición perdida (×2, máx.)</span><strong class="neg">−10</strong></div>
                            <div class="ayuda-pts-fila"><span>Bandera amarilla causada</span><strong class="neg">−5</strong></div>
                            <div class="ayuda-pts-fila"><span>Bandera roja causada</span><strong class="neg">−15</strong></div>
                            <div class="ayuda-pts-fila"><span>Penalización en carrera</span><strong class="neg">−5</strong></div>
                        </div>
                        <div class="ayuda-pts-titulo positivo" style="margin-top:20px;">Capitán</div>
                        <div class="ayuda-pts-tabla">
                            <div class="ayuda-pts-fila"><span>Puntos del capitán</span><strong class="pos">×2</strong></div>
                        </div>
                        <div class="ayuda-nota">El capitán se designa desde <em>Mi Equipo</em>. Elige al piloto que creas que tendrá el mejor fin de semana.</div>
                    </div>

                </div>
            </div>
        </div>

        <!-- 4. Escudería -->
        <div class="ayuda-card">
            <div class="ayuda-card-header">
                <span class="ayuda-num">04</span>
                <h4>Puntos de escudería</h4>
            </div>
            <div class="ayuda-card-body">
                <p>Tu escudería puntúa en función de los puestos que finalicen sus dos pilotos oficiales:</p>
                <ul>
                    <li>Suma de puntos oficiales de ambos pilotos <strong>÷ 5</strong>.</li>
                    <li>Si ambos pilotos puntúan: <strong>+5 bonus</strong>.</li>
                    <li>Si ambos pilotos terminan en podio: <strong>+3 bonus</strong>.</li>
                    <li>Si ambos están en el Top 5: <strong>+8 bonus doble</strong>.</li>
                </ul>
            </div>
        </div>

        <!-- 5. Estrategia -->
        <div class="ayuda-card">
            <div class="ayuda-card-header">
                <span class="ayuda-num">05</span>
                <h4>Estrategia y consejos</h4>
            </div>
            <div class="ayuda-card-body">
                <ul>
                    <li>Guarda cambios para circuitos donde tu piloto tiene ventaja histórica.</li>
                    <li>El capitán puede ser decisivo: elige en función del trazado y el ritmo de clasificación.</li>
                    <li>Pilotos baratos con buen ritmo de carrera (adelantamientos) dan muchos puntos.</li>
                    <li>Atención a las banderas rojas y penalizaciones: pueden arruinar una gran actuación.</li>
                    <li>La escudería es una inversión a largo plazo: elige la que más puntúe en conjunto.</li>
                </ul>
            </div>
        </div>

        <!-- 6. Páginas de la app -->
        <div class="ayuda-card ayuda-card--full">
            <div class="ayuda-card-header">
                <span class="ayuda-num">06</span>
                <h4>Qué hay en cada sección</h4>
            </div>
            <div class="ayuda-card-body">
                <div class="ayuda-secciones">
                    <div class="ayuda-seccion-item">
                        <div class="ayuda-seccion-icono"><?= icono('inicio', 'ayuda-sec-icono', 28) ?></div>
                        <strong>Inicio</strong>
                        <p>Panel de control con tus puntos totales, posición en la liga y cuenta atrás al próximo GP.</p>
                    </div>
                    <div class="ayuda-seccion-item">
                        <div class="ayuda-seccion-icono"><?= icono('coche', 'ayuda-sec-icono', 28) ?></div>
                        <strong>Mi Equipo</strong>
                        <p>Gestiona tu plantilla, designa al capitán y consulta el desglose de puntos por piloto.</p>
                    </div>
                    <div class="ayuda-seccion-item">
                        <div class="ayuda-seccion-icono"><?= icono('mercado', 'ayuda-sec-icono', 28) ?></div>
                        <strong>Mercado</strong>
                        <p>Ficha y libera pilotos. Filtra por escudería, precio o puntos. Controla tu presupuesto.</p>
                    </div>
                    <div class="ayuda-seccion-item">
                        <div class="ayuda-seccion-icono"><?= icono('trofeo', 'ayuda-sec-icono', 28) ?></div>
                        <strong>Clasificación</strong>
                        <p>Tabla general de la liga con todos los equipos ordenados por puntos acumulados.</p>
                    </div>
                    <div class="ayuda-seccion-item">
                        <div class="ayuda-seccion-icono"><?= icono('resultados', 'ayuda-sec-icono', 28) ?></div>
                        <strong>Resultados</strong>
                        <p>Resultados de cada Gran Premio con el desglose de puntos fantasy por piloto.</p>
                    </div>
                    <div class="ayuda-seccion-item">
                        <div class="ayuda-seccion-icono"><?= icono('ayuda', 'ayuda-sec-icono', 28) ?></div>
                        <strong>Ayuda</strong>
                        <p>Esta página. Aquí está todo lo que necesitas saber para jugar.</p>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /ayuda-grid -->

</div><!-- /ayuda-contenedor -->

<?php include __DIR__ . '/../private/footer.php'; ?>
