<?php
require_once __DIR__ . '/../private/funciones_auth.php';

if (estaLogueado()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = iniciarSesion($_POST['correo'] ?? '', $_POST['contrasena'] ?? '');
    if (isset($r['exito'])) { header('Location: index.php'); exit; }
    $error = $r['error'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — F1 Fantasy</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
<div class="fondo" aria-hidden="true"><div class="fondo-glow"></div></div>
<div class="tarjeta-auth">
    <span class="numero-decorativo" aria-hidden="true">01</span>
    <div class="logo-auth"><h1>F1 FANTASY</h1><p>Liga Fantasy</p></div>
    <h2 class="titulo-auth">Iniciar Sesión</h2>
    <p class="subtitulo-auth">Accede a tu equipo fantasy</p>

    <?php if ($error): ?>
        <div class="mensaje-error" role="alert">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
        <div class="campo">
            <label for="correo">Correo electrónico *</label>
            <input type="email" id="correo" name="correo"
                   placeholder="piloto@f1fantasy.com"
                   value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                   required autocomplete="email">
        </div>
        <div class="campo">
            <label for="contrasena">Contraseña *</label>
            <input type="password" id="contrasena" name="contrasena"
                   placeholder="••••••••" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn-primario">Entrar a la Liga →</button>
    </form>

    <div class="separador"><span>¿Eres nuevo aquí?</span></div>
    <p class="enlace-alternativo">¿No tienes cuenta? <a href="register.php">Regístrate gratis</a></p>
</div>
</body>
</html>
