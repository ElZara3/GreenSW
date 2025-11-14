<?php
    require '../includes/app.php';
    
    session_start();
    //En cada php que sea de User y se le muestre se debe hacer la comprobacion
    verificarSesionActiva('User');
    incluirTemplate('header');    
    //Tabla de los datos
    include '../includes/SeccionesCompartidas/Datosprincipales.php';

    //Incluir las insignias por usuario
    include '../includes/SeccionesCompartidas/Insignias.php';

    ?>
    <script src="/build_previo/js/app.js"></script>
    <script src="/build_previo/js/modernizr.js"></script>

<?php 
    
    incluirTemplate('footer');
?>