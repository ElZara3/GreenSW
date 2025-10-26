<?php
  
session_start();
include '../../includes/app.php';
use ProtoClase\Usuario; // O la clase que uses para usuarios
//pagina protegida
verificarSesionActiva('Admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {
    $idUsuario = intval($_POST['id_usuario']);
    $informacionUsuario = $_POST['informacion_usuario'] ?? '';
    
    // Sanitizar la información del usuario
    $informacionUsuario = htmlspecialchars($informacionUsuario, ENT_QUOTES, 'UTF-8');
    
    // Actualizar la información del usuario en la base de datos
    // Opción 1: Si usas una clase Usuario con método Actualizar
    $usuarioObj = new Usuario(['InformacionUsuario' => $informacionUsuario]);
    $resultado = $usuarioObj->Actualizar("Id", $idUsuario, "=", null, null, null, "InformacionUsuario");
    
    // O Opción 2: Query directo si no tienes método en la clase
    // $query = "UPDATE usuarios SET InformacionUsuario = ? WHERE IdUsuario = ?";
    // $stmt = mysqli_prepare($db, $query);
    // mysqli_stmt_bind_param($stmt, 'si', $informacionUsuario, $idUsuario);
    // $resultado = mysqli_stmt_execute($stmt);
    
    // Recuperar datos del formulario ocultos y guardarlos en sesión
    if (isset($_POST['usuario_previo_serializado'], $_POST['progreso_anterior'], 
              $_POST['cubetas_anteriores'], $_POST['meta_anterior'])
    ) {
        $_SESSION['usuario_previo'] = unserialize($_POST['usuario_previo_serializado']);
        $_SESSION['progreso_anterior'] = $_POST['progreso_anterior'];
        $_SESSION['cubetasAnteriores'] = $_POST['cubetas_anteriores'];
        $_SESSION['metaAnterior'] = $_POST['meta_anterior'];
        $_SESSION['InfodeUsuario'] = $informacionUsuario;
        
        $_SESSION['mensajeActualizacion'] = '<h3 class="mensajeInfoUsuarioActualizado">¡Información del usuario actualizada correctamente!</h3>';
    } else {
        $_SESSION['mensajeActualizacion'] = 'Faltan datos ocultos para conservar estado.';
    }
    
    header("Location: busqueda.php");
    exit;
    
} else {
    $_SESSION['mensajeActualizacion'] = 'Datos incompletos para la actualización.';
    header("Location: busqueda.php");
    exit;
}