<?php

namespace ProtoClase;

class Usuario extends ActiveRecord{

    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['Id','Nombre','ApPat','ApMat','Telefono','FNacimiento','FRegistro',
                                    'Direccion','Rol','Correo','IdCentroAcopio','CubetasTot','Contrasena',
                                    'InformacionUsuario'];

    public $Id;
    public $Nombre;
    public $ApPat;
    public $ApMat;
    public $Telefono;
    public $FNacimiento;
    public $FRegistro;
    public $Direccion;
    public $Rol;
    public $Correo;
    public $IdCentroAcopio;
    public $CubetasTot;
    protected $Contrasena;
    public $InformacionUsuario;
    

    // Constructor recibe array opcional con datos para asignar propiedades
    public function __construct(array $datos = []) {
        $props = [
            'Id'            => null,
            'Nombre'        => '',
            'ApPat'         => null,
            'ApMat'         => null,
            'Telefono'      => '',
            'FNacimiento'   => null,
            'FRegistro'     => null,
            'Direccion'     => null,
            'Rol'           => 1,
            'Correo'        => null,
            'IdCentroAcopio'=> null,
            'CubetasTot'    => 0,
            'Contrasena'    => null,
            'InformacionUsuario' => null
        ];

        foreach ($props as $prop => $defecto) {
            $this->$prop = $datos[$prop] ?? $defecto;
        }
    }

    public static function ExtraerRolUser(): ?string {
        
        // Verificar que existe el usuario en sesión
        if(!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
            return null;
        }
        //Extraemos al usuario con una funcion padre
        $Fila = self::ExtraerUnaTupla('Id',$_SESSION['usuario']);

        if($Fila){//si nos da un arreglo no vacio
            return $Fila['Rol'] ?? null;
        }        
        return null;
    }

    public static function verificarExistenciaUsuario(
        string $emailOrPhone, 
        string $password
        ): bool {

        $camposLogin = ['Correo', 'Telefono'];
        $campoPassword = 'Contrasena';

        $emailOrPhone = mb_strtolower(trim($emailOrPhone), 'UTF-8');
        
        // Usar la función general para buscar el usuario
        $usuario = self::buscarPorCamposOr($camposLogin, $emailOrPhone);
        
        if (!$usuario) {
            self::$errores[] = "El usuario no existe.";
            return false;
            }

        // Verificar contraseña
        if (!password_verify($password, $usuario[$campoPassword])) {
            self::$errores[] = "La contraseña es incorrecta.";
            return false;
            }

        // Establecer sesión
        $_SESSION['usuario'] = $usuario['Id'];
        $_SESSION['login'] = true;
        $_SESSION['ultimo_acceso'] = time();
        $_SESSION['rol'] = $usuario['Rol'] ?? null;
        
        return true;
    }

    public static function ExtraerNombreCompletoyId(): array{
        $Args = self::ExtraerAll(null, null, "Nombre");
            
            if (!empty($Args)) {
                $ArrayARetornar = [];
                foreach ($Args as $fila) {
                    $nombreCompleto = $fila['Nombre'] . ' ' . $fila['ApPat'] . ' ' . $fila['ApMat'];
                    $ArrayARetornar[] = ['Id' => $fila['Id'],
                        'NombreCompleto' => $nombreCompleto];

                }

                return $ArrayARetornar;
            }

            return [];
    }

    public function ValidarCambioContrasena($contrasena_actual, $nueva_contrasena, $confirmar_nueva_contrasena): bool {
        
        // Limpiar errores previos específicos de esta operación
        $errores_cambio = [];
        
        // Validar que todos los campos estén completos
        if (empty($contrasena_actual)) {
            $errores_cambio[] = "La contraseña actual es obligatoria.";
        }
        
        if (empty($nueva_contrasena)) {
            $errores_cambio[] = "La nueva contraseña es obligatoria.";
        }
        
        if (empty($confirmar_nueva_contrasena)) {
            $errores_cambio[] = "Debe confirmar la nueva contraseña.";
        }
        
        // Si faltan campos, agregar errores y retornar false
        if (!empty($errores_cambio)) {
            self::$errores = array_merge(self::$errores, $errores_cambio);
            return false;
        }
        
        // Validar que las nuevas contraseñas coincidan
        if ($nueva_contrasena !== $confirmar_nueva_contrasena) {
            self::$errores[] = "Las nuevas contraseñas no coinciden.";
            return false;
        }
        
        // Validar longitud mínima de la nueva contraseña
        if (strlen($nueva_contrasena) < 6) {
            self::$errores[] = "La nueva contraseña debe tener al menos 6 caracteres.";
            return false;
        }
                
        // Verificar que la contraseña actual sea correcta
        if (!password_verify($contrasena_actual, $this->Contrasena)) {
            self::$errores[] = "La contraseña actual es incorrecta.";
            return false;
        }
        
        // Verificar que la nueva contraseña sea diferente a la actual
        if (password_verify($nueva_contrasena, $this->Contrasena)) {
            self::$errores[] = "La nueva contraseña debe ser diferente a la actual.";
            return false;
        }
        
        // Si llegamos aquí, todas las validaciones pasaron
        // Preparar la nueva contraseña encriptada para la actualización
        $this->Contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        
        return true;
    }
    public function ActualizarDatosUsuario(){
        self::ConversionDeDatosParaInsercionABD();
        //Por ultimo hacemos la actualizacion de datos especificos
        self::Actualizar("Id",$this->Id,"=",null,null,null,"Nombre","ApPat","ApMat",
        "Telefono","FNacimiento","Direccion","Correo","IdCentroAcopio");
        if(empty(self::$errores))
            return true;
    }
    public function ReestablecerContraseña(){
        //Es una funcion con la clase ya creada por loq ue solo 
        //hacemos la busqueda desde aqui por su id
        $UsuarioCompleto = self::ExtraerUnaTupla("Id",$this->Id);
        //Establecemos los datos para la creacion
        $this->Nombre = $UsuarioCompleto['Nombre'];
        $this->Telefono = $UsuarioCompleto['Telefono'];

        //Hacemos la creacion de la contraseña nueva
        $this->Contrasena = password_hash(self::ExtraerContraseñaGeneradaAutom(), PASSWORD_BCRYPT);

        //Por ultimo solo se actualiza ese atributo de contraseña
        if(!$this->Actualizar("Id",$this->Id,"=",null,null,null,"Contrasena")){//Si nos manda un false entonces
            self::$errores[] = "No se Actualizo la contraseña correctamente";
            return false;
        }
        return true;
    }

