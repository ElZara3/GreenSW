<?php

use ProtoClase\Usuario;

define('TEMPLATES_URL',__DIR__. '/templates');

function incluirTemplate(string $nombre, bool $inicio = false) {
    include TEMPLATES_URL . "/$nombre.php";
}

/**
 * Función para redireccionar desde login.php si ya está autenticado
 */
function redirigirSiEstaAutenticado() {
    if (isset($_SESSION['login']) && $_SESSION['login']) {
        TiempoConcluido();

         // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
        
        // Redirigir según el rol
        $url = ($_SESSION['rol'] === 'User') ? '/Usuario/index.php' : '/Admin/index.php';
        header("Location: $url");
        exit;
    }
}

/**
 * Función principal para verificar sesión activa sin redireccionar
 * Para paginas protegidas
 */
function verificarSesionActiva($RolActual= null) {
    TiempoConcluido();

    // Verificar sesión y usuario
    if (!isset($_SESSION['login']) || !$_SESSION['login'] || !isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
        cerrarSesionYRedirigir();
    }

    // Establecer rol si no existe
    if (!isset($_SESSION['rol']) || empty($_SESSION['rol'])) {
        $rol = Usuario::ExtraerRolUser();
        if (!$rol) {
            cerrarSesionYRedirigir();
        }
        $_SESSION['rol'] = $rol;
    }

    if (!empty($RolActual)){//Si se establece un rol especifico
        if($_SESSION['rol'] ==='Admin' || $_SESSION['rol'] === 'SuperAdmin'){
            ($RolActual === 'Admin') ?: cerrarSesionYRedirigir();
            }
    
        if($_SESSION['rol'] ==='User'){
            ($RolActual === 'User') ?: cerrarSesionYRedirigir();
            }

    }
}

/**
 * Función auxiliar para cerrar sesión y redirigir
 */
function cerrarSesionYRedirigir() {
    session_unset();
    session_destroy();
    header('Location: /login.php');
    exit;
}

/**
 * Verificar tiempo de expiración de sesión
 */
function TiempoConcluido() {
    $tiempo_expiracion = 1800; // 30 minutos

    if (isset($_SESSION['login']) && $_SESSION['login']) {
        if (isset($_SESSION['ultimo_acceso'])) {
            $tiempo_inactivo = time() - $_SESSION['ultimo_acceso'];

            if ($tiempo_inactivo >= $tiempo_expiracion) {
                session_unset();
                session_destroy();
                header('Location: /login.php?sesion_expirada=1');
                exit;
            }
        } else {
        }
    }
}

function s($html){
    return htmlspecialchars($html, ENT_QUOTES, 'UTF-8');
}