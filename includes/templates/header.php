<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Somos un proyecto mexicano dedicado a la recolección y manejo de residuos orgánicos para una correcta transformación en composta">
    <meta name="keywords" content="residuos orgánicos, composta, ViveComposta, Empresa de reciclaje, clientes, comunidad">
    <meta name="author" content="<?php echo $inicio ? "Equipo GreenSW" : "David Zaragoza, Diego Fuentes, Aletia Villasis, Alejandro Medina";?>">
    <title>Vive Composta</title>

    <link rel="stylesheet" href="/build_previo/css/app.css">
    <!-- css sobreescrito del anterior -->
    <link rel="stylesheet" href="build/css/app.css">
    <script src="build/js/bundle.js"></script>
    <link rel="shortcut icon" href="/build_previo/img/Logos/3vc_isotipo.webp" type="image/x-icon">

</head>

<body>
    <header class="NavegacionDesktop">
        <?php echo $inicio ? '
        <div class="Redes_princ">
            <li class="Redes">
                <a href="https://www.instagram.com/vive_composta/?hl=es-la" target="_blank">
                    <img src="/vectores/Logos_redes_inicio/logo instagram_nude.svg" alt="Instagram">
                </a>
                <a href="https://www.facebook.com/Vivecomposta" target="_blank">
                    <img src="/vectores/Logos_redes_inicio/logo face_nude.svg" alt="Facebook">
                </a>
                <a href="https://www.tiktok.com/@vive_composta" target="_blank">
                    <img src="/vectores/Logos_redes_inicio/logo tiktok_nude-.svg" alt="TikTok">
                </a>
            </li>
            <h1>Generando gramos de vida</h1>
            <div class="InicioS">
                <a href="/login.php">
                    <h1 class="TextoIniciar"> Iniciar sesión</h1>
                </a>
            </div>
        </div>' : '';  ?>

        <nav class="NavPrincipal NavDesplegable BordeCircular">
            <a class="Logo" href="/">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"viewBox="0 0 50 50">
                    <path d="M 3 9 A 1.0001 1.0001 0 1 0 3 11 L 47 11 A 1.0001 1.0001 0 1 0 47 9 L 3 9 z M 3 24 A 1.0001 1.0001 0 1 0 3 26 L 47 26 A 1.0001 1.0001 0 1 0 47 24 L 3 24 z M 3 39 A 1.0001 1.0001 0 1 0 3 41 L 47 41 A 1.0001 1.0001 0 1 0 47 39 L 3 39 z"></path>
                </svg>
                <img src="/build_previo/img/Logos/1vc_logo_horiz.webp" alt="Logo ViveC">
            </a>
            <ul class="Menu">
                <a href="/Nosotros.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Nosotros.php' ? 'active' : ''; ?>">NOSOTROS</a>
                <a href="/Ubicaciones.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Ubicaciones.php' ? 'active' : ''; ?>">UBICACIONES</a>
                <a href="/Unete.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Unete.php' ? 'active' : ''; ?>">ÚNETE</a>
                <a href="/Composta.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Composta.php' ? 'active' : ''; ?>">COMPOSTA</a>
                <a href="/MiCubeta.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'MiCubeta.php' ? 'active' : ''; ?>">MI CUBETA</a>
                <a href="/Contacto.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'Contacto.php' ? 'active' : ''; ?>">CONTACTO</a>
            </ul>
        </nav>
    </header>
    
    <nav class="NavMobile">
        <svg class="toggleMenu" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 50 50">
            <path d="M 0 9 L 0 11 L 50 11 L 50 9 Z M 0 24 L 0 26 L 50 26 L 50 24 Z M 0 39 L 0 41 L 50 41 L 50 39 Z"></path>
        </svg>
        
        <img src="/build_previo/img/Logos/1vc_logo_horiz.webp" alt="Logo ViveC">
        <a href="/login.php" class="IconoIniciarS">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="800px" height="800px" viewBox="0 0 32 32" enable-background="new 0 0 32 32" id="Stock_cut" version="1.1" xml:space="preserve">
                <desc/>
                <g>
                <circle cx="16" cy="16" fill="none" r="15" stroke="#000000" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>
                <path d="M26,27L26,27   c0-5.523-4.477-10-10-10h0c-5.523,0-10,4.477-10,10v0" fill="none" stroke="#000000" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>
                <circle cx="16" cy="11" fill="none" r="6" stroke="#000000" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>
                </g>
            </svg>
        </a>
        <!--Menu que se va a desplegar cuando se de clic-->
        <div class="menuPrincipalMobile">
            <h3>MENU</h3>
            <!--TEXTO PARA PODER CERRAR EL MENU-->
            <p id="CierreMenu"></p>
            <li>
                <a href="/">INICIO</a>
                <a href="/Nosotros.php">NOSOTROS</a>
                <a href="/Ubicaciones.php">UBICACIONES</a>
                <a href="/Unete.php">ÚNETE</a>
                <a href="/Composta.php">COMPOSTA</a>
                <a href="/MiCubeta.php">MI CUBETA</a>
                <a href="/Contacto.php">CONTACTO</a>
            </li>
        </div>    
    </nav>


        