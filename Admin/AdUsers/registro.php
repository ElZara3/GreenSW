<?php
// Iniciar sesión
session_start();
//incluir todas las funciones
require_once '../../includes/app.php';
// Verificar si el usuario tiene rol de admin
verificarSesionActiva('Admin');
//Incluir clases
use ProtoClase\Usuario;
use ProtoClase\CentrosAcopio;

// Recuperar el centro de acopio seleccionado de la URL o de la sesión
$CentroSelectedId = isset($_GET['centro']) ? (int)$_GET['centro'] : (isset($_SESSION['centro_actual']) ? $_SESSION['centro_actual'] : null);
// Si viene de seleccionar un centro, guardarlo en sesión
if (isset($_GET['centro'])) {
    $_SESSION['centro_actual'] = s($CentroSelectedId);
}
// Consultar centros de acopio
$TodosLosCentros = CentrosAcopio::ExtraerAtributosEspecificos();

// Inicializar variables

$UsuarioAintroducir = new Usuario;//Array

$mensaje = '';
$errores = Usuario::getErrores();
$exito = false;

// Calcular la fecha máxima permitida para el campo de fecha de nacimiento
$fecha_maxima = date('Y-m-d', strtotime('-18 years'));   // Persona más joven: 18 años

// Procesar el formulario al enviar los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $UsuarioAintroducir = new Usuario($_POST);//Le pasamos el formulario para despues validarlo   

    if($UsuarioAintroducir->ValidarInsercionUsuario()){//es decir que nos regreso un true
        
        $exito = true;
        $mensaje = "Usuario registrado correctamente";
        //Contraseña que se va a introducir en la base solo el texto para mostrarlo
        // Generar contraseña a partir del nombre original (en minúsculas) y teléfono
        $nombreMin = strtolower(substr($UsuarioAintroducir->Nombre, 0, 3));
        $telefonoUltimos3 = substr($UsuarioAintroducir->Telefono, -3);
        $contrasena = $nombreMin . $telefonoUltimos3;

        $UsuarioAintroducir->InsertarUsuario();

    }

    //Al final se puede poner asi, si no hay  ningun error no se llena este arreglo se queda vacio
    $errores = Usuario::getErrores();
    $exitos = Usuario::getExitos();

}

// Incluir el header
incluirTemplate('header');
?>