    public function ValidarInsercionUsuario(): bool {
        // Validaciones
        if (empty($this->Nombre)) {
            self::$errores[] = "El nombre es obligatorio";
            return false;
        }
        
        // Validación del teléfono (10-13 dígitos)
        if (empty($this->Telefono)) {
            self::$errores[] = "El número de teléfono es obligatorio";
            return false;
        } elseif (!ctype_digit($this->Telefono)) {
            self::$errores[] = "El número de teléfono solo debe contener números";
        } elseif (strlen($this->Telefono) < 10 || strlen($this->Telefono) > 13) {
            self::$errores[] = "El número de teléfono debe tener entre 10 y 13 dígitos";
        }
        
        // Validación de fecha de nacimiento (solo validar que no tenga más de 120 años)
        // En caso de que no esté vacío el campo (por lo regular está vacío)
        if (!empty($this->FNacimiento)) {
            $fecha_actual = new \DateTime();//Usamos slash invertivo por ser clases globales 
            $fecha_nacimiento_dt = \DateTime::createFromFormat('Y-m-d', $this->FNacimiento);
            
            if (!$fecha_nacimiento_dt) {
                self::$errores[] = "La fecha de nacimiento no es válida";
            } else {
                $diferencia = $fecha_actual->diff($fecha_nacimiento_dt);
                if ($diferencia->y < 18) {
                    self::$errores[] = "El usuario debe tener al menos 18 años para registrarse";
                }
                if ($diferencia->y > 120) {
                    self::$errores[] = "La edad no puede ser mayor a 120 años";
                }
            }
        }
        
        // Verificación si el número de teléfono existe
        $UsuarioExistente = self::ExtraerUnaTupla("Telefono", $this->Telefono);
        if (!empty($UsuarioExistente)) {//verificamos que se extrajo una tupla
            //En caso de que sea actualizacion 
            //verificar que el numero es de el usuario, porque se va actualizar
            if($UsuarioExistente['Id'] != $this->Id)
                self::$errores[] = "El número de teléfono ya está registrado";
        }
        
        // Si hay errores, retornar false
        if (!empty(self::$errores)) {
            return false;
        }
        
        // Si llegamos hasta abajo se regresa true
        return true;
    }

    public function InsertarUsuario() {
        self::ConversionDeDatosParaInsercionABD();

        // 3. Fecha de registro
        $this->FRegistro = date('Y-m-d');

        $ContraseñaPorDefecto = self::ExtraerContraseñaGeneradaAutom();
        $this->Contrasena = password_hash($ContraseñaPorDefecto, PASSWORD_BCRYPT);

        // 6. Insertar en la base de datos
        if(!self::InsertarRegistro()){
            self::$errores[] = "Registro No insertado, falla en la inserción a la base";
            return ;
        }

    }

    public function ConversionDeDatosParaInsercionABD(){
        // 1. Convertir vacíos a NULL
        foreach (self::$columnasDB as $columna) {
            if ($this->{$columna} === '') {
                $this->{$columna} = null;
            }
        }

        // 2. Convertir a mayúsculas ciertos campos (si no son null)
        $camposMayus = ['Nombre', 'ApPat', 'ApMat', 'Direccion'];
        foreach ($camposMayus as $columna) {
            if (!is_null($this->{$columna})) {
                $this->{$columna} = mb_strtoupper($this->{$columna}, 'UTF-8');
            }
        }

        //4.Correo a minuscula
        if($this->Correo !== null)
            $this->Correo = mb_strtolower($this->Correo, 'UTF-8');

    }

    //Getters y Setters
    
    // En la clase Usuario
    public function ExtraerContraseñaGeneradaAutom():String{
        // 5. Crear contraseña a partir de nombre y teléfono
        $nombreMin = strtolower(substr($this->Nombre, 0, 3));
        $telefonoUltimos3 = substr($this->Telefono, -3);
        $Contraseña = $nombreMin.$telefonoUltimos3;

        return $Contraseña;
        }
    }