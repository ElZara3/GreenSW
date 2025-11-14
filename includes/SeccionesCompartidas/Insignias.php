<?php

//Agregamos las 2 clases a utilizar
use ProtoClase\InsigniasUsuario;
use ProtoClase\Insignias;

verificarSesionActiva();

// Obtener ID del usuario actual (usando la variable de sesión correcta)
$idUsuarioActual = $_SESSION['usuario'];

// Registrar insignias desconocidas anteriormente para detectar nuevos desbloqueos
$insigniasPrevias = [];
if (isset($_SESSION['insignias_previas'])) {
    $insigniasPrevias = $_SESSION['insignias_previas'];
}

// Consultar las insignias del usuario
$insigniasDesbloqueadas = [];
$insigniasDesbloqueadas = InsigniasUsuario::ExtraerInsigniasUsuarioId('IdUsuario', $idUsuarioActual);

// Detectar nuevas insignias desbloqueadas
$nuevasInsignias = array_diff($insigniasDesbloqueadas, $insigniasPrevias);

// Guardar el estado actual de las insignias para la próxima vez
$_SESSION['insignias_previas'] = $insigniasDesbloqueadas;

// Extraemos todas las insignias posibles de la clase (sin duplicar con consulta SQL)
$todasInsignias = Insignias::ExtraerInsigniasSinMensual();

?>

