<?php
    //por si se accede manualmente verificar la session de solamente admin
    verificarSesionActiva('Admin');
   
    use ProtoClase\CentrosAcopio;
    use ProtoClase\Usuario;
    // Asegurar que tenemos la información del usuario seleccionado
    if (!isset($usuarioSeleccionado)) {
        // Si este archivo se carga directamente, redirigir
        header('Location: /Admin/index.php');
        exit;
    }

// Obtener el ID del usuario
$idUsuario = $usuarioSeleccionado['Id'];

//Creamos un objeto usuario
$usuarioSeleccionado = new Usuario(Usuario::ExtraerUnaTupla("Id",$idUsuario));

// Calcular días desde el registro
$diasRegistrado = 0;
if (isset($usuarioSeleccionado->FRegistro)) {
    $fechaRegistro = new DateTime($usuarioSeleccionado->FRegistro);
    $fechaActual = new DateTime();
    $diasRegistrado = $fechaRegistro->diff($fechaActual)->days;
}

// Obtener edad a partir de FNacimiento
$edad = '';
if (isset($usuarioSeleccionado->FNacimiento)) {
    $fechaNacimiento = new DateTime($usuarioSeleccionado->FNacimiento);
    $fechaActual = new DateTime();
    $edad = $fechaNacimiento->diff($fechaActual)->y;
}

// Datos del centro de acopio
$nombreCentro = CentrosAcopio::ExtraerUnsoloDatoConWhere("Id",$usuarioSeleccionado->IdCentroAcopio,"Nombre");
// Calcular cubetas faltantes como módulo 10
$cubetasTotales = $usuarioSeleccionado->CubetasTot;

$cubetasFaltantes = $cubetasTotales % 10;
$cubetasFaltantes = 10 - $cubetasFaltantes;

// Calcular la fecha máxima permitida para el campo de fecha de nacimiento (18 años)
$fecha_maxima = date('Y-m-d', strtotime('-18 years'));

?>

<div class="informacion-usuario">
    <div class="informacion-usuario__header">
        <h2 class="informacion-usuario__titulo">Información Detallada del Usuario</h2>

        <?php if($usuarioSeleccionado->Rol !== 'SuperAdmin'):?>
        <button class="boton-editar-perfil" type="button" id="btnAbrirModal">
            <img src="/vectores/Admin/perfil/VectorEditarPerfil.svg" alt="Editar Perfil" class="boton-editar-perfil__icono">
            <span class="boton-editar-perfil__texto">Editar Perfil</span>
        </button>
        <?php endif; ?>
        
    </div>
    
    <div class="informacion-usuario__tabla">
        <table>
            <!-- Primera fila: Nombre y apellidos -->
            <tr>
                <td>
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Nombre</div>
                        <div class="tarjeta__contenido"><?php echo s($usuarioSeleccionado->Nombre); ?></div>
                    </div>
                </td>
                <td>
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Apellido Paterno</div>
                        <div class="tarjeta__contenido"><?php echo $usuarioSeleccionado->ApPat ? s($usuarioSeleccionado->ApPat) : "-"; ?></div>
                    </div>
                </td>
                <td>
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Apellido Materno</div>
                        <div class="tarjeta__contenido"><?php echo $usuarioSeleccionado->ApMat ? s($usuarioSeleccionado->ApMat) : "-"; ?></div>
                    </div>
                </td>
            </tr>
            
            <!-- Segunda fila: Centro de acopio (rowspan=2), Tiempo en VC, ID y Edad -->
            <tr>
                <td rowspan="2">
                    <div class="tarjeta tarjeta--alta tarjeta--centro-acopio">
                        <div class="tarjeta__encabezado">Centro de Acopio</div>
                        <div class="tarjeta__contenido"><?php echo s($nombreCentro?? '-'); ?></div>
                    </div>
                </td>
                <td>
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Tiempo en VC</div>
                        <div class="tarjeta__contenido"><?php echo s($diasRegistrado); ?> días</div>
                    </div>
                </td>
                <td>
                    <div class="tarjeta tarjeta--dividida">
                        <div class="tarjeta__mitad">
                            <div class="tarjeta__encabezado">ID</div>
                            <div class="tarjeta__contenido"><?php echo s($usuarioSeleccionado->Id); ?></div>
                        </div>
                        <div class="tarjeta__mitad">
                            <div class="tarjeta__encabezado">Edad</div>
                            <div class="tarjeta__contenido"><?php echo s($edad!==''? $edad : "-"); ?></div>
                        </div>
                    </div>
                </td>
            </tr>
            
            <!-- Tercera fila: Centro de acopio (continuación), Cubetas Totales y Cubetas Faltantes -->
            <tr>
                <!-- Centro de acopio ya ocupado por rowspan -->
                <td>
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Cubetas Totales</div>
                        <div class="tarjeta__contenido"><?php echo s($cubetasTotales); ?></div>
                    </div>
                </td>
                <td>
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Cubetas Faltantes</div>
                        <div class="tarjeta__contenido"><?php echo $cubetasFaltantes; ?></div>
                    </div>
                </td>
            </tr>
            
            <!-- Cuarta fila: Teléfono y Correo (colspan=2) -->
            <tr>
                <td>
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Teléfono</div>
                        <div class="tarjeta__contenido"><?php echo $usuarioSeleccionado->Telefono!==''? s($usuarioSeleccionado->Telefono) : '-'; ?></div>
                    </div>
                </td>
                <td colspan="2">
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Correo</div>
                        <div class="tarjeta__contenido"><?php echo s($usuarioSeleccionado->Correo?? 'Correo no especificado'); ?></div>
                    </div>
                </td>
            </tr>
            
            <!-- Quinta fila: Rol de usuario -->
            <tr>
                <td rowspan="2">
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Rol de usuario</div>
                        <div class="tarjeta__contenido"><?php echo isset($usuarioSeleccionado->Rol) ? s($usuarioSeleccionado->Rol) : 'User'; ?></div>
                    </div>
                </td>
                <td colspan="2" rowspan="2">
                    <div class="tarjeta">
                        <div class="tarjeta__encabezado">Direccion</div>
                        <div class="tarjeta__contenido"><?php echo s($usuarioSeleccionado->Direccion ?? 'Direccion no especificada'); ?></div>
                    </div>
                </td>
            </tr>
            <tr></tr>
        </table>
    </div>
