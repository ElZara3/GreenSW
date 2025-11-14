<?php

namespace ProtoClase;

abstract class ActiveRecord{

    // Base DE DATOS
    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];

    // Errores
    protected static $errores = [];
    protected static $exitos = [];

    public static function getErrores() {
        return static::$errores;
    }
    
    public static function getExitos() {
        return static::$exitos;
    }
    
    // Definir la conexión a la BD
    public static function setDB($database) {
        self::$db = $database;
    }

    public static function ExtraerAll($columna = null, $valor = null, $orden = '', $operador = '='): array {      
        $query = "SELECT * FROM " . static::$tabla;
        
        // Solo agregar WHERE si se proporcionan columna y valor
        if ($columna !== null && $valor !== null) {
            $query .= " WHERE $columna $operador ?";
        }
        
        if (!empty($orden)) {
            $query .= " ORDER BY $orden";
        }

        $stmt = self::$db->prepare($query);
        if (!$stmt) {
            return [];
        }

        // Solo bind_param si hay parámetros WHERE
        if ($columna !== null && $valor !== null) {
            $stmt->bind_param("s", $valor);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        $filas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $filas ?: [];
    }


    public static function ExtraerUnaTupla($columna,$valor): array {
        // Preparar la consulta
        $query = "SELECT * FROM " . static::$tabla  ." WHERE $columna = ? Limit 1";

        $stmt = self::$db->prepare($query);
        if (!$stmt) {
            // Manejar error de prepare
            return [];
        }

        // Vincular el parámetro
        $stmt->bind_param("i", $valor);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener resultado
        $resultado = $stmt->get_result();

        // Obtener datos como arreglo asociativo
        $usuario = $resultado->fetch_assoc();

        // Cerrar statement
        $stmt->close();

        // Devolver el usuario o arreglo vacío si no existe
        return $usuario ? $usuario : [];
    }


    public static function buscarPorCamposOr(array $campos, $valor): ?array {
            //siempre string 
            $tipoParam = 's';
            // Construir la consulta dinámicamente
            $condiciones = [];
            foreach ($campos as $campo) {
                $condiciones[] = "$campo = ?";
            }
            
            $query = "SELECT * FROM " . static::$tabla . " WHERE " . implode(' OR ', $condiciones) . " LIMIT 1";
            
            $stmt = self::$db->prepare($query);

            // Crear el string de tipos para bind_param (repetir el tipo según la cantidad de campos)
            $tipos = str_repeat($tipoParam, count($campos));
            
            // Crear array con el valor repetido para cada campo
            $valores = array_fill(0, count($campos), $valor);
            
            // Bind parameters
            $stmt->bind_param($tipos, ...$valores);
            //Ejecutar
            $stmt->execute();
            
            $resultado = $stmt->get_result();
            $stmt->close();

            if ($resultado && $resultado->num_rows > 0) {
                return $resultado->fetch_assoc();
            }
            
            return null;
    }

    public function Actualizar($atributoWhere, $valorWhere, $operador = '=', $atributoWhere2 = null, $valorWhere2 = null, $operadorLogico = 'AND', ...$atributosActualizar): bool { 
        // Construir los SET de la query
        $atributosValidos = [];
        $valores = [];
        $tipos = '';
        
        foreach ($atributosActualizar as $atributo) {
            $atributosValidos[] = "$atributo = ?";
            $valores[] = $this->$atributo;
            
            // Determinar el tipo de dato para bind_param
            if (is_int($this->$atributo)) {
                $tipos .= 'i';
            } elseif (is_float($this->$atributo)) {
                $tipos .= 'd';
            } else {
                $tipos .= 's';
            }
        }
        
        // Construir la cláusula WHERE
        $whereClause = "$atributoWhere $operador ?";
        $valores[] = $valorWhere;
        
        // Determinar tipo del primer valor WHERE
        if (is_int($valorWhere)) {
            $tipos .= 'i';
        } elseif (is_float($valorWhere)) {
            $tipos .= 'd';
        } else {
            $tipos .= 's';
        }
        
        // Si hay una segunda condición WHERE
        if ($atributoWhere2 !== null && $valorWhere2 !== null) {
            $whereClause .= " $operadorLogico $atributoWhere2 = ?";
            $valores[] = $valorWhere2;
            
            // Determinar tipo del segundo valor WHERE
            if (is_int($valorWhere2)) {
                $tipos .= 'i';
            } elseif (is_float($valorWhere2)) {
                $tipos .= 'd';
            } else {
                $tipos .= 's';
            }
        }
        
        // Construir la query completa
        $query = "UPDATE " . static::$tabla . " SET " . implode(', ', $atributosValidos) . " WHERE $whereClause";
        
        // Preparar y ejecutar la consulta
        $stmt = self::$db->prepare($query);
        $stmt->bind_param($tipos, ...$valores);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                self::$exitos[] = "Actualización realizada correctamente.";
                $stmt->close();
                return true;
            } else {
                $stmt->close();
                self::$errores[] = "No se realizó ninguna actualización";
                return false;
            }
        } else {
            $stmt->close();
            self::$errores[] = "Error al ejecutar la consulta: " . self::$db->error;
            return false;
        }
    }

  
    protected function InsertarRegistro(): bool {
        // Sincronizamos dependiendo de la clase heredada
        $atributos = $this->SincrotizarAbtributosABase();
        
        // Si no hay atributos para insertar, retornar false
        if (empty($atributos)) {
            self::$errores[] = "No hay datos para insertar";
            return false;
        }
        
        // Preparar los nombres de columnas y placeholders
        $columnas = array_keys($atributos);
        $valores = array_values($atributos);
        $placeholders = str_repeat('?,', count($columnas) - 1) . '?';
        
        // Construir la consulta
        $query = "INSERT INTO " . static::$tabla . " (" . implode(',', $columnas) . ") VALUES ($placeholders)";
        
        // Preparar statement
        $stmt = self::$db->prepare($query);
        
        if (!$stmt) {
            self::$errores[] = "Error al preparar la consulta: " . self::$db->error;
            return false;
        }
        
        // Determinar tipos de datos para bind_param
        $tipos = '';
        foreach ($valores as $valor) {
            if (is_null($valor)) {
                $tipos .= 's'; // NULL se trata como string
            } elseif (is_int($valor)) {
                $tipos .= 'i';
            } elseif (is_float($valor)) {
                $tipos .= 'd';
            } else {
                $tipos .= 's';
            }
        }
        
        // Bind parameters
        $stmt->bind_param($tipos, ...$valores);
        
        // Ejecutar la consulta
        if ($stmt->execute()){
            $stmt->close();
            return true;
        }
        
        $stmt->close();
        return false;
    }

    // Identificar y unir los atributos de la BD
    public function SincrotizarAbtributosABase():array {
        $atributos = [];
        foreach(static::$columnasDB as $columna) {
            if($columna === 'Id') continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }
}