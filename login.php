<?php
require './includes/app.php';

use ProtoClase\Usuario;
session_start();
redirigirSiEstaAutenticado();

$errores = [];
$DatoIngresadoUsuario = '';

// Verificar si hay errores en la sesión (después de redirect)
if (isset($_SESSION['errores_login'])) {
    $errores = $_SESSION['errores_login'];
    unset($_SESSION['errores_login']); // Limpiar errores después de mostrarlos
}

// Verificar si hay dato del usuario en la sesión
if (isset($_SESSION['dato_usuario_temporal'])) {
    $DatoIngresadoUsuario = $_SESSION['dato_usuario_temporal'];
    unset($_SESSION['dato_usuario_temporal']); // Limpiar después de usar
}

// Mostrar mensaje de sesión expirada
if (isset($_GET['sesion_expirada']) && $_GET['sesion_expirada'] == 1) {
    $errores[] = "Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente.";
}

// Procesar el inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_phone = $_POST['email_or_phone'] ?? '';
    $password = $_POST['password'] ?? '';

    if (Usuario::verificarExistenciaUsuario($email_or_phone, $password)) {
        // Login exitoso - redirect directo
        //Reestablecemos el id de la sesion
        session_regenerate_id(true);
        if ($_SESSION['rol'] === 'Admin' || $_SESSION['rol'] === 'SuperAdmin') {
            header('Location: /Admin/index.php');
        } else {
            header('Location: /Usuario/index.php');
        }
        exit;
    } else {
        // Login fallido - guardar errores y datos en sesión, luego redirect
        $_SESSION['errores_login'] = Usuario::getErrores();
        $_SESSION['dato_usuario_temporal'] = $email_or_phone;
        
        // Redirect a la misma página pero con GET
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

incluirTemplate('header');
?>

<section class="log">
    <!-- Mostrar errores -->
    <?php foreach ($errores as $error): ?>
        <div class="alerta error">
            <?php echo "<p class='errorloging'>" . s($error) . "</p>"; ?>
        </div>
    <?php endforeach; ?>

    <form class="login-form" method="POST" action="">
        <h2>Iniciar Sesión</h2>
        <div class="datos">
            <div class="form-group">
                <label for="email-or-phone">Correo o Número de Teléfono</label>
                <input
                    type="text"
                    id="email-or-phone"
                    name="email_or_phone"
                    placeholder="Ingrese su correo o número"
                    required
                    value="<?php echo s($DatoIngresadoUsuario); ?>">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="Contenedor_contrasena">
                    <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingrese su contraseña"
                    required>
                    
                    <button type="button" id="IngresarContraseñaSesion" class="toggle-password">👁️</button>
                    <div class="contenedorWarning">
                        <img class="iconoWarning" src="vectores/IniciarSesion/warning-circle-svg.svg" alt="IconWarning">
                        <span class="tooltipWarning">Si olvidaste tu contraseña acude a uno de nuestros centros de acopio para reestablecerla</span>
                    </div>
                </div>
            </div>
            <button type="submit" class="BotonInicioS">Iniciar Sesión</button>

            <div class="registro-section">
                <span class="pregunta-inscripcion">¿No estás inscrito?</span>
                <a href="https://wa.me/5564702484?text=Holaa!%20Quiero%20formar%20parte%20de%20la%20comunidad" class="boton-inscribirme" target="_blank">Inscribirme</a>
            </div>
        </div>
    </form>
</section>

<img src="vectores/IniciarSesion/IMAGEN INICIAR SESIÓN.svg" alt="iconoColection" id="imagColectionCom">

<script src="./build_previo/js/app.js"></script>

<?php
incluirTemplate('footer');
?>