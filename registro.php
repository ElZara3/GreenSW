<?php
// Incluir funciones necesarias
include 'includes/funciones.php';
require 'includes/config/database.php';

// Iniciar sesión para guardar datos temporalmente
session_start();

// Conectar a la base de datos
$db = conectarDB();
if (!$db) {
    die("<p>Error al conectar a la base de datos: " . mysqli_connect_error() . "</p>");
}

// Incluir el template del header
incluirTemplate('header');

// Habilitar el reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializar variables
$nombre = $_POST['nombre'] ?? NULL;
$apellido_paterno = $_POST['apellido_paterno'] ?? NULL;
$apellido_materno = $_POST['apellido_materno'] ?? NULL;
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? NULL;
$correo = $_POST['correo'] ?? NULL;
$telefono = $_POST['telefono'] ?? NULL;
$centro_acopios = $_POST['centro_acopios'] ?? NULL;

// Calcular la fecha máxima permitida para el campo de fecha de nacimiento
$fecha_maxima = date('Y-m-d', strtotime('-18 years'));

// Consultar centros de acopio
$Consultacentros = "SELECT * FROM centrosacopio";
$resultado = mysqli_query($db, $Consultacentros);

// Procesar el formulario al enviar los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errores = []; // Arreglo para almacenar mensajes de error
    
    // Truncar los espacios del teléfono
    $telefono = str_replace(' ', '', $telefono);

    // Validar que el teléfono no contenga letras
    if (!ctype_digit($telefono)) {
        $errores[] = "El número de teléfono solo debe contener números.";
    }

    // Validar que la fecha de nacimiento no sea menor a 18 años
    $fecha_actual = new DateTime();
    $fecha_nacimiento_dt = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);

    if (!$fecha_nacimiento_dt) {
        $errores[] = "La fecha de nacimiento no es válida.";
    } else {
        $diferencia = $fecha_actual->diff($fecha_nacimiento_dt);
        if ($diferencia->y < 18) {
            $errores[] = "Debes tener al menos 18 años para registrarte.";
        }
    }

    // Validar que las contraseñas coincidan
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    if ($contrasena !== $confirmar_contrasena) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    // Si no hay errores, proceder con el registro
    if (empty($errores)) {
        $contrasena_encriptada = password_hash($contrasena, PASSWORD_DEFAULT);

        $query = "INSERT INTO usuarios 
                  (Nombre, ApPat, ApMat, Telefono, FNacimiento, FRegistro, Correo, IdCentroAcopio, Contrasena) 
                  VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
        try {
            $stmt = mysqli_prepare($db, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ssssssis', $nombre, $apellido_paterno, $apellido_materno, $telefono, $fecha_nacimiento, $correo, $centro_acopios, $contrasena_encriptada);
                $resultado = mysqli_stmt_execute($stmt);

                if ($resultado) {
                    $_SESSION['nombre'] = $_POST['nombre'];
                    header("Location: login.php");
                    exit;
                }
            } else {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($db));
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $errores[] = "El número de teléfono ya está en uso.";
            } else {
                $errores[] = "Ocurrió un error: " . $e->getMessage();
            }
        } catch (Exception $e) {
            $errores[] = "Ocurrió un error inesperado: " . $e->getMessage();
        }
    }

    // Mostrar errores
    if (!empty($errores)) {
        echo "<div class='Errores'>";
        foreach ($errores as $error) {
            echo "<p class='ErrorRegistro'>$error</p>";
        }
        echo "</div>";
    }
}

// Cerrar la conexión a la base de datos
mysqli_close($db);
?>

<section class="Registro">
    <h2>Registro de Usuario</h2>
    <form action="" method="POST">
        <div class="datos">
            <!-- Campos del formulario -->
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required value="<?php echo $nombre ?>">
            </div>

            <div class="form-group">
                <label for="apellido_paterno">Apellido Paterno:</label>
                <input type="text" id="apellido_paterno" name="apellido_paterno" required value="<?php echo $apellido_paterno ?>">
            </div>

            <div class="form-group">
                <label for="apellido_materno">Apellido Materno:</label>
                <input type="text" id="apellido_materno" name="apellido_materno" required value="<?php echo $apellido_materno ?>">
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?php echo $fecha_nacimiento; ?>" max="<?php echo $fecha_maxima; ?>">
            </div>

            <div class="form-group">
                <label for="centro_acopios">Centro de Acopios:</label>
                <select id="centro_acopios" name="centro_acopios" required>
                    <option value="">Seleccione un centro</option>
                    <?php while ($Centros = mysqli_fetch_assoc($resultado)): ?>
                        <option <?php echo $centro_acopios === $Centros['Id'] ? 'selected' : ''; ?>
                            value="<?php echo $Centros['Id']; ?>">
                            <?php echo $Centros['Nombre']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" required value="<?php echo $correo ?>">
            </div>

            <div class="form-group ultimo">
                <label for="telefono">Número de Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" required value="<?php echo $telefono ?>">
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <div class="Contenedor_contrasena">
                    <input type="password" id="contrasena" name="contrasena" required>
                    <button type="button" id="togglePassword" class="toggle-password">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar Contraseña:</label>
                <div class="Contenedor_contrasena">
                    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
                    <button type="button" id="toggleConfirmPassword" class="toggle-password">👁️</button>
                </div>
            </div>

            <button type="submit" class="boton-enviar">Registrar</button>
        </div>
    </form>
</section>

<script src="./build_previo/js/app.js"></script>

<?php
incluirTemplate('footer');
?>
