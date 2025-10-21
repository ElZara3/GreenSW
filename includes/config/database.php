<?php 

function conectarDB() : mysqli {
    $host="localhost";
    $port=3306;
    $user="root";
    $password="root";
    $dbname="vivecomposta";
    
    $db = new mysqli($host, $user, $password, $dbname, $port);
    //$con->close();

    if(!$db) {
        echo "Error no se pudo conectar";
        exit;
    } 
    
    return $db;
    
}