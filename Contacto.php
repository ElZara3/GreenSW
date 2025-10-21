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


<main class="Contacto PrimerVista" >
    <h3>Contacto</h3>

    <section class="NumeroTextoYrecuadroContacto">
        <p class="Textprincipal">Para mayor información, contáctanos <span>y únete a la mejor comunidad de Vive Composta</span></p>
        <a class="Numero" href="https://wa.me/5564702484?text=Holaa!%20Quiero%20formar%20parte%20de%20la%20comunidad" target="_blank">
            <img src="vectores/Contacto/whats.svg" alt="">
            <h4>55 6470 2484</h4>
        </a>
        <div class="recuadro">
            <p>¿Quieres contarnos algo más?</p>
            <form action="" method="post">
                <textarea id="comentario" name="comentario"></textarea>
                <button type="submit" class="BtnEnviar">
                    <p>Enviar</p>
                </button>
            </form>
        </div>
        <img src="vectores/Contacto/Contacto menú.png" alt="" class="manoConPhone">
    </section>
    <section class="RedesIconosContacto">
        <figure class="FigurasDeRedes">
            <img src="vectores/Contacto/PLECA VERDE.png" alt="Fondo" class="desktop-only">
            <img src="vectores/Contacto/PLECA VERDE_mobile.png" alt="Fondo" class="mobile-only">
            <div class="ContenedorPrincipalImagenes divdeAbsolute">
                <div class="TexodeContacto">
                    <h2>¡Síguenos en nuestras redes sociales!</h2>
                    <h4>Forma parte de la mejor comunidad y entérate de más</h4>
                </div>
                <div class="RedesHorizontal">
                    <a href="https://www.instagram.com/vive_composta/?hl=es-la" target="_blank">
                        <img src="vectores/Contacto/Redes_iconos/instagram_contorno.png" alt="">
                    </a>
                    <a href="https://www.facebook.com/Vivecomposta" target="_blank">
                        <img src="vectores/Contacto/Redes_iconos/face_contorno.png" alt="">
                    </a>
                    <a href="https://www.tiktok.com/@vive_composta" target="_blank">
                        <img src="vectores/Contacto/Redes_iconos/tiktok_contorno.png" alt="">
                    </a>
                </div>
            </div>
            
        </figure>
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