<div class="contenedor-seccion">
    <div class="registro-admin">
        <h1 class="registro-admin__titulo">Registro de Usuario</h1>
        
        <?php if (!empty($errores)): ?>
            <div class="alerta error">
                <?php foreach ($errores as $error): ?>
                    <p><?php echo s($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
            <!-- CONTENIDO CUANDO EL REGISTRO ES EXITOSO -->
            <div class="registro-exitoso">
                <h2 class="registro-exitoso__titulo">Registro de Usuario</h2>

                <div class="registro-exitoso__contenedor">
                    <!-- Contenido central -->
                    <div class="registro-exitoso__contenido-central">
                        <h3 class="registro-exitoso__mensaje">¡Usuario registrado correctamente!</h3>

                        <div class="registro-exitoso__recuadro-contrasena">
                            <p>La nueva contraseña es: <strong><?php echo s($contrasena); ?></strong></p>
                        </div>
                    </div>
                </div>

                <div class="registro-exitoso__botones">
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="boton-confirmar-registro boton-confirmar-registro-verde">
                        <i class="fa fa-user-plus"></i> Registro Nuevo
                    </a>

                    <a href="/Admin/index.php" class="boton-confirmar-registro boton-confirmar-registro-azul">
                        <i class="fa fa-arrow-left"></i> Volver a Centros de Acopio
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- CONTENIDO DEL FORMULARIO DE REGISTRO -->
            <div class="registro-admin__navegacion">
                <a href="/Admin/index.php" class="boton-volver">
                    <i class="fa fa-arrow-left"></i> Volver a Centros de Acopio
                </a>
            </div>
            
            <div class="registro-admin__contenedor">
                <div class="registro-admin__grid">
                    <!-- Columna izquierda: Formulario -->
                    <div class="registro-admin__columna-formulario">
                        <form method="POST" class="formulario-registro" id="formulario-registro">
                            <div class="formulario-registro__campo">
                                <label for="nombre">Nombre:</label>
                                <input 
                                    type="text" 
                                    id="nombre" 
                                    name="Nombre" 
                                    placeholder="Nombre del usuario" 
                                    value="<?php echo s($UsuarioAintroducir->Nombre); ?>" required
                                >
                            </div>
                            
                            <div class="formulario-registro__campo">
                                <label for="apellido_paterno">Apellido Paterno:</label>
                                <input 
                                    type="text" 
                                    id="apellido_paterno" 
                                    name="ApPat" 
                                    placeholder="Apellido Paterno" 
                                    value="<?php echo s(($UsuarioAintroducir->ApPat) ?? ''); ?>"
                                >
                            </div>
                            
                            <div class="formulario-registro__campo">
                                <label for="apellido_materno">Apellido Materno:</label>
                                <input 
                                    type="text" 
                                    id="apellido_materno" 
                                    name="ApMat" 
                                    placeholder="Apellido Materno" 
                                    value="<?php echo s(($UsuarioAintroducir->ApMat) ?? ''); ?>"
                                >
                            </div>
                            
                            <div class="formulario-registro__campo">
                                <label for="fecha_nacimiento">Fecha de Nacimiento: (opcional)</label>
                                <input 
                                    type="date"
                                    id="fecha_nacimiento" 
                                    name="FNacimiento" 
                                    max="<?php echo $fecha_maxima; ?>" 
                                    value="<?php echo s(( $UsuarioAintroducir->FNacimiento) ?? ''); ?>"
                                >
                            </div>
                            
                            <div class="formulario-registro__campo">
                                <label for="telefono">Número de Teléfono:</label>
                                <input 
                                    type="tel" 
                                    id="telefono" 
                                    name="Telefono" 
                                    placeholder="Mínimo 10 dígitos" 
                                    value="<?php echo s($UsuarioAintroducir->Telefono); ?>" required
                                >
                            </div>
                            
                            <div class="formulario-registro__campo">
                                <label for="direccion">Direccion: (opcional)</label>
                                <input 
                                    type="text" 
                                    id="direccion" 
                                    name="Direccion" 
                                    placeholder="Domicilio" 
                                    value="<?php echo s(($UsuarioAintroducir->Direccion) ?? ''); ?>"
                                >
                            </div>
                            
                            <div class="formulario-registro__campo">
                                <label for="correo">Correo Electrónico: (opcional)</label>
                                <input 
                                    type="email" 
                                    id="correo" 
                                    name="Correo" 
                                    placeholder="correo@ejemplo.com" 
                                    value="<?php echo s(($UsuarioAintroducir->Correo) ?? ''); ?>"
                                >
                            </div>
                            
                            <div class="formulario-registro__campo">
                                <label for="centro_acopios">Centro de Acopio:</label>
                                <select id="centro_acopios" name="IdCentroAcopio" required>
                                    <option value="">-- Seleccione --</option>
                                    <?php
                                    foreach($TodosLosCentros as $centro): ?>
                                    <option value="<?php echo $centro['Id']; ?>" 
                                            <?php echo (int)$CentroSelectedId === (int)$centro['Id'] ? 'selected' : ''; ?>
                                    >
                                        <?php echo s($centro['Nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Columna derecha: Imagen y botones -->
                    <div class="registro-admin__columna-imagen">
                        <div class="registro-admin__imagen-contenedor">
                            <img src="/vectores/Admin/perfil/REGISTRO USUARIO IMAGEN.svg" alt="Registro de usuario" class="registro-admin__imagen">
                        </div>
                        
                        <div class="registro-admin__botones">
                            <button type="submit" form="formulario-registro" class="boton-confirmar-registro boton-confirmar-registro-verde">
                                <i class="fa fa-user-plus"></i> Registrar Usuario
                            </button>
                            
                            <a href="/login.php" class="boton-confirmar-registro boton-confirmar-registro-rojo">
                                <i class="fa fa-times"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación en tiempo real para el teléfono
        const telefonoInput = document.getElementById('telefono');
        
        if (telefonoInput) {
            telefonoInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
                
                if (this.value.length > 13) {
                    this.value = this.value.slice(0, 13);
                }
                
                if (this.value.length >= 10 && this.value.length <= 13) {
                    this.style.borderColor = '#28a745';
                    this.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
                } else if (this.value.length > 0) {
                    this.style.borderColor = '#dc3545';
                    this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                } else {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                }
            });
            
        }
        
        // Validación en tiempo real para los nombres y apellidos
        const camposTexto = [
            document.getElementById('nombre'),
            document.getElementById('apellido_paterno'),
            document.getElementById('apellido_materno')
        ];
        
        camposTexto.forEach(campo => {
            if (campo) {
                campo.addEventListener('input', function() {
                    this.value = this.value
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                        .join(' ');
                });
            }
        });
    });
</script>

<script src="/build_previo/js/app.js"></script>
<?php

// Incluir el footer
incluirTemplate('footer');
?>
