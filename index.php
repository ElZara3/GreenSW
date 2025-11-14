<?php
require 'includes/app.php';

incluirTemplate('header', true);

?>


<!-- Preloader para la carga de pag -->
<div class="Imgblanca">
    <div class="preloader" id="preloader">
        <div class="loader"></div>
        <div class="loader-text">Cargando...</div>
    </div>
</div>

    <?php
        //Cargamos el inicio desde aqui es donde se va a hacer de manera dinamica
        //para no estar yendo de php en php, todo desde el index

        require  'inicio.php';
        //
    
    ?>

<script src="./build_previo/js/app.js">
</script>
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
