<?php
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');

$num   = preg_replace('/[^0-9]/', '', $_GET['num']  ?? '00');
$ini   = strtoupper(substr(preg_replace('/[^A-Z]/i', '', $_GET['ini'] ?? 'XX'), 0, 3));
$color = preg_replace('/[^0-9A-Fa-f]/', '', $_GET['color'] ?? 'e8001d');
if (strlen($color) !== 6) $color = 'e8001d';

$r = hexdec(substr($color, 0, 2));
$g = hexdec(substr($color, 2, 2));
$b = hexdec(substr($color, 4, 2));
$lum = ($r * 0.299 + $g * 0.587 + $b * 0.114) / 255;
$textColor = $lum > 0.55 ? '111111' : 'ffffff';

echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 400" width="300" height="400">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#141414"/>
      <stop offset="100%" style="stop-color:#0a0a0a"/>
    </linearGradient>
    <radialGradient id="glow" cx="50%" cy="40%" r="55%">
      <stop offset="0%" style="stop-color:#$color;stop-opacity:0.25"/>
      <stop offset="100%" style="stop-color:#$color;stop-opacity:0"/>
    </radialGradient>
  </defs>
  <!-- Fondo -->
  <rect width="300" height="400" fill="url(#bg)"/>
  <!-- Glow -->
  <ellipse cx="150" cy="160" rx="180" ry="160" fill="url(#glow)"/>
  <!-- Franja color escudería abajo -->
  <rect x="0" y="370" width="300" height="30" fill="#$color" opacity="0.9"/>
  <!-- Icono coche F1 (centrado, escalado a ~120px) -->
  <g transform="translate(90, 140) scale(5)">
    <path d="M19 17H5a2 2 0 0 1-2-2V9l3-5h12l3 5v6a2 2 0 0 1-2 2z"
          fill="none" stroke="#$color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <circle cx="7.5" cy="17" r="2"
            fill="none" stroke="#$color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <circle cx="16.5" cy="17" r="2"
            fill="none" stroke="#$color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M3 9h18"
          fill="none" stroke="#$color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </g>
  <!-- Iniciales -->
  <text x="150" y="330"
        font-family="'Arial Black', Arial, sans-serif"
        font-size="28" font-weight="900" letter-spacing="10"
        text-anchor="middle" dominant-baseline="middle"
        fill="#ffffff" opacity="0.5">{$ini}</text>
  <!-- Línea decorativa -->
  <rect x="60" y="305" width="180" height="2" fill="#$color" opacity="0.4" rx="1"/>
</svg>
SVG;
