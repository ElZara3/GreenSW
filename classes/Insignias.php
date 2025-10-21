<?php

    namespace ProtoClase;

class Insignias extends ActiveRecord{

    protected static $tabla = 'insignias';

    protected static $columnasDB = ['Id','Descripcion','KilosComposta'];

    private $Id;
    public $Descripcion;
    public $KilosComposta;

    
    public static function ExtraerInsigniasSinMensual():array{
        $insignias = self::ExtraerAll("Id",9,"Id","!=");
        return $insignias;
    }
}