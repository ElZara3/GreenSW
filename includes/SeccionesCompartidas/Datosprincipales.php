<?php

use ProtoClase\Usuario;

//Verificarporserpaginaprotegidas
verificarSesionActiva();

$Id_user = $_SESSION['usuario'];

$Errores = Usuario::getErrores();
$Exitos = Usuario::getExitos();
//El usuario lo cargamos directamente
$UsuarioSelect = new Usuario(Usuario::ExtraerUnaTupla("Id",$Id_user));


// Procesar el formulario de cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_contrasena'])) {
    $contrasena_actual = $_POST['contrasena_actual'] ?? '';
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
    $confirmar_nueva_contrasena = $_POST['confirmar_nueva_contrasena'] ?? '';

    //Validamos errores
    if ($UsuarioSelect->ValidarCambioContrasena($contrasena_actual,$nueva_contrasena,$confirmar_nueva_contrasena)){
        $UsuarioSelect->Actualizar("Id", $Id_user, '=', null, null, null, 'Contrasena');
    }
    //Se hace la actualizacion de mensajes de error y exito
    $Errores = Usuario::getErrores();
    $Exitos = Usuario::getExitos();
    
}

// Calcular edad
date_default_timezone_set('America/Mexico_City');
$fecha_actual = new DateTime();
if (!empty($UsuarioSelect->FNacimiento)) {
    try {
        $fecha_nacimiento = new DateTime($UsuarioSelect->FNacimiento);
        if ($fecha_nacimiento <= $fecha_actual) {
            $edad_calc = $fecha_actual->diff($fecha_nacimiento)->y;
            $edad = ($edad_calc > 120) ? 'Fecha mal proporcionada' : $edad_calc;
        } else {
            $edad = 'Fecha inválida';
        }
    } catch (Exception $e) {
        $edad = 'Desconocida';
    }
}

// Calcular días desde registro
if (!empty($UsuarioSelect->FRegistro)) {
    try {
        $fecha_registro = new DateTime($UsuarioSelect->FRegistro);
        $diferencia_dias = $fecha_actual->diff($fecha_registro)->days;
    } catch (Exception $e) {
        $diferencia_dias = 0;
    }
}

// Calcular cubetas restantes
if (!empty($UsuarioSelect->CubetasTot)) {
    $CubTot = $UsuarioSelect->CubetasTot;
    $CubetasRes = $CubTot % 10;
    $CubetasRes = ($CubetasRes === 0) ? 10 : 10 - $CubetasRes;
    $kilos_residuos = $CubTot * 6.5;
}
//imprimimos lo errores en caso de errores:
foreach($Errores as $error): ?>
        <div class="mensaje-error">
            <?php echo $error; ?>
        </div>
<?php endforeach;
//En caso de exito
foreach($Exitos as $exito): ?>
        <div class="mensaje-exito">
            <?php echo $exito; ?>
        </div>
<?php endforeach;

// Mostramos el perfil
include '../includes/templates/infoPerfilUsuario.php';

// Sección para la tabla de cubetas 
if (isset($CubTot) && $CubTot >= 10): ?>
    <section class="RegistrodeBolsasdesdeUsuario">
        <?php 
        //Seccion momentanea 
        $db = conectarDB();
        $query = "SELECT * FROM bolsascompostausuario WHERE IdUsuario = ? ORDER BY FechadeBolsa DESC";
        $stmt = mysqli_prepare($db, $query);

        if ($stmt):
            mysqli_stmt_bind_param($stmt, 'i', $Id_user);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($resultado) > 0): ?>
                <!-- Tabla responsive -->
                <div class="table-container">
                    <table class="tabla-bolsas">
                        <thead>
                            <tr>
                                <th>Fecha de Bolsa</th>
                                <th>Número de Bolsa</th>
                                <th>Estatus de Entrega</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)):
                                $FechaBolsa = $fila['FechadeBolsa'] ?? '';
                                $NumdeBolsa = $fila['NoBolsa'] ?? '';
                                $EstatusdeEntrega = $fila['EstatusEntrega'] ?? 0; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($FechaBolsa); ?></td>
                                    <td><?php echo htmlspecialchars($NumdeBolsa); ?></td>
                                    <td>
                                        <?php echo ($EstatusdeEntrega == 1) ? 'Entregada' : 'Pendiente'; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No hay registros de bolsas disponibles.</p>
            <?php endif;

            mysqli_stmt_close($stmt);
        endif;
        ?>
    </section>
<?php endif; ?>