</div>

<!-- Nueva sección para la imagen a ancho completo -->
<div class="informacion-usuario__imagen-completa">
    <img src="/vectores/User/perfil/COMPOSTA TIERRA.png" alt="Imagen informativa" class="imagen-ancho-completo">
</div>

<!-- Modal para editar perfil -->
<div id="modalEditarPerfil" class="modal-editar">
    <div class="modal-editar__contenido">
        <div class="modal-editar__header">
            <h3 class="modal-editar__titulo">Editar Perfil de Usuario</h3>
            <button class="modal-editar__cerrar" type="button" id="btnCerrarModal">&times;</button>
        </div>
        
        <form id="formEditarPerfil" class="modal-editar__formulario" method="POST" action="procesar_edicion.php">
            <input type="hidden" name="Id" value="<?php echo s($usuarioSeleccionado->Id); ?>">
            
            <!-- Variables adicionales para procesar_edicion.php -->
            <input type="hidden" name="usuario_seleccionado" value="<?php echo s(json_encode($usuarioSeleccionado)); ?>">
            <input type="hidden" name="progreso_cubetas" value="<?php echo s(json_encode($progresoCubetas)); ?>">
            <input type="hidden" name="cubetas_restantes" value="<?php echo s(json_encode($cubetasRestantes)); ?>">
            <input type="hidden" name="meta_alcanzada" value="<?php echo s(json_encode($metaAlcanzada)); ?>">
            
            <div class="modal-editar__grid">
                <!-- Columna izquierda: Datos personales -->
                <div class="modal-editar__columna">
                    <h4 class="modal-editar__subtitulo">Datos Personales</h4>
                    
                    <div class="modal-editar__campo">
                        <label for="edit_nombre">Nombre:</label>
                        <input type="text" id="edit_nombre" name="Nombre" value="<?php echo s($usuarioSeleccionado->Nombre); ?>" required>
                    </div>
                    
                    <div class="modal-editar__campo">
                        <label for="edit_apellido_paterno">Apellido Paterno:</label>
                        <input type="text" id="edit_apellido_paterno" name="ApPat" value="<?php if(isset($usuarioSeleccionado->ApPat)) echo s($usuarioSeleccionado->ApPat); ?>" >
                    </div>
                    
                    <div class="modal-editar__campo">
                        <label for="edit_apellido_materno">Apellido Materno:</label>
                        <input type="text" id="edit_apellido_materno" name="ApMat" value="<?php if(isset($usuarioSeleccionado->ApMat)) echo s($usuarioSeleccionado->ApMat); ?>" >
                    </div>
                    
                    <div class="modal-editar__campo">
                        <label for="edit_fecha_nacimiento">Fecha de Nacimiento:</label>
                        <input type="date" id="edit_fecha_nacimiento" name="FNacimiento" max="<?php echo $fecha_maxima; ?>" value="<?php echo $usuarioSeleccionado->FNacimiento; ?>" >
                    </div>
                </div>
                
                
                <!-- Columna derecha: Contacto y centro - CORREGIDA -->
                <div class="modal-editar__columna">
                    <h4 class="modal-editar__subtitulo">Contacto y Ubicación</h4>
                    
                    <div class="modal-editar__campo">
                        <label for="edit_telefono">Teléfono:</label>
                        <input type="tel" id="edit_telefono" name="Telefono" value="<?php echo s($usuarioSeleccionado->Telefono); ?>" required>
                    </div>
                    
                    <!-- CAMPO DE DIRECCIÓN CORREGIDO -->
                    <div class="modal-editar__campo">
                        <label for="edit_direccion">Dirección (opcional):</label>
                        <input type="text" id="edit_direccion" name="Direccion" value="<?php echo s($usuarioSeleccionado->Direccion?? ''); ?>" >
                    </div>
                    
                    <div class="modal-editar__campo">
                        <label for="edit_correo">Correo (opcional):</label>
                        <input type="email" id="edit_correo" name="Correo" value="<?php echo s($usuarioSeleccionado->Correo ?? ''); ?>">
                    </div>
                    
                    <div class="modal-editar__campo">
                        <label for="edit_centro_acopio">Centro de Acopio:</label>
                        <select id="edit_centro_acopio" name="IdCentroAcopio" required>
                            <?php
                            $TodosLosCentros = CentrosAcopio::ExtraerAtributosEspecificos();
                            foreach($TodosLosCentros as $centro): 
                            ?>
                                <option value="<?php echo $centro['Id']; ?>" <?php echo (int)$usuarioSeleccionado->IdCentroAcopio === (int)$centro['Id'] ? 'selected' : ''; ?>>
                                    <?php echo s($centro['Nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Botón de reestablecer contraseña -->
                    <div class="modal-editar__campo modal-editar__campo--especial">
                        <button type="button" class="boton-reestablecer" id="btnReestablecerPassword">
                            <i class="fa fa-key"></i>
                            Reestablecer Contraseña
                        </button>
                        <p class="modal-editar__ayuda">La contraseña se reestablecerá a la generada automáticamente</p>
                    </div>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="modal-editar__botones">
                <button type="button" class="boton-modal boton-modal--cancelar" id="btnCancelarModal">
                    <i class="fa fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="boton-modal boton-modal--guardar">
                    <i class="fa fa-save"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Funciones específicas para este modal (evitar conflictos con app.js)
    (function() {
        'use strict';
        
        // Funciones del modal
        function abrirModalEditarPerfil() {
            const modal = document.getElementById('modalEditarPerfil');
            
            if (modal) {
                modal.classList.remove('modal-cerrado');
                modal.classList.add('modal-activo');
                document.body.style.overflow = 'hidden';
            }
        }

        function cerrarModalEditarPerfil() {
            const modal = document.getElementById('modalEditarPerfil');
            
            if (modal) {
                modal.classList.remove('modal-activo');
                modal.classList.add('modal-cerrado');
                document.body.style.overflow = 'auto';
            }
        }

        // Función para reestablecer contraseña
        function reestablecerContrasenaEditarPerfil() {
            if (confirm('¿Está seguro de que desea reestablecer la contraseña? La nueva contraseña será generada automáticamente basada en el nombre y número telefonico del usuario.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'reestablecer_contrasena.php';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'Id';
                inputId.value = '<?php echo $usuarioSeleccionado->Id; ?>';
                
                // Agregar las 4 variables adicionales para reestablecer_contrasena.php
                const inputUsuarioSeleccionado = document.createElement('input');
                inputUsuarioSeleccionado.type = 'hidden';
                inputUsuarioSeleccionado.name = 'usuario_seleccionado';
                inputUsuarioSeleccionado.value = <?php echo json_encode(json_encode($usuarioSeleccionado)); ?>;
                
                const inputProgresoCubetas = document.createElement('input');
                inputProgresoCubetas.type = 'hidden';
                inputProgresoCubetas.name = 'progreso_cubetas';
                inputProgresoCubetas.value = <?php echo json_encode(json_encode($progresoCubetas)); ?>;
                
                const inputCubetasRestantes = document.createElement('input');
                inputCubetasRestantes.type = 'hidden';
                inputCubetasRestantes.name = 'cubetas_restantes';
                inputCubetasRestantes.value = <?php echo json_encode(json_encode($cubetasRestantes)); ?>;
                
                const inputMetaAlcanzada = document.createElement('input');
                inputMetaAlcanzada.type = 'hidden';
                inputMetaAlcanzada.name = 'meta_alcanzada';
                inputMetaAlcanzada.value = <?php echo json_encode(json_encode($metaAlcanzada)); ?>;
                
                form.appendChild(inputId);
                form.appendChild(inputUsuarioSeleccionado);
                form.appendChild(inputProgresoCubetas);
                form.appendChild(inputCubetasRestantes);
                form.appendChild(inputMetaAlcanzada);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Función de validación del formulario
        function validarFormulario() {
            const telefono = document.getElementById('edit_telefono').value;
            const nombre = document.getElementById('edit_nombre').value.trim();
            
            return true;
        }

        // Esperar a que el DOM esté cargado
        document.addEventListener('DOMContentLoaded', function() {
            
            // Elementos del modal
            const modal = document.getElementById('modalEditarPerfil');
            const btnAbrir = document.getElementById('btnAbrirModal');
            const btnCerrar = document.getElementById('btnCerrarModal');
            const btnCancelar = document.getElementById('btnCancelarModal');
            const btnReestablecer = document.getElementById('btnReestablecerPassword');
            const formulario = document.getElementById('formEditarPerfil');
            
            // Event listeners para botones del modal
            if (btnAbrir) {
                btnAbrir.addEventListener('click', function(e) {
                    e.preventDefault();
                    abrirModalEditarPerfil();
                });
            }
            
            if (btnCerrar) {
                btnCerrar.addEventListener('click', function(e) {
                    e.preventDefault();
                    cerrarModalEditarPerfil();
                });
            }
            
            if (btnCancelar) {
                btnCancelar.addEventListener('click', function(e) {
                    e.preventDefault();
                    cerrarModalEditarPerfil();
                });
            }
            
            if (btnReestablecer) {
                btnReestablecer.addEventListener('click', function(e) {
                    e.preventDefault();
                    reestablecerContrasenaEditarPerfil();
                });
            }
            
            // Validación del formulario antes de envío
            if (formulario) {
                formulario.addEventListener('submit', function(e) {
                    if (!validarFormulario()) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
            
            // Cerrar modal al hacer clic fuera de él
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        cerrarModalEditarPerfil();
                    }
                });
            }
            
            // Cerrar modal con tecla Escape
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const modal = document.getElementById('modalEditarPerfil');
                    if (modal && modal.classList.contains('modal-activo')) {
                        cerrarModalEditarPerfil();
                    }
                }
            });
            
            // Validación del teléfono en tiempo real - CORREGIDA
            const telefonoInput = document.getElementById('edit_telefono');
            if (telefonoInput) {
                
                telefonoInput.addEventListener('input', function() {
                    
                    // Solo permitir números
                    this.value = this.value.replace(/\D/g, '');
                    
                    // Limitar a 13 dígitos máximo
                    if (this.value.length > 13) {
                        this.value = this.value.slice(0, 13);
                    }
                    
                    // Validación visual
                    if (this.value.length >= 10 && this.value.length <= 13) {
                        this.style.borderColor = '#28a745'; // Verde para válido
                        this.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
                    } else if (this.value.length > 0) {
                        this.style.borderColor = '#dc3545'; // Rojo para inválido
                        this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                    } else {
                        // Campo vacío, restablecer estilos
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }
                                });
                
                // También validar al perder el foco
                telefonoInput.addEventListener('blur', function() {
                    if (this.value.length > 0 && (this.value.length < 10 || this.value.length > 12)) {
                        this.style.borderColor = '#dc3545';
                        this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                    }
                });
            } 
            
            // Capitalizar nombres en tiempo real
            const camposTexto = ['edit_nombre', 'edit_apellido_paterno', 'edit_apellido_materno'];
            camposTexto.forEach(campoId => {
                const campo = document.getElementById(campoId);
                if (campo) {
                    campo.addEventListener('input', function() {
                        this.value = this.value
                            .split(' ')
                            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                            .join(' ');
                    });
                }
            });
            
            // Validación del campo de dirección (si existe)
            const direccionInput = document.getElementById('edit_direccion');
            if (direccionInput) {
                direccionInput.addEventListener('input', function() {
                    // Capitalizar primera letra de cada palabra
                    this.value = this.value
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                        .join(' ');
                });
            }
            
            // Validación del correo en tiempo real
            const correoInput = document.getElementById('edit_correo');
            if (correoInput) {
                correoInput.addEventListener('input', function() {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (this.value.length > 0) {
                        if (emailPattern.test(this.value)) {
                            this.style.borderColor = '#28a745';
                            this.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
                        } else {
                            this.style.borderColor = '#dc3545';
                            this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                        }
                    } else {
                        this.style.borderColor = '';
                        this.style.boxShadow = '';
                    }
                });
            }
            
        });
    })();
</script>