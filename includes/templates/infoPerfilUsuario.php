<?php 
    //incluimos los datos desde la sesion
    

?>

<section class="perfil">
    <img src="/vectores/Admin/perfil/REGISTRO USUARIO IMAGEN.svg" alt="Foto de perfil">

    <p class="nombre"><span>Nombre: </span><?php echo s($UsuarioSelect->Nombre ?? ''); ?></p>

    <p class="dato">
        <span class="etiqueta">Apellido Paterno:</span> <?php echo s($UsuarioSelect->ApPat ?? '-'); ?>
    </p>

    <p class="dato">
        <span class="etiqueta">Apellido Materno:</span> <?php echo s($UsuarioSelect->ApMat ?? '-'); ?>
    </p>

    <p class="dato">
        <span class="etiqueta">Tiempo en Vive Composta:</span> <?php echo s($diferencia_dias ?? 0); ?> días
    </p>
    <p class="dato">
        <span class="etiqueta">Edad:</span> <?php echo s($edad ?? 'Desconocida'); ?>
    </p>

    <p class="dato">
        <span class="etiqueta">Kilos de Residuos Orgánicos:</span> <?php echo s($kilos_residuos ?? 0); ?> kg
    </p>

    <p class="dato">
        <span class="etiqueta">Cubetas faltantes para la siguiente meta:</span>
        <span class="valor-destacado"><?php echo s($CubetasRes ?? 10); ?></span>
    </p>

    <p class="dato">
        <span class="etiqueta">ID:</span> <?php echo s($Id_user ?? ''); ?>
    </p>
</section>
<section class="acciones_perfilInfoPerfilUsuario">
    <div class="botones-accion">
        <a href="javascript:void(0);" id="botonCambiarContrasena" class="BotonCambiarContrasena">Cambiar Contraseña</a>
        <a href="../cerrarSesion.php" class="BotonCerrarSesion">Cerrar sesión</a>
    </div>

    <!-- Modal para cambiar contraseña -->
    <div id="modalCambiarContrasena" class="modal" style="display:none;">
        <div class="modal-contenido">
            <span class="cerrar-modal" style="cursor:pointer;">&times;</span>
            <h3>Cambiar Contraseña</h3>

            <?php if (!empty($mensaje)): ?>
                <div class="mensaje-<?php echo s($tipo_mensaje); ?>">
                    <?php echo s($mensaje); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="contrasena_actual">Contraseña Actual:</label>
                    <div class="Contenedor_contrasena">
                        <input type="password" id="contrasena_actual" name="contrasena_actual" required>
                        <button type="button" class="toggle-password" aria-label="Mostrar/Ocultar contraseña">👁️</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="nueva_contrasena">Nueva Contraseña:</label>
                    <div class="Contenedor_contrasena">
                        <input type="password" id="nueva_contrasena" name="nueva_contrasena" required>
                        <button type="button" class="toggle-password" aria-label="Mostrar/Ocultar contraseña">👁️</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_nueva_contrasena">Confirmar Nueva Contraseña:</label>
                    <div class="Contenedor_contrasena">
                        <input type="password" id="confirmar_nueva_contrasena" name="confirmar_nueva_contrasena" required>
                        <button type="button" class="toggle-password" aria-label="Mostrar/Ocultar contraseña">👁️</button>
                    </div>
                </div>
                
                <button type="submit" name="cambiar_contrasena" class="boton-cambiar-contrasena">Cambiar Contraseña</button>
            </form>
        </div>
    </div>
</section>

