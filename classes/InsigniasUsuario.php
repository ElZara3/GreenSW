<?php

namespace ProtoClase;

class InsigniasUsuario extends ActiveRecord{

    protected static $tabla = 'insigniasusuario';

    protected static $columnasDB = ['Id','IdUsuario','IdInsignias', 'Fecha'];

    private $Id;
    public $IdUsuario;
    public $IdInsignias;
    public $Fecha;

    public static function ExtraerInsigniasUsuarioId($atributo,$valor): array{

        //hacemos la consulta ya preparada solo ingresamos valor
        $Arreglo_completo = self::ExtraerAll($atributo,$valor);
        if(!empty($Arreglo_completo)){
            //Despues unicamente extraemos los Id
            $insigniasDesbloqueadas = array_column($Arreglo_completo, "IdInsignias");
            return $insigniasDesbloqueadas;
        }
        return [];
    }

    public static function ExtraerConteoInsigniasEspeciales($idUsuario) {
        // Consulta para contar insignias especiales (ID = 9)
        $query = "SELECT COUNT(*) AS total 
                FROM insigniasusuario 
                WHERE IdUsuario = ? AND IdInsignias = 9";
        
        $stmt = self::$db->prepare($query);
        if ($stmt) {
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $fila = $resultado->fetch_assoc();
            return (int)($fila['total'] ?? 0);
        }
        
        return 0; // Si algo falla, devolver 0
    }
}