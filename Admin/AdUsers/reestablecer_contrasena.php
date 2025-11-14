<?php
    // Iniciar sesión si no está iniciada
    session_start();
    include '../../includes/app.php';
    use ProtoClase\Usuario;
    //pagina protegida
    verificarSesionActiva('Admin');

// Procesar solo si es una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //vamos a crear la clase  usuario solo con su Id solo vamos a ocupar eso para 
    //Buscarlo en la base
    $UsuarioAActualizar = new Usuario($_POST);

    //Si lo encontramos procedemos a hacer su cambio
    if($UsuarioAActualizar->ReestablecerContraseña()){
        $nueva_contrasena = $UsuarioAActualizar->ExtraerContraseñaGeneradaAutom();
        $_SESSION['mensajeActualizacion'] = "<h3 class='mensajeDeReestablecimientodeDatos' >Contraseña reestablecida correctamente. </br>La nueva contraseña es: <p class='color_azul'>" . $nueva_contrasena."</p></h3>";
        
    }
    //En caso de que no haya ningun error no se guarda nada en errores 
    //solo un arreglo vacio
    $errores = Usuario::getErrores();
    $_SESSION['errores'] = $errores;

    //Valores cargados previamente
    //que vienen en la sesion y se reutilizan
    $_SESSION['usuario_previo'] = json_decode($_POST['usuario_seleccionado'], true);
    $_SESSION['progreso_anterior'] = json_decode($_POST['progreso_cubetas'], true);
    $_SESSION['cubetasAnteriores'] = json_decode($_POST['cubetas_restantes'], true);
    $_SESSION['metaAnterior'] = json_decode($_POST['meta_alcanzada'], true);

    /* Obtenemos la info del usuario por la variable */
    $UsuarioActual = $_SESSION['usuario_previo'];
    $_SESSION['InfodeUsuario'] = $UsuarioActual['InformacionUsuario'];
}
header ('Location: busqueda.php');
exit;
