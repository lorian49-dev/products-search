<?php
session_start();

// --- GUARDAR LA URL ACTUAL ANTES DE CERRAR SESIÓN ---
$redirect_url = $_SERVER['HTTP_REFERER'] ?? '../home.php';

// Si no hay referer (raro, pero puede pasar), usar la URL actual
if (empty($redirect_url) || strpos($redirect_url, 'logout.php') !== false) {
    // Opción 1: Usar la URL actual (sin parámetros de logout)
    $redirect_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                    "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    // Limpiar parámetros de logout si los hubiera
    $redirect_url = strtok($redirect_url, '?');
    
    // Si después de limpiar queda solo logout.php, ir a home
    if (strpos($redirect_url, 'logout.php') !== false) {
        $redirect_url = '../home.php';
    }
}

// --- CERRAR SESIÓN ---
session_unset();
session_destroy();

// Borrar cookies si las usas
if (isset($_COOKIE['usuario_id'])) {
    setcookie("usuario_id", "", time() - 3600, "/");
}
if (isset($_COOKIE['admin_logueado'])) {
    setcookie("admin_logueado", "", time() - 3600, "/");
}
// Puedes borrar más cookies aquí si tienes

// --- REDIRIGIR A LA MISMA PÁGINA DONDE ESTABA ---
header("Location: $redirect_url");
exit();
?>