<main class="contenedor-insignias">
    <!-- Sección 1: Insignias Totales -->
    <section class="seccion-insignias-totales">
        <?php
        // Ajustar la ruta para que sea relativa a la raíz del sitio
        // Usar la imagen ILUSTRACION INSIGNIAS.svg como imagen principal
        $rutaImagenTotales = "/vectores/User/Insignias/INSIGNIAS KG HISTORICOS.svg";

        // Verificar si la imagen existe (para entorno local)
        $rutaServerTotales = $_SERVER['DOCUMENT_ROOT'] . $rutaImagenTotales;
        if (file_exists($rutaServerTotales)) {
            echo '<img src="' . $rutaImagenTotales . '" alt="Insignias Totales" class="insignia-totales-img">';
        } else {
            echo '<div class="imagen-placeholder">';
            echo '<p>Imagen de insignias totales</p>';
            echo '</div>';
        }
        ?>
    </section>

    <!-- Sección 2: Insignias del Mes -->
    <section class="seccion-insignias-mes">
        <?php
        // Ajustar la ruta para que sea relativa a la raíz del sitio
        // Usar MENSUAL.svg para la imagen mensual
        $rutaImagenMes = "/vectores/User/Insignias/MENSUAL.svg";

        // Verificar si la imagen existe (para entorno local)
        $rutaServerMes = $_SERVER['DOCUMENT_ROOT'] . $rutaImagenMes;
        if (file_exists($rutaServerMes)) {
            echo '<img src="' . $rutaImagenMes . '" alt="Insignias del Mes" class="insignia-mes-img">';
        } else {
            echo '<div class="imagen-placeholder">';
            echo '<p>Imagen de insignias del mes</p>';
            echo '</div>';
        }
        ?>
    </section>

    <!-- Sección 3: Línea de Progresión -->
    <section class="seccion-progresion">
        <h2 class="titulo-progresion">Mis insignias</h2>

        <?php if (empty($todasInsignias)): ?>
            <div class="sin-insignias">
                <p>No se encontraron insignias en la base de datos.</p>
            </div>
        <?php else: ?>
            <div class="linea-progresion">
                <?php foreach ($todasInsignias as $insignia): ?>
                    <div class="insignia-item">
                        <?php
                        // Convertir a string para comparación consistente (ya que vienen de la clase como string)
                        $insigniaId = (string)$insignia['Id'];
                        
                        // Verificar si la insignia está desbloqueada
                        $desbloqueada = in_array($insigniaId, $insigniasDesbloqueadas);
                        // Verificar si es una nueva insignia desbloqueada
                        $nuevaInsignia = in_array($insigniaId, $nuevasInsignias);

                        // Clases adicionales para la animación
                        $clasesAdicionales = '';
                        if ($desbloqueada) {
                            $clasesAdicionales .= ' desbloqueada';
                        } else {
                            $clasesAdicionales .= ' bloqueada';
                        }

                        // Determinar qué imagen mostrar
                        if ($nuevaInsignia) {
                            // Para nuevas insignias, mostrar cubreinsignia inicialmente
                            $nombreArchivoMostrar = 'cubreinsignia.png';
                            $nombreArchivoReal = $insignia['Id'] . ' INS ' . strtoupper(getNombreAnimal($insignia['Id'])) . '.png';
                            $rutaImagenReal = "/vectores/User/Insignias/" . $nombreArchivoReal;
                        } else if ($desbloqueada) {
                            $nombreArchivoMostrar = $insignia['Id'] . ' INS ' . strtoupper(getNombreAnimal($insignia['Id'])) . '.png';
                            $rutaImagenReal = null;
                        } else {
                            $nombreArchivoMostrar = 'cubreinsignia.png';
                            $rutaImagenReal = null;
                        }

                        $rutaImagen = "/vectores/User/Insignias/" . $nombreArchivoMostrar;

                        // Verificar si la imagen existe
                        $rutaServerImagen = $_SERVER['DOCUMENT_ROOT'] . $rutaImagen;
                        if (file_exists($rutaServerImagen)):
                        ?>
                            <div class="insignia-contenedor">
                                <img src="<?php echo $rutaImagen; ?>"
                                    alt="<?php echo htmlspecialchars($insignia['Descripcion']); ?>"
                                    class="insignia-imagen <?php echo $clasesAdicionales; ?>"
                                    data-id="<?php echo $insignia['Id']; ?>"
                                    <?php echo $nuevaInsignia ? 'data-nueva-insignia="true"' : ''; ?>
                                    <?php echo $rutaImagenReal ? 'data-imagen-original="' . $rutaImagenReal . '"' : ''; ?>>
                                
                                <?php if ($nuevaInsignia): ?>
                                    <div class="confeti"></div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="insignia-placeholder">
                                <?php echo $insignia['Id']; ?>
                            </div>
                            <p class="imagen-no-encontrada">No se encontró: <?php echo $nombreArchivoMostrar; ?></p>
                        <?php endif; ?>

                        <?php if ($desbloqueada): ?>
                            <p class="insignia-descripcion">
                                <?php echo htmlspecialchars($insignia['Descripcion']); ?>
                            </p>
                            <p class="insignia-animal">
                                <?php echo $insignia['Id']; ?> INS <?php echo strtoupper(getNombreAnimal($insignia['Id'])); ?>
                            </p>
                        <?php else: ?>
                            <p class="insignia-descripcion">
                                CUBRE INSIGNIA
                            </p>
                        <?php endif; ?>

                        <p class="insignia-requisito">
                            <?php echo $insignia['KilosComposta']; ?> kg
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="insignias-mensuales">
        <?php
        // Consultar cuántas insignias especiales tiene el usuario (ID = 9)
        $insigniasEspecialesCuenta = InsigniasUsuario::ExtraerConteoInsigniasEspeciales($idUsuarioActual);

        // Ruta a la imagen de la insignia especial
        $rutaImagenEspecial = "/vectores/User/Insignias/INSIGNIA ESPECIAL (STAR).png";
        
        // Verificar si hay nueva insignia especial
        $nuevaInsigniaEspecial = false;
        if (isset($_SESSION['insignias_especiales_previas']) && 
            $insigniasEspecialesCuenta > $_SESSION['insignias_especiales_previas']) {
            $nuevaInsigniaEspecial = true;
        }
        
        // Guardar conteo actual para la próxima vez
        $_SESSION['insignias_especiales_previas'] = $insigniasEspecialesCuenta;

        ?>
        <!-- Sección de Insignias Especiales -->
        <section class="seccion-insignias-especiales" id="seccion-insignias-especiales">
            <h2 class="insignias-especiales-titulo">Insignias Especiales</h2>
            <div class="insignias-especiales-contenido">
                <div class="insignias-especiales-contador"><?php echo $insigniasEspecialesCuenta; ?></div>
                <?php
                // Verificar si la imagen existe
                $rutaServerImagenEspecial = $_SERVER['DOCUMENT_ROOT'] . $rutaImagenEspecial;
                if (file_exists($rutaServerImagenEspecial)):
                ?>
                    <div class="insignia-especial-contenedor<?php echo $nuevaInsigniaEspecial ? ' nueva-insignia-especial-contenedor' : ''; ?>">
                        <img src="<?php echo $rutaImagenEspecial; ?>"
                            alt="Insignia Especial"
                            class="insignias-especiales-imagen<?php echo $nuevaInsigniaEspecial ? ' nueva-insignia-especial-pendiente' : ''; ?>"
                            <?php echo $nuevaInsigniaEspecial ? 'data-nueva-insignia-especial="true"' : ''; ?>>
                        
                        <?php if ($nuevaInsigniaEspecial): ?>
                            <div class="confeti-especial"></div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="imagen-placeholder-especial">
                        <span>⭐</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </section>
</main>
<script>
    //Forzar la animacion de insignias que no pude desplegarla desde el dom principal
    document.addEventListener('DOMContentLoaded', function () {
        AnimacionesInsigniasUsuario();
    });

</script>
<?php

// Función auxiliar para obtener el nombre del animal basado en el ID
function getNombreAnimal($id)
{
    $animales = [
        1 => 'TORTUGA',
        2 => 'CONEJO',
        3 => 'LOBO',
        4 => 'COLIBRI',
        5 => 'MARIPOSA',
        6 => 'AJOLOTE',
        7 => 'JAGUAR',
        8 => 'QUETZAL'
    ];

    return isset($animales[$id]) ? $animales[$id] : 'INSIGNIA';
}

?>