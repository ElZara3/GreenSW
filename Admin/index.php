<?php
    require '../includes/app.php';
    
    session_start();
    //En cada php que sea de User y se le muestre se debe hacer la comprobacion
    verificarSesionActiva('Admin');

    $db = conectarDB();
        
    incluirTemplate('header');

    require_once 'ControlAdmin/admincentros.php';
    //require_once 'Visualizardatos/DatosprincipalesAdmin.php';
    require_once '../includes/SeccionesCompartidas/Datosprincipales.php';

    require_once '../includes/SeccionesCompartidas/Insignias.php';

?>

<script src="/build_previo/js/app.js"></script>
<script src="/build_previo/js/modernizr.js"></script>


<?php 
    incluirTemplate('footer');

?>