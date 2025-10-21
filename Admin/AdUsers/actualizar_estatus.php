<?php
  
    session_start();
    include '../../includes/app.php';
    use ProtoClase\bolsascompostausuario;
    //pagina protegida
    verificarSesionActiva('Admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'])) {
    $idUsuario = intval($_POST['id_usuario']);
    $estatusRecibidos = $_POST['estatus_bolsas'] ?? [];
    
    //Obtenemos las primeras 3 bolsas solo numero
    $ultimasBolsas = bolsascompostausuario::ExtraerSolo3BolsasNum($idUsuario);
    if($ultimasBolsas){
        foreach ($ultimasBolsas as $bolsa) {
            $nuevoEstatus = isset($estatusRecibidos[$bolsa]) ? 1 : 0;
            echo "Bolsa".$bolsa." = ".$nuevoEstatus;
            //Conesto Se verifica solo se hace la actualizacion
            //Creamos el objeto ya que actualizar es una funcion de la clase
            $ObjetoAuxiliar = new bolsascompostausuario(["EstatusEntrega" =>$nuevoEstatus]);
            $ObjetoAuxiliar->Actualizar("IdUsuario",$idUsuario,"=","NoBolsa",$bolsa,"AND","EstatusEntrega");
        }

    // Recuperar datos del formulario ocultos y guardarlos en sesión
    if (isset($_POST['usuario_previo_serializado'], $_POST['progreso_anterior'], 
                $_POST['cubetas_anteriores'], $_POST['meta_anterior'])
        ) {
            $_SESSION['usuario_previo'] = unserialize($_POST['usuario_previo_serializado']);
            $_SESSION['progreso_anterior'] = $_POST['progreso_anterior'];
            $_SESSION['cubetasAnteriores'] = $_POST['cubetas_anteriores'];
            $_SESSION['metaAnterior'] = $_POST['meta_anterior'];

            $_SESSION['mensajeActualizacion'] = '<h3 class="mensajeEstatusDeBolsaAtualizado" >¡Estatus de bolsas entregadas actualizado!</h3>';
        } else {
            $_SESSION['mensajeActualizacion'] = 'Faltan datos ocultos para conservar estado.';
        }
        header("Location: busqueda.php");
        exit;
    } 
}else {
    $_SESSION['mensajeActualizacion'] = 'Datos incompletos para la actualización.';
    header("Location: busqueda.php");
    exit;
}
