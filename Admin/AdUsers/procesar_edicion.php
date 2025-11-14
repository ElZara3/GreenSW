<?php
    session_start();
    include '../../includes/app.php';
    use ProtoClase\Usuario;
    use ProtoClase\CentrosAcopio;
    //pagina protegida
    verificarSesionActiva('Admin');
    
// Procesar solo si es una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar mensajes anteriores de la sesión
    unset($_SESSION['mensajeActualizacionEditUser']);
    unset($_SESSION['errores']);
    unset($_SESSION['MensajeError']);
    $errores = [];
    // Obtener y limpiar los datos del formulario
    //Creamos el objeto con el arreglo post
    $UsuarioAEditar = new Usuario($_POST);
    //Una ves que se creo el objeto 
    if($UsuarioAEditar->ValidarInsercionUsuario()){//Validamos esto nos regresa true o false
        if($UsuarioAEditar->ActualizarDatosUsuario()){
            $_SESSION['mensajeActualizacionEditUser'] = "Datos actualizados correctamente.";
            $_SESSION['tipoMensaje'] = 'exito';
        }
    }else{
        $errores[] = "No se pudo realizar la actualizacion del usuario, reiniciando busqueda...";
        $_SESSION['tipoMensaje'] = 'error';
    }
    // Mensajes de error
    $errores = Usuario::getErrores();
    $_SESSION['errores'] = $errores;
    

    // Guardamos los datos que se pasaron por medio del formulario los que ya teníamos desde el php busqueda
    // que vienen en la sesión y se reutilizan
    if (isset($_POST['usuario_seleccionado'])) {
        $_SESSION['usuario_previo'] = json_decode($_POST['usuario_seleccionado'], true);
    }
    if (isset($_POST['progreso_cubetas'])) {
        $_SESSION['progreso_anterior'] = json_decode($_POST['progreso_cubetas'], true);
    }
    if (isset($_POST['cubetas_restantes'])) {
        $_SESSION['cubetasAnteriores'] = json_decode($_POST['cubetas_restantes'], true);
    }
    if (isset($_POST['meta_alcanzada'])) {
        $_SESSION['metaAnterior'] = json_decode($_POST['meta_alcanzada'], true);
    }    

    /* Obtenemos especificamente la informacion de usuario */
    $UsuarioActual = $_SESSION['usuario_previo'] ?? null;
    $_SESSION['InfodeUsuario'] = $UsuarioActual['InformacionUsuario'] ?? '';

}    

// Redirigir de vuelta a la página anterior
header("Location: busqueda.php");
exit;
