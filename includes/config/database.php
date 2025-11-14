<?php 

function conectarDB() : mysqli {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../'); //Donde se encuentra el .env
    $dotenv->load();

    $host=$_ENV['DB_HOST'];
    $port=3306;
    $user=$_ENV['DB_USER'];
    $password=$_ENV['DB_PASS'];
    $dbname=$_ENV['DB_NAME'];
    
    $db = new mysqli($host, $user, $password, $dbname, $port);
    //$con->close();

    if(!$db) {
        echo "Error no se pudo conectar";
        exit;
    } 
    
    return $db;
    
}