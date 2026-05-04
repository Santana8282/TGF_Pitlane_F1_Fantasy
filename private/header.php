<?php
if (!isset($tituloPagina)) $tituloPagina = 'F1 Fantasy';
require_once __DIR__ . '/iconos.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?> — F1 Fantasy</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="contenedor">
    <aside class="barra-lateral">
        <div class="logo">
            <h1>F1 Fantasy</h1>
            <p>Liga Fantasy</p>
        </div>
        <nav class="menu">
            <a href="../public/index.php"         class="<?= basename($_SERVER['PHP_SELF']) === 'index.php'         ? 'activo' : '' ?>">
                <?= icono('inicio', 'menu-icono') ?> Inicio
            </a>
            <a href="../public/equipo.php"         class="<?= basename($_SERVER['PHP_SELF']) === 'equipo.php'        ? 'activo' : '' ?>">
                <?= icono('equipo', 'menu-icono') ?> Mi Equipo
            </a>
            <a href="../public/mercado.php"        class="<?= basename($_SERVER['PHP_SELF']) === 'mercado.php'       ? 'activo' : '' ?>">
                <?= icono('mercado', 'menu-icono') ?> Mercado
            </a>
            <a href="../public/clasificacion.php"  class="<?= basename($_SERVER['PHP_SELF']) === 'clasificacion.php' ? 'activo' : '' ?>">
                <?= icono('clasificacion', 'menu-icono') ?> Clasificación
            </a>
            <a href="../public/resultados.php"     class="<?= basename($_SERVER['PHP_SELF']) === 'resultados.php'    ? 'activo' : '' ?>">
                <?= icono('resultados', 'menu-icono') ?> Resultados
            </a>
            <a href="../public/ligas.php"          class="<?= basename($_SERVER['PHP_SELF']) === 'ligas.php'         ? 'activo' : '' ?>">
                <?= icono('trofeo', 'menu-icono') ?> Ligas
            </a>

            <a href="../public/ayuda.php"          class="<?= basename($_SERVER['PHP_SELF']) === 'ayuda.php'         ? 'activo' : '' ?>">
                <?= icono('ayuda', 'menu-icono') ?> Ayuda
            </a>
        </nav>
        <div class="usuario">
            <p class="nombre-usuario"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></p>
            <span>Piloto Pro</span><br>
            <a href="../public/logout.php" class="btn-logout">
                <?= icono('logout', 'menu-icono', 14) ?> Cerrar sesión
            </a>
        </div>

    </aside>
    <main class="principal">
