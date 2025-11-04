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
            <p>Conoce cómo transformamos tus residuos orgánicos en la mejor composta.</p>
            <img src="vectores/Composta/PROCESO_completo.png" alt="">
        </section>

        <section class="FotosSectionComp">
            <figure class="Fondo">
                <img src="vectores/Composta/Amarillo y linea.png" alt="">
                <!--Carrusel solo para dispositivos moviles-->
                <!-- <div class="carousel">
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
                    <div class="carousel-indicators"></div>
                </div> -->
                <div class="only-mobile CarruselBootstrapComp">
                    <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                        <!-- Indicadores -->
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="2" aria-label="Slide 3"></button>
                        </div>

                        <!-- Slides -->
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="vectores/Composta/imagenes_comprimidas/Foto1.png" class="d-block w-100" alt="">
                            </div>
                            <div class="carousel-item">
                                <img src="vectores/Composta/imagenes_comprimidas/Foto2.png" class="d-block w-100" alt="">
                            </div>
                            <div class="carousel-item">
                                <img src="vectores/Composta/imagenes_comprimidas/Foto3.png" class="d-block w-100" alt="">
                            </div>
                        </div>
                    </div>
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