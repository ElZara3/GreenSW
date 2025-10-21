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

    <main class="Composta_sect PrimerVista">
        <section class="ProcesoComposta">
            <h3>Proceso de transformación</h3>
            <p>Conoce como transformamos tus residuos organicos en la mejor composta</p>
            <img src="vectores/Composta/PROCESO_completo.png" alt="">
        </section>

        <section class="FotosSectionComp">
            <figure class="Fondo">
                <img src="vectores/Composta/Amarillo y linea.png" alt="">
                <!--Carrusel solo para dispositivos moviles-->
                <div class="carousel">
                    <div class="carousel-track-container">
                        <ul class="carousel-track">
                            <li class="carousel-slide">
                                <img src="vectores/Composta/imagenes_comprimidas/Foto1.png" alt="">
                            </li>
                            <li class="carousel-slide">
                                <img src="vectores/Composta/imagenes_comprimidas/Foto2.png" alt="">
                            </li>
                            <li class="carousel-slide">
                                <img src="vectores/Composta/imagenes_comprimidas/Foto3.png" alt="">
                            </li>
                        </ul>
                    </div>
                    <!-- Contenedor para los indicadores -->
                    <div class="carousel-indicators"></div>
                </div>
                <!--Seccion para unicamente desktop-->
                <div class="imagenesFotosComp">
                    <img src="vectores/Composta/imagenes_comprimidas/Foto1.png" alt="">
                    <img src="vectores/Composta/imagenes_comprimidas/Foto2.png" alt="">
                    <img src="vectores/Composta/imagenes_comprimidas/Foto3.png" alt="">
                </div>

            </figure>
        </section>

        <section class="ComoUtilizarComp">
            <figure class="FondoTierraComp">
                <img src="vectores/Composta/COMPOSTA TIERRA.png" alt="" class="Tierra">
                <h2>¿Cómo utilizar la mejor composta?</h2>
                <img src="vectores/Composta/Bolsa.png" alt="" class="Bolsa">
                <img src="vectores/Composta/flecha.svg" alt="" class="Flecha">
                <a href="/Manual_Composta.pdf" target="_blank" class="Boton">Descargar el manual</a>
                
            </figure>
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