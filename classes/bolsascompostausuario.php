<?php

namespace ProtoClase;

class bolsascompostausuario extends ActiveRecord {

    protected static $tabla = 'bolsascompostausuario';
    protected static $columnasDB = ['IdUsuario','FechadeBolsa','NoBolsa','EstatusEntrega'];

    public $IdUsuario;
    public $FechadeBolsa;
    public $NoBolsa;
    public $EstatusEntrega;

    public function __construct(array $datos = []) {
            //Esta mal el constructor esta diseñado solo para una funcion para 
            //no modificar todo, esta por defecto 
            //Para que funcione se tiene que pasar correctamente el arreglo
            $props = [
            'IdUsuario' => null,
            'FechadeBolsa' => null,
            'NoBolsa' => null,
            'EstatusEntrega' => null,
        ];

        foreach ($props as $prop => $defecto) {
            $this->$prop = $datos[$prop] ?? $defecto;
        }
    }

    public static function ExtraerSolo3BolsasNum($IdUsuario){
        //La extraccion se basa en (atributo where,valorwhere,orden,operadorwhere)
        $Bolsas = self::ExtraerAll("IdUsuario",$IdUsuario,"NoBolsa");
        if($Bolsas){//Extraemos unicamente el numero de bolsa
            $Bolsas = array_column($Bolsas, "NoBolsa");
            //Tomar las ultimas 3 del arreglo
            $Bolsas = array_slice($Bolsas,-3);
        }
        
        return $Bolsas;
    }

}
