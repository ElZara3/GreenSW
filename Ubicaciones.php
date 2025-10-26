<?php
require './includes/config/database.php';
require 'includes/funciones.php';

incluirTemplate('header', true);

?>

<!-- Preloader para la carga de pag -->
<div class="Imgblanca">
    <div class="preloader" id="preloader">
        <div class="loader"></div>
        <div class="loader-text">Cargando...</div>
    </div>
</div>


<main class="Ubicaciones PrimerVista" >
    <h3>Ubicaciones Centros de Acopio</h3>
    <!-- Carrusel para móvil -->
    <p>Visítanos en alguno de nuestros centros de acopio para realizar tu inscripcion y el cambio de tu cubeta amarilla.</p>
    
    <div class="CarruselParaUbicacionesBootstrap mobile-only">
        <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                <a href="https://maps.app.goo.gl/xdH86fg7ZXkZvkoF6" target="_blank">
                    <img src="vectores/ubicaciones/IconosRecortados/ZonaAzul_recortada.svg" class="d-flex w-80" alt="1 Satélite zona azul">
                </a>
                </div>
                <div class="carousel-item">
                <a href="https://maps.app.goo.gl/PLnwbeDvQEaAc18a8" target="_blank">
                    <img src="vectores/ubicaciones/IconosRecortados/chiluca_recortada.svg" class="d-flex w-80" alt="2 Chiluca">
                </a>
                </div>
                <div class="carousel-item">
                <a href="https://maps.app.goo.gl/NWtuHU2DtiSaXr519" target="_blank">
                    <img src="vectores/ubicaciones/IconosRecortados/sayavedra_recortada.svg" class="d-flex w-80" alt="3 Condado de Sayavedra">
                </a>
                </div>
                <div class="carousel-item">
                <a href="https://maps.app.goo.gl/xbZMjbiWRfTy8Mhy7" target="_blank">
                    <img src="vectores/ubicaciones/IconosRecortados/echegaray_recortada.svg" class="d-flex w-80" alt="4 Paseo Echegaray">
                </a>
                </div>
                <div class="carousel-item">
                <a href="https://maps.app.goo.gl/y81pMNy9489x6tGv5" target="_blank">
                    <img src="vectores/ubicaciones/IconosRecortados/CentroCivico_Recortada.svg" class="d-flex w-80" alt="5 Satelite Centro Civico">
                </a>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>    
    <!-- Grid para desktop -->

    <div class="locations-grid desktop-only">
        <div class="location-item">
            <img src="vectores/ubicaciones/Ubicaciones svg/1 zona azul.svg" alt="1 Satélite zona azul">
            <a href="https://maps.app.goo.gl/xdH86fg7ZXkZvkoF6" target="_blank">Clic maps</a>
        </div>
        <div class="location-item">
            <img src="vectores/ubicaciones/Ubicaciones svg/2 chiluca.svg" alt="2 Chiluca">
            <a href="https://maps.app.goo.gl/PLnwbeDvQEaAc18a8" target="_blank">Clic maps</a>
        </div>
        <div class="location-item">
            <img src="vectores/ubicaciones/Ubicaciones svg/3 condado de sayavedra.svg" alt="3 Condado de Sayavedra">
            <a href="https://maps.app.goo.gl/NWtuHU2DtiSaXr519" target="_blank">Clic maps</a>
        </div>
        <div class="location-item">
            <img src="vectores/ubicaciones/Ubicaciones svg/4 paseo de echegaray.svg" alt="4 Paseo de Echegaray">
            <a href="https://maps.app.goo.gl/xbZMjbiWRfTy8Mhy7" target="_blank">Clic maps</a>
        </div>
        <div class="location-item">
            <img src="vectores/ubicaciones/Ubicaciones svg/5 centro cívico.svg" alt="5 Satelite Centro Civico">
            <a href="https://maps.app.goo.gl/y81pMNy9489x6tGv5" target="_blank">Clic maps</a>
        </div>
    </div>

</main>

<script src="./build_previo/js/app.js"></script>
<script src="./build_previo/js/modernizr.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        //Funcion para el nav
        DesplegarNav();
    });
</script>
</body>

<?php
incluirTemplate('footer');
