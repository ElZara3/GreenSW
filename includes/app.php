<?php 

require 'funciones.php';
require 'config/database.php';

require __DIR__ . '/../vendor/autoload.php';

use ProtoClase\ActiveRecord;

//pasarle la conexion
ActiveRecord::setDB(conectarDB());