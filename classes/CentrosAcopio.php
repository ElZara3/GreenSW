<?php

namespace ProtoClase;

class CentrosAcopio extends ActiveRecord{

    protected static $tabla = 'centrosacopio';

    protected static $columnasDB = ['Id','Nombre','Horarios', 'Ubicacion'];

    private $Id;
    public $Nombre;
    public $Horarios;
    public $Ubicacion;

   public static function ExtraerAtributosEspecificos(...$Atributos): array {
        $Args = self::ExtraerAll(null, null, "Id");
        
        if (!empty($Args)) {
            $ArrayARetornar = [];
            if($Atributos)
                foreach ($Args as $fila) {
                    $temp = [];
                    foreach ($Atributos as $Atributo) {
                        if (array_key_exists($Atributo, $fila)) {
                            $temp[$Atributo] = $fila[$Atributo];
                        }
                    }
                    $ArrayARetornar[] = $temp;
                }
            else    
                return $Args;

            return $ArrayARetornar;
        }

        return [];
    }

    public static function ExtraerUnsoloDatoConWhere($Columna,$valor,$Dato){
        $Arr = self::ExtraerUnaTupla($Columna,$valor);
        return $Arr[$Dato] ?? null;
    }
}