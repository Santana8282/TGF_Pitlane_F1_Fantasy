<?php
require_once 'private/funciones_auth.php';

if (estaLogueado()) {
    header('Location: public/index.php');
    exit;
} else {
    header('Location: public/login.php');
    exit;
}