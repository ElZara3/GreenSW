<?php
require 'includes/app.php';

incluirTemplate('header', true);
?>

<div class="Imgblanca">
    <div class="preloader" id="preloader">
        <div class="loader"></div>
        <div class="loader-text">Cargando...</div>
    </div>
</div>

    <main class="nos PrimerVista">
        <section class="quienessomos">
            <div class="contenido">
                <div class="texto">
                    <h1>¿Quiénes somos?</h1>
                    <img src="build_previo/img/Logos/1vc_logo_horiz.webp" alt="Logo ViveC" class="logo">
                    <p>Somos un equipo de biólogos de la UNAM que brinda una
                        solución práctica para reducir la contaminación ambiental
                        mediante el compostaje de residuos orgánicos, a través de un
                        modelo innovador de recolección, un proceso eficiente de
                        transformación de residuos orgánicos a composta…</p>
                </div>
                <div class="imagen-principal">
                    <img src="build_previo\img\nosotros_sect\IMAGEN 1.png" alt="Imagen Quiénes Somos">
                </div>
            </div>
        </section>


        <section class="seccion-info">
            <img src="build_previo/img/nosotros_sect/SOLIDO1.webp" alt="" class="imagen-fondo">
                <h1>... Y un sistema exitoso que motiva a las familias mexicanas a adquirir hábitos positivos para generar una mayor conciencia ambiental</h1>
                <div class="MisionCont">
                    <h2>MISIÓN</h2>
                    <p>Nuestra misión es crear conciencia y disminuir
                        el impacto ambiental de los residuos orgánicos
                        a través de acciones prácticas y reales
                    </p>
                </div>
                
                <img src="build_previo/img/nosotros_sect/3vc_isotipo.webp" alt="" class="imagen-flor">
        </section>



        <section class="somos_cadena">
            <h1>Normas con las que cumplimos:</h1>

            <!-- Primera tabla 1x2 -->
            <table class="tabla-normas">
                <tr>
                    <td class="celda-texto">
                        <h1>NORMA</h1>
                        <h2>NTEA-006-SMA-RS-2006</h2>
                        <p>Norma Técnica Estatal Ambiental (SMA
                            EDOMEX), que establece los requisitos
                            para la producción de mejoradores de
                            suelos elaborados a partir de residuos
                            orgánicos.</p>
                    </td>
                    <td class="celda-imagen">
                        <img src="build_previo/img/nosotros_sect/SEMARNAT 1.webp" alt="Imagen representativa NADF-020-AMBT-2011">
                    </td>
                </tr>
            </table>

            <!-- Segunda tabla 1x2 -->
            <table class="tabla-normas">
                <tr>
                    <td class="celda-texto2">
                        <h1>NORMA</h1>
                        <h2>NMX-AA-180-SCFI-2018</h2>
                        <p>Norma Mexicana (SEMARNAT).
                            Establece los métodos y procedimientos
                            para el tratamiento aerobio de la fracción
                            orgánica de los residuos sólidos urbanos
                            y de manejo especial, así como la
                            información comercial y de sus parámetros
                            de calidad de los productos finales.</p>
                    </td>
                    <td class="celda-imagen">
                        <img src="build_previo/img/nosotros_sect/SEMARNAT 2.webp" alt="Imagen representativa NMX-AA-180-SCFI-2018">
                    </td>
                </tr>
            </table>
        </section>


        <!-- HTML para la sección de colaboradores con carrusel -->
        <section class="colaboradores">
            <h1>COLABORADORES</h1>

            <div class="carrusel-container">
                <!-- Nuevo wrapper para permitir el deslizamiento -->
                <div class="carrusel-slides-wrapper">
                    <div class="carrusel-slide active">
                        <div class="colaborador-item">
                            <img src="build_previo/img/COLABORADORES/CHILUCA.webp" alt="Colaborador 1">
                        </div>
                        <div class="colaborador-item">
                            <img src="build_previo/img/COLABORADORES/CLILUCA LIMPIO.webp" alt="Colaborador 2">
                        </div>
                        <div class="colaborador-item">
                            <img src="build_previo/img/COLABORADORES/COLONOS BOSQUES DE ECHEGARAI.webp" alt="Colaborador 3">
                        </div>
                    </div>
                    <div class="carrusel-slide">
                        <div class="colaborador-item">
                            <img src="build_previo/img/COLABORADORES/COLONOS SATÉLITE.webp" alt="Colaborador 5">
                        </div>
                        <div class="colaborador-item">
                            <img src="build_previo/img/COLABORADORES/ECOSAYAVEDRA.webp" alt="Colaborador 6">
                        </div>
                        <div class="colaborador-item">
                            <img src="build_previo/img/COLABORADORES/UNDERGROUND FITNES.webp" alt="Colaborador 7">
                        </div>
                    </div>
                    <!-- Puedes agregar más slides según sea necesario -->
                </div>
            </div>

            <!-- Indicadores del carrusel -->
            <div class="carrusel-indicadores">
                <span class="indicador active" data-slide="0"></span>
                <span class="indicador" data-slide="1"></span>
                <!-- Agrega más indicadores si tienes más slides -->
            </div>
        </section>

        
        <section class="cadena_gift_Nosotros">
            <img src="vectores/Nosotros/SOLIDO2.svg" alt="" class="fondo-svg">
            
            <div class="contenedor-superpuesto">
                <div class="lado-izquierdo">
                    <img src="/vectores/Nosotros/SOLIDO2 TEXTO.svg" alt="Imagen lado izquierdo" class="imagen-estatica">
                </div>
                <div class="lado-derecho">
                    <img src="Gifs_videos_media/Sect_Nosotros/INTERCAMBIO.gif" alt="Animación" class="gif-animado">
                </div>
            </div>
        </section>

        <section class="camionesNosotros">
            <div class="FondoCamionesNosotros">
                <article class="Cif">
                    <p class="ValorCif animacionCifras">+630</p>
                    <img src="vectores/Nosotros/SIFRAS 1.svg" class="gif-cifras" alt="">
                    <p class="TexCif">De residuos orgánicos recolectados y transformados en composta</p>
                </article>
                <article class="Cif escpecialCrifras">
                    <p class="ValorCif animacionCifras">+2,000</p>
                    <img src="vectores/Nosotros/SIFRAS 2.svg" class="gif-cifras" alt="">

                </article>
                <article class="Cif">
                    <p class="ValorCif animacionCifras">+190</p>
                    <img src="vectores/Nosotros/SIFRAS 3.svg" class="gif-cifras" alt="">
                    <p class="TexCif especial ">De composta producida que se ha entregado a los participantes y donado para restauraciones de suelo</p>
                </article>
                <article class="Cif">
                    <p class="ValorCif animacionCifras">+30</p>
                    <img src="vectores/Nosotros/SIFRAS 4.svg" class="gif-cifras" alt="">
                    <p class="TexCif">De residuos orgánicos recolectados mensualmente</p>
                </article>
            </div>
        </section>
    </main>

<script src="build_previo/js/app.js">
</script>
<script src="build_previo/js/modernizr.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        //Funcion para el nav
        DesplegarNav();
    });
</script>

<?php
    incluirTemplate('footer', false);
    ?>