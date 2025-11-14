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


<main class="MiCubeta PrimerVista" >
    <section class="TituloSectCubeta">
        <h3>Mi Cubeta Amarilla</h3>
    </section>
    <section class="ListadoDeCosasSi">
        <img src="vectores/MiCubeta/QUE SI PUEDES INCLUIR.png" alt="">
    </section>
    <section class="PlacaVerd"> 
        <img src="/vectores/MiCubeta/PLECA VERDE.png" alt="">
    </section>
    <section class="muneca"> 
        <img src="/vectores/MiCubeta/MUÑEQUITA NO.png" alt="">
    </section>
    <section> 
        <img src="/vectores/MiCubeta/QUE NO PONER.png" alt="">
    </section>

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
