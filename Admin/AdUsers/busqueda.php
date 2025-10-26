<?php
    //incluir las funciones
    require_once '../../includes/app.php';
    // Iniciar sesión 
    session_start();
    //protegerPagina
    verificarSesionActiva('Admin');
    //Incluir conexion a db, ya que no se cambio a POO
    $db = conectarDB();

// Variables para la búsqueda
$resultadosBusqueda = [];
$mensajeBusqueda = '';
$totalResultados = 0;

// Variables para el usuario seleccionado
$usuarioSeleccionado = $_SESSION['usuario_previo'] ?? null;
unset($_SESSION['usuario_previo']); // así ya no se usa después por accidente
$progresoCubetas = $_SESSION['progreso_anterior'] ?? 0;
$cubetasRestantes = $_SESSION['cubetasAnteriores'] ?? 0;
$metaAlcanzada = isset($_SESSION['metaAnterior']) ? $_SESSION['metaAnterior'] : false;
$mensajeActualizacion = '';
$rolActualizado = false;
$InfoUsuarioActual = $_SESSION['InfodeUsuario'] ?? null;

unset($_SESSION['progreso_anterior'], $_SESSION['cubetasAnteriores'], $_SESSION['metaAnterior'], $_SESSION['InfodeUsuario']);

// Recuperar el centro de acopio seleccionado de la sesión o URL
$centroAcopioId = isset($_GET['centro']) ? (int)$_GET['centro'] : (isset($_SESSION['centro_actual']) ? $_SESSION['centro_actual'] : null);

// Si viene de seleccionar un centro, guardarlo en sesión
if (isset($_GET['centro'])) {
    $_SESSION['centro_actual'] = $centroAcopioId;
}

// Procesar la búsqueda cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscarUsuario'])) {
    $terminoBusqueda = trim($_POST['terminoBusqueda'] ?? '');
    $usuarioSeleccionado = null;
    if (!empty($terminoBusqueda)) {
        // Preparar el término para la búsqueda con comodines
        $terminoBusquedaLike = '%' . s($terminoBusqueda, ENT_QUOTES, 'UTF-8') . '%';

        // Construir la consulta base - MODIFICACIÓN: Incluir el campo Rol y join con centros de acopio
        $query = "SELECT u.Id, u.Nombre, u.ApPat, u.ApMat, u.CubetasTot, u.IdCentroAcopio, u.Rol, 
                         ca.Nombre as NombreCentro
                FROM usuarios u 
                LEFT JOIN centrosacopio ca ON u.IdCentroAcopio = ca.Id
                WHERE (u.Nombre LIKE ? OR u.ApPat LIKE ? OR u.ApMat LIKE ? OR u.Rol LIKE ?
                      OR u.Telefono LIKE ? OR u.Correo LIKE ? 
                      OR CONCAT(u.Nombre, ' ', u.ApPat, ' ', u.ApMat) LIKE ?)";

        // Parámetros base para la consulta
        $types = "sssssss";
        $params = [
            $terminoBusquedaLike,
            $terminoBusquedaLike,
            $terminoBusquedaLike,
            $terminoBusquedaLike,
            $terminoBusquedaLike,
            $terminoBusquedaLike,
            $terminoBusquedaLike
        ];

        // Ordenar: primero los del centro actual, luego por nombre
        $query .= " ORDER BY 
                   CASE WHEN u.IdCentroAcopio = ? THEN 0 ELSE 1 END,
                   u.Nombre";
        
        $types .= "i";
        $params[] = $centroAcopioId;

        $stmt = mysqli_prepare($db, $query);

        if ($stmt) {
            // Vincular parámetros a la consulta
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            // Ejecutar la consulta
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            // Procesar los resultados
            if ($resultado) {
                $totalResultados = mysqli_num_rows($resultado);

                if ($totalResultados > 0) {
                    $resultadosBusqueda = [];
                    $usuariosCentroActual = [];
                    $otrosUsuarios = [];

                    // Separar usuarios por centro de acopio
                    while ($usuario = mysqli_fetch_assoc($resultado)) {
                        if ($usuario['IdCentroAcopio'] == $centroAcopioId) {
                            $usuariosCentroActual[] = $usuario;
                        } else {
                            $otrosUsuarios[] = $usuario;
                        }
                    }

                    // Obtener el nombre del centro actual
                    $nombreCentroActual = '';
                    if (!empty($usuariosCentroActual)) {
                        $nombreCentroActual = $usuariosCentroActual[0]['NombreCentro'];
                    } else if ($centroAcopioId) {
                        // Si no hay usuarios del centro pero tenemos ID, consultar el nombre
                        $queryCentro = "SELECT Nombre FROM centrosacopio WHERE Id = ?";
                        $stmtCentro = mysqli_prepare($db, $queryCentro);
                        if ($stmtCentro) {
                            mysqli_stmt_bind_param($stmtCentro, 'i', $centroAcopioId);
                            mysqli_stmt_execute($stmtCentro);
                            $resultCentro = mysqli_stmt_get_result($stmtCentro);
                            if ($rowCentro = mysqli_fetch_assoc($resultCentro)) {
                                $nombreCentroActual = $rowCentro['Nombre'];
                            }
                            mysqli_stmt_close($stmtCentro);
                        }
                    }

                    // Combinar resultados para el total
                    $resultadosBusqueda = array_merge($usuariosCentroActual, $otrosUsuarios);
                } else {
                    $mensajeBusqueda = "No se encontraron usuarios que coincidan con '$terminoBusqueda'";
                }
            } else {
                $mensajeBusqueda = "Error al realizar la búsqueda: " . mysqli_error($db);
            }

            mysqli_stmt_close($stmt);
        } else {
            $mensajeBusqueda = "Error al preparar la consulta: " . mysqli_error($db);
        }
    } else {
        $mensajeBusqueda = "Por favor, ingrese un término de búsqueda";
    }
}

// Procesar el formulario de selección de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seleccionarUsuario'])) {
    $idUsuario = (int)filter_var($_POST['idUsuarioSeleccionado'], FILTER_SANITIZE_NUMBER_INT);

    if ($idUsuario > 0) {
        // Consultar los datos del usuario - MODIFICACIÓN: Incluir el campo Rol
        $query = "SELECT Id, Nombre, ApPat, ApMat, CubetasTot, IdCentroAcopio, Rol, InformacionUsuario
                 FROM usuarios 
                 WHERE Id = ?";

        $stmt = mysqli_prepare($db, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if ($resultado && mysqli_num_rows($resultado) > 0) {
                $usuarioSeleccionado = mysqli_fetch_assoc($resultado);

                //Aqui mismo guardar la informacion del usuario en una variable de sesion
                $InfoUsuarioActual = $usuarioSeleccionado['InformacionUsuario'];

                // Calcular progreso hacia la meta (cada 10 cubetas)
                $totalCubetas = $usuarioSeleccionado['CubetasTot'];
                $progresoCubetas = $totalCubetas % 10;
                $cubetasRestantes = 10 - $progresoCubetas;
                if ($cubetasRestantes == 10 && $totalCubetas > 0) {
                    $cubetasRestantes = 0; // Ya alcanzó la meta exacta
                }

                // Verificar si se alcanzó una meta exacta (0 cubetas restantes)
                $metaAlcanzada = ($cubetasRestantes == 0 && $totalCubetas > 0);
            }

            mysqli_stmt_close($stmt);
        }
    }
}

// Procesar el formulario para aumentar cubetas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aumentarCubetas'])) {
    $idUsuario = (int)filter_var($_POST['idUsuario'], FILTER_SANITIZE_NUMBER_INT);
    $cubetasAgregar = (int)filter_var($_POST['cubetasAgregar'], FILTER_SANITIZE_NUMBER_INT);
    $idCentroAcopio = (int)$centroAcopioId;

    // Si no hay centro de acopio especificado, usar el del usuario
    if (!$idCentroAcopio) {
        $query = "SELECT IdCentroAcopio FROM usuarios WHERE Id = ?";
        $stmt = mysqli_prepare($db, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $idCentroAcopio = $row['IdCentroAcopio'];
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($idUsuario > 0 && $cubetasAgregar > 0 && $idCentroAcopio > 0) {
        // Iniciar transacción
        mysqli_begin_transaction($db);

        try {
            // 1. Actualizar cubetas del usuario
            $queryUpdate = "UPDATE usuarios SET CubetasTot = CubetasTot + ? WHERE Id = ?";
            $stmtUpdate = mysqli_prepare($db, $queryUpdate);

            if (!$stmtUpdate) {
                throw new Exception("Error al preparar la consulta de actualización: " . mysqli_error($db));
            }

            mysqli_stmt_bind_param($stmtUpdate, 'ii', $cubetasAgregar, $idUsuario);

            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Error al actualizar cubetas: " . mysqli_stmt_error($stmtUpdate));
            }

            if (mysqli_stmt_affected_rows($stmtUpdate) == 0) {
                throw new Exception("No se pudo actualizar el usuario. Verifique el ID");
            }

            mysqli_stmt_close($stmtUpdate);

            // 2. Registrar la entrega en la tabla visitausuario
            $queryVisita = "INSERT INTO visitasusuario (IdUsuario, IdCentroAcopio, Fecha, CubetasEntregadas) 
                           VALUES (?, ?, NOW(), ?)";
            $stmtVisita = mysqli_prepare($db, $queryVisita);

            if (!$stmtVisita) {
                throw new Exception("Error al preparar el registro de visita: " . mysqli_error($db));
            }

            mysqli_stmt_bind_param($stmtVisita, 'iii', $idUsuario, $idCentroAcopio, $cubetasAgregar);

            if (!mysqli_stmt_execute($stmtVisita)) {
                throw new Exception("Error al registrar visita: " . mysqli_stmt_error($stmtVisita));
            }

            mysqli_stmt_close($stmtVisita);

            // Confirmar transacción
            mysqli_commit($db);

            // INICIALIZAR VARIABLE DE MENSAJES
            $mensajeActualizacion = "<h3 class='mensaje_Cubetas_Agregadas'>¡Se agregaron $cubetasAgregar cubetas correctamente!</h3>";

            // Proceso de insignias (código existente...)
            $queryGetTotal = "SELECT CubetasTot FROM usuarios WHERE Id = ?";
            $stmtGetTotal = mysqli_prepare($db, $queryGetTotal);
            
            if ($stmtGetTotal) {
                mysqli_stmt_bind_param($stmtGetTotal, 'i', $idUsuario);
                mysqli_stmt_execute($stmtGetTotal);
                $resultadoTotal = mysqli_stmt_get_result($stmtGetTotal);
                
                if ($row = mysqli_fetch_assoc($resultadoTotal)) {
                    $totalCubetas = $row['CubetasTot'];
                    
                    // Calcular si se alcanzó una meta
                    $progresoCubetas = $totalCubetas % 10;
                    $metaAlcanzada = ($progresoCubetas == 0 && $totalCubetas > 0);
                    
                    // AQUÍ ESTÁ LA CORRECCIÓN: Convertir cubetas a kilogramos (6.5 kg por cubeta)
                    $kilosComposta = $totalCubetas * 6.5;
                    
                    // Consultar las insignias disponibles para asignar según los kilos de composta
                    $queryInsignias = "SELECT * FROM insignias WHERE KilosComposta <= ? ORDER BY KilosComposta DESC";
                    $stmtInsignias = mysqli_prepare($db, $queryInsignias);
                    
                    if ($stmtInsignias) {
                        mysqli_stmt_bind_param($stmtInsignias, 'd', $kilosComposta); // Cambiado a 'd' para soportar decimales
                        mysqli_stmt_execute($stmtInsignias);
                        $resultadoInsignias = mysqli_stmt_get_result($stmtInsignias);
                        
                        // Procesar cada insignia elegible
                        while ($insignia = mysqli_fetch_assoc($resultadoInsignias)) {
                            $idInsignia = $insignia['Id'];
                            $descripcionInsignia = $insignia['Descripcion'];
                            
                            // Verificar si es la insignia mensual (ID 9)
                            if ($idInsignia == 9) {
                                // Para la insignia mensual, verificar si ya se ha asignado este mes
                                $queryVerificarMensual = "SELECT * FROM insigniasusuario 
                                                        WHERE IdUsuario = ? AND IdInsignias = 9 
                                                        AND MONTH(Fecha) = MONTH(CURRENT_DATE()) 
                                                        AND YEAR(Fecha) = YEAR(CURRENT_DATE())";
                                $stmtVerificarMensual = mysqli_prepare($db, $queryVerificarMensual);
                                
                                if ($stmtVerificarMensual) {
                                    mysqli_stmt_bind_param($stmtVerificarMensual, 'i', $idUsuario);
                                    mysqli_stmt_execute($stmtVerificarMensual);
                                    $resultadoVerificarMensual = mysqli_stmt_get_result($stmtVerificarMensual);
                                    
                                    // Si no hay resultados, asignar la insignia mensual
                                    if (mysqli_num_rows($resultadoVerificarMensual) == 0 && $metaAlcanzada) {
                                        $queryAsignarInsignia = "INSERT INTO insigniasusuario (IdUsuario, IdInsignias, Fecha) 
                                                               VALUES (?, ?, NOW())";
                                        $stmtAsignarInsignia = mysqli_prepare($db, $queryAsignarInsignia);
                                        
                                        if ($stmtAsignarInsignia) {
                                            mysqli_stmt_bind_param($stmtAsignarInsignia, 'ii', $idUsuario, $idInsignia);
                                            mysqli_stmt_execute($stmtAsignarInsignia);
                                            mysqli_stmt_close($stmtAsignarInsignia);
                                            
                                            // CONCATENAR mensaje de insignia
                                            $mensajeActualizacion .= "<h2 class='mensaje-insignia-asignada'>¡Se ha otorgado la insignia mensual: $descripcionInsignia!</h2>";
                                        }
                                    }
                                    mysqli_stmt_close($stmtVerificarMensual);
                                }
                            } else {
                                // Para otras insignias, verificar si ya se ha asignado alguna vez
                                $queryVerificarInsignia = "SELECT * FROM insigniasusuario 
                                                         WHERE IdUsuario = ? AND IdInsignias = ?";
                                $stmtVerificarInsignia = mysqli_prepare($db, $queryVerificarInsignia);
                                
                                if ($stmtVerificarInsignia) {
                                    mysqli_stmt_bind_param($stmtVerificarInsignia, 'ii', $idUsuario, $idInsignia);
                                    mysqli_stmt_execute($stmtVerificarInsignia);
                                    $resultadoVerificarInsignia = mysqli_stmt_get_result($stmtVerificarInsignia);
                                    
                                    // Si no hay resultados, asignar la nueva insignia
                                    if (mysqli_num_rows($resultadoVerificarInsignia) == 0) {
                                        $queryAsignarInsignia = "INSERT INTO insigniasusuario (IdUsuario, IdInsignias, Fecha) 
                                                               VALUES (?, ?, NOW())";
                                        $stmtAsignarInsignia = mysqli_prepare($db, $queryAsignarInsignia);
                                        
                                        if ($stmtAsignarInsignia) {
                                            mysqli_stmt_bind_param($stmtAsignarInsignia, 'ii', $idUsuario, $idInsignia);
                                            mysqli_stmt_execute($stmtAsignarInsignia);
                                            mysqli_stmt_close($stmtAsignarInsignia);
                                            
                                            // CONCATENAR mensaje de insignia
                                            $mensajeActualizacion .= "<h2 class='mensaje-insignia-asignada'>¡Se ha otorgado una nueva insignia: $descripcionInsignia!</h2>";
                                        }
                                    }
                                    mysqli_stmt_close($stmtVerificarInsignia);
                                }
                            }
                        }
                        mysqli_stmt_close($stmtInsignias);
                    }
                }
                mysqli_stmt_close($stmtGetTotal);
            }

            // 3. Consultar los datos actualizados del usuario - MODIFICACIÓN: Incluir el campo Rol
            $queryRefresh = "SELECT Id, Nombre, ApPat, ApMat, CubetasTot, IdCentroAcopio, Rol, InformacionUsuario
                           FROM usuarios 
                           WHERE Id = ?";

            $stmtRefresh = mysqli_prepare($db, $queryRefresh);

            if (!$stmtRefresh) {
                throw new Exception("Error al preparar la consulta de actualización: " . mysqli_error($db));
            }

            mysqli_stmt_bind_param($stmtRefresh, 'i', $idUsuario);
            if (!mysqli_stmt_execute($stmtRefresh)) {
                throw new Exception("Error al obtener datos actualizados: " . mysqli_stmt_error($stmtRefresh));
            }

            $resultadoRefresh = mysqli_stmt_get_result($stmtRefresh);

            if ($resultadoRefresh && mysqli_num_rows($resultadoRefresh) > 0) {
                $usuarioSeleccionado = mysqli_fetch_assoc($resultadoRefresh);

                // Recalcular progreso
                $totalCubetas = $usuarioSeleccionado['CubetasTot'];
                $progresoCubetas = $totalCubetas % 10;
                $cubetasRestantes = 10 - $progresoCubetas;
                if ($cubetasRestantes == 10 && $totalCubetas > 0) {
                    $cubetasRestantes = 0; // Ya alcanzó la meta exacta
                }

                // Verificar si se alcanzó una meta
                $metaAlcanzada = ($cubetasRestantes == 0 && $totalCubetas > 0);

            } else {
                throw new Exception("No se pudo obtener la información actualizada del usuario.");
            }

            mysqli_stmt_close($stmtRefresh);
            $_SESSION['mensajeActualizacion'] = $mensajeActualizacion;
            $_SESSION['usuario_previo'] = $usuarioSeleccionado;
            $_SESSION['progreso_anterior'] = $progresoCubetas;
            $_SESSION['cubetasAnteriores'] = $cubetasRestantes;
            $_SESSION['metaAnterior'] = $metaAlcanzada;

            /* Variable de info de usuario que se regresa */
            $_SESSION['InfodeUsuario'] = $usuarioSeleccionado['InformacionUsuario'];

            header("Location: busqueda.php");
            exit();
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            mysqli_rollback($db);
            $mensajeActualizacion = "Error: " . $e->getMessage();

            // Recargar datos del usuario en caso de error
            if ($idUsuario > 0) {
                $query = "SELECT Id, Nombre, ApPat, ApMat, CubetasTot, IdCentroAcopio, Rol FROM usuarios WHERE Id = ?";
                $stmt = mysqli_prepare($db, $query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
                    mysqli_stmt_execute($stmt);
                    $resultado = mysqli_stmt_get_result($stmt);
                    if ($resultado && mysqli_num_rows($resultado) > 0) {
                        $usuarioSeleccionado = mysqli_fetch_assoc($resultado);
                        $totalCubetas = $usuarioSeleccionado['CubetasTot'];
                        $progresoCubetas = $totalCubetas % 10;
                        $cubetasRestantes = 10 - $progresoCubetas;
                        if ($cubetasRestantes == 10 && $totalCubetas > 0) {
                            $cubetasRestantes = 0;
                        }
                        $metaAlcanzada = ($cubetasRestantes == 0 && $totalCubetas > 0);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } else {
        $mensajeActualizacion = "Error: Datos de entrada inválidos para aumentar cubetas.";
    }
}

//Procesar el formulario para modificar cubetas DIRECTAMENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificarCubetas'])) {
    $idUsuario = (int)filter_var($_POST['idUsuario'], FILTER_SANITIZE_NUMBER_INT);
    $nuevasCubetas = (int)filter_var($_POST['nuevasCubetas'], FILTER_SANITIZE_NUMBER_INT);
    $idCentroAcopio = (int)$centroAcopioId;

    if ($idUsuario > 0 && $nuevasCubetas >= 0) {
        // Iniciar transacción
        mysqli_begin_transaction($db);

        try {
            // Obtener las cubetas actuales para calcular la diferencia
            $queryActual = "SELECT CubetasTot FROM usuarios WHERE Id = ?";
            $stmtActual = mysqli_prepare($db, $queryActual);
            
            if (!$stmtActual) {
                throw new Exception("Error al preparar la consulta actual: " . mysqli_error($db));
            }

            mysqli_stmt_bind_param($stmtActual, 'i', $idUsuario);
            mysqli_stmt_execute($stmtActual);
            $resultActual = mysqli_stmt_get_result($stmtActual);
            
            if ($row = mysqli_fetch_assoc($resultActual)) {
                $cubetasActuales = $row['CubetasTot'];
                $diferencia = $nuevasCubetas - $cubetasActuales;
            } else {
                throw new Exception("Usuario no encontrado.");
            }
            mysqli_stmt_close($stmtActual);

            // 1. Actualizar cubetas del usuario directamente
            $queryUpdate = "UPDATE usuarios SET CubetasTot = ? WHERE Id = ?";
            $stmtUpdate = mysqli_prepare($db, $queryUpdate);

            if (!$stmtUpdate) {
                throw new Exception("Error al preparar la consulta de actualización: " . mysqli_error($db));
            }

            mysqli_stmt_bind_param($stmtUpdate, 'ii', $nuevasCubetas, $idUsuario);

            if (!mysqli_stmt_execute($stmtUpdate)) {
                throw new Exception("Error al modificar cubetas: " . mysqli_stmt_error($stmtUpdate));
            }

            mysqli_stmt_close($stmtUpdate);

            // 2. NUEVA LÓGICA: Manejar registro de visitas (un registro por día)
            if ($idCentroAcopio > 0) {
                // Verificar si ya existe un registro para hoy
                $queryVerificarVisitaHoy = "SELECT Id, CubetasEntregadas FROM visitasusuario 
                                           WHERE IdUsuario = ? AND IdCentroAcopio = ? 
                                           AND DATE(Fecha) = CURDATE()";
                $stmtVerificarVisita = mysqli_prepare($db, $queryVerificarVisitaHoy);
                
                if ($stmtVerificarVisita) {
                    mysqli_stmt_bind_param($stmtVerificarVisita, 'ii', $idUsuario, $idCentroAcopio);
                    mysqli_stmt_execute($stmtVerificarVisita);
                    $resultadoVisita = mysqli_stmt_get_result($stmtVerificarVisita);
                    
                    if ($filaVisita = mysqli_fetch_assoc($resultadoVisita)) {
                        // Ya existe un registro para hoy, actualizar la cantidad
                        $idVisitaExistente = $filaVisita['Id'];
                        $cubetasRegistradasHoy = $filaVisita['CubetasEntregadas'];
                        $nuevasCubetasRegistradas = $cubetasRegistradasHoy + $diferencia;
                        
                        // Solo actualizar si la nueva cantidad es mayor a 0
                        if ($nuevasCubetasRegistradas > 0) {
                            $queryActualizarVisita = "UPDATE visitasusuario 
                                                    SET CubetasEntregadas = ? 
                                                    WHERE Id = ?";
                            $stmtActualizarVisita = mysqli_prepare($db, $queryActualizarVisita);
                            
                            if ($stmtActualizarVisita) {
                                mysqli_stmt_bind_param($stmtActualizarVisita, 'ii', $nuevasCubetasRegistradas, $idVisitaExistente);
                                mysqli_stmt_execute($stmtActualizarVisita);
                                mysqli_stmt_close($stmtActualizarVisita);
                            }
                        } else {
                            // Si la nueva cantidad sería 0 o negativa, eliminar el registro
                            $queryEliminarVisita = "DELETE FROM visitasusuario WHERE Id = ?";
                            $stmtEliminarVisita = mysqli_prepare($db, $queryEliminarVisita);
                            
                            if ($stmtEliminarVisita) {
                                mysqli_stmt_bind_param($stmtEliminarVisita, 'i', $idVisitaExistente);
                                mysqli_stmt_execute($stmtEliminarVisita);
                                mysqli_stmt_close($stmtEliminarVisita);
                            }
                        }
                    } else {
                        // No existe registro para hoy, crear uno nuevo solo si la diferencia es positiva
                        if ($diferencia > 0) {
                            $queryCrearVisita = "INSERT INTO visitasusuario (IdUsuario, IdCentroAcopio, Fecha, CubetasEntregadas) 
                                               VALUES (?, ?, NOW(), ?)";
                            $stmtCrearVisita = mysqli_prepare($db, $queryCrearVisita);
                            
                            if ($stmtCrearVisita) {
                                mysqli_stmt_bind_param($stmtCrearVisita, 'iii', $idUsuario, $idCentroAcopio, $diferencia);
                                mysqli_stmt_execute($stmtCrearVisita);
                                mysqli_stmt_close($stmtCrearVisita);
                            }
                        }
                    }
                    
                    mysqli_stmt_close($stmtVerificarVisita);
                }
            }

            // 3. NUEVA LÓGICA: Eliminar insignias que ya no merece el usuario
            $kilosCompostaNuevo = $nuevasCubetas * 6.5;
            
            // Obtener todas las insignias que el usuario NO merece más (excluyendo la insignia mensual ID 9)
            $queryInsigniasNoMerecidas = "SELECT iu.Id as InsigniaUsuarioId, iu.IdInsignias, i.Descripcion 
                                         FROM insigniasusuario iu
                                         INNER JOIN insignias i ON iu.IdInsignias = i.Id
                                         WHERE iu.IdUsuario = ? 
                                         AND i.KilosComposta > ? 
                                         AND iu.IdInsignias != 9";
            
            $stmtInsigniasNoMerecidas = mysqli_prepare($db, $queryInsigniasNoMerecidas);
            
            if ($stmtInsigniasNoMerecidas) {
                mysqli_stmt_bind_param($stmtInsigniasNoMerecidas, 'id', $idUsuario, $kilosCompostaNuevo);
                mysqli_stmt_execute($stmtInsigniasNoMerecidas);
                $resultadoInsigniasNoMerecidas = mysqli_stmt_get_result($stmtInsigniasNoMerecidas);
                
                $insigniasEliminadas = [];
                
                // Eliminar cada insignia que ya no merece
                while ($insigniaNoMerecida = mysqli_fetch_assoc($resultadoInsigniasNoMerecidas)) {
                    $queryEliminarInsignia = "DELETE FROM insigniasusuario WHERE Id = ?";
                    $stmtEliminarInsignia = mysqli_prepare($db, $queryEliminarInsignia);
                    
                    if ($stmtEliminarInsignia) {
                        mysqli_stmt_bind_param($stmtEliminarInsignia, 'i', $insigniaNoMerecida['InsigniaUsuarioId']);
                        mysqli_stmt_execute($stmtEliminarInsignia);
                        mysqli_stmt_close($stmtEliminarInsignia);
                        
                        // Guardar descripción para el mensaje
                        $insigniasEliminadas[] = $insigniaNoMerecida['Descripcion'];
                    }
                }
                
                mysqli_stmt_close($stmtInsigniasNoMerecidas);
            }

            // 4. Proceso de asignación de nuevas insignias (si corresponde)
            $queryGetTotal = "SELECT CubetasTot FROM usuarios WHERE Id = ?";
            $stmtGetTotal = mysqli_prepare($db, $queryGetTotal);
            
            $insigniasNuevas = [];
            
            if ($stmtGetTotal) {
                mysqli_stmt_bind_param($stmtGetTotal, 'i', $idUsuario);
                mysqli_stmt_execute($stmtGetTotal);
                $resultadoTotal = mysqli_stmt_get_result($stmtGetTotal);
                
                if ($row = mysqli_fetch_assoc($resultadoTotal)) {
                    $totalCubetas = $row['CubetasTot'];
                    $kilosComposta = $totalCubetas * 6.5;
                    
                    // Calcular si se alcanzó una meta
                    $progresoCubetas = $totalCubetas % 10;
                    $metaAlcanzada = ($progresoCubetas == 0 && $totalCubetas > 0);
                    
                    // Consultar las insignias disponibles para asignar según los kilos de composta
                    $queryInsignias = "SELECT * FROM insignias WHERE KilosComposta <= ? ORDER BY KilosComposta DESC";
                    $stmtInsignias = mysqli_prepare($db, $queryInsignias);
                    
                    if ($stmtInsignias) {
                        mysqli_stmt_bind_param($stmtInsignias, 'd', $kilosComposta);
                        mysqli_stmt_execute($stmtInsignias);
                        $resultadoInsignias = mysqli_stmt_get_result($stmtInsignias);
                        
                        // Procesar cada insignia elegible
                        while ($insignia = mysqli_fetch_assoc($resultadoInsignias)) {
                            $idInsignia = $insignia['Id'];
                            $descripcionInsignia = $insignia['Descripcion'];
                            
                            // Verificar si es la insignia mensual (ID 9)
                            if ($idInsignia == 9) {
                                // Para la insignia mensual, verificar si ya se ha asignado este mes
                                $queryVerificarMensual = "SELECT * FROM insigniasusuario 
                                                        WHERE IdUsuario = ? AND IdInsignias = 9 
                                                        AND MONTH(Fecha) = MONTH(CURRENT_DATE()) 
                                                        AND YEAR(Fecha) = YEAR(CURRENT_DATE())";
                                $stmtVerificarMensual = mysqli_prepare($db, $queryVerificarMensual);
                                
                                if ($stmtVerificarMensual) {
                                    mysqli_stmt_bind_param($stmtVerificarMensual, 'i', $idUsuario);
                                    mysqli_stmt_execute($stmtVerificarMensual);
                                    $resultadoVerificarMensual = mysqli_stmt_get_result($stmtVerificarMensual);
                                    
                                    // Si no hay resultados, asignar la insignia mensual
                                    if (mysqli_num_rows($resultadoVerificarMensual) == 0 && $metaAlcanzada) {
                                        $queryAsignarInsignia = "INSERT INTO insigniasusuario (IdUsuario, IdInsignias, Fecha) 
                                                               VALUES (?, ?, NOW())";
                                        $stmtAsignarInsignia = mysqli_prepare($db, $queryAsignarInsignia);
                                        
                                        if ($stmtAsignarInsignia) {
                                            mysqli_stmt_bind_param($stmtAsignarInsignia, 'ii', $idUsuario, $idInsignia);
                                            mysqli_stmt_execute($stmtAsignarInsignia);
                                            mysqli_stmt_close($stmtAsignarInsignia);
                                            
                                            $insigniasNuevas[] = $descripcionInsignia;
                                        }
                                    }
                                    mysqli_stmt_close($stmtVerificarMensual);
                                }
                            } else {
                                // Para otras insignias, verificar si ya se ha asignado alguna vez
                                $queryVerificarInsignia = "SELECT * FROM insigniasusuario 
                                                         WHERE IdUsuario = ? AND IdInsignias = ?";
                                $stmtVerificarInsignia = mysqli_prepare($db, $queryVerificarInsignia);
                                
                                if ($stmtVerificarInsignia) {
                                    mysqli_stmt_bind_param($stmtVerificarInsignia, 'ii', $idUsuario, $idInsignia);
                                    mysqli_stmt_execute($stmtVerificarInsignia);
                                    $resultadoVerificarInsignia = mysqli_stmt_get_result($stmtVerificarInsignia);
                                    
                                    // Si no hay resultados, asignar la nueva insignia
                                    if (mysqli_num_rows($resultadoVerificarInsignia) == 0) {
                                        $queryAsignarInsignia = "INSERT INTO insigniasusuario (IdUsuario, IdInsignias, Fecha) 
                                                               VALUES (?, ?, NOW())";
                                        $stmtAsignarInsignia = mysqli_prepare($db, $queryAsignarInsignia);
                                        
                                        if ($stmtAsignarInsignia) {
                                            mysqli_stmt_bind_param($stmtAsignarInsignia, 'ii', $idUsuario, $idInsignia);
                                            mysqli_stmt_execute($stmtAsignarInsignia);
                                            mysqli_stmt_close($stmtAsignarInsignia);
                                            
                                            $insigniasNuevas[] = $descripcionInsignia;
                                        }
                                    }
                                    mysqli_stmt_close($stmtVerificarInsignia);
                                }
                            }
                        }
                        mysqli_stmt_close($stmtInsignias);
                    }
                }
                mysqli_stmt_close($stmtGetTotal);
            }

            // Confirmar transacción
            mysqli_commit($db);

            // 5. Crear mensaje de actualización con información de insignias
            $mensajeActualizacion = "<h3 class='mensaje_Cubetas_Modificadas'>¡Se modificaron las cubetas correctamente! Total: $nuevasCubetas cubetas</h3>";
            
            // Agregar mensajes de insignias eliminadas
            if (!empty($insigniasEliminadas)) {
                $mensajeActualizacion .= "<h4 class='mensaje-insignia-eliminada'>Insignias removidas (ya no alcanza el requisito):</h4>";
            }
            
            // Agregar mensajes de insignias nuevas
            if (!empty($insigniasNuevas)) {
                foreach ($insigniasNuevas as $insigniaNueva) {
                    $mensajeActualizacion .= "<h2 class='mensaje-insignia-asignada'>¡Se ha otorgado una nueva insignia: $insigniaNueva!</h2>";
                }
            }

            // 6. Consultar los datos actualizados del usuario
            $queryRefresh = "SELECT Id, Nombre, ApPat, ApMat, CubetasTot, IdCentroAcopio, Rol, InformacionUsuario
                           FROM usuarios 
                           WHERE Id = ?";

            $stmtRefresh = mysqli_prepare($db, $queryRefresh);
            mysqli_stmt_bind_param($stmtRefresh, 'i', $idUsuario);
            mysqli_stmt_execute($stmtRefresh);
            $resultadoRefresh = mysqli_stmt_get_result($stmtRefresh);

            if ($resultadoRefresh && mysqli_num_rows($resultadoRefresh) > 0) {
                $usuarioSeleccionado = mysqli_fetch_assoc($resultadoRefresh);

                // Recalcular progreso
                $totalCubetas = $usuarioSeleccionado['CubetasTot'];
                $progresoCubetas = $totalCubetas % 10;
                $cubetasRestantes = 10 - $progresoCubetas;
                if ($cubetasRestantes == 10 && $totalCubetas > 0) {
                    $cubetasRestantes = 0;
                }
                $metaAlcanzada = ($cubetasRestantes == 0 && $totalCubetas > 0);
            }

            mysqli_stmt_close($stmtRefresh);

            $_SESSION['mensajeActualizacion'] = $mensajeActualizacion;
            $_SESSION['usuario_previo'] = $usuarioSeleccionado;
            $_SESSION['progreso_anterior'] = $progresoCubetas;
            $_SESSION['cubetasAnteriores'] = $cubetasRestantes;
            $_SESSION['metaAnterior'] = $metaAlcanzada;

            /* Seccion para regresar la info del usuario */
            $_SESSION['InfodeUsuario'] = $usuarioSeleccionado['InformacionUsuario'];

            header("Location: busqueda.php");
            exit();

        } catch (Exception $e) {
            mysqli_rollback($db);
            $mensajeActualizacion = "Error: " . $e->getMessage();

            // Recargar datos del usuario en caso de error
            if ($idUsuario > 0) {
                $query = "SELECT Id, Nombre, ApPat, ApMat, CubetasTot, IdCentroAcopio, Rol FROM usuarios WHERE Id = ?";
                $stmt = mysqli_prepare($db, $query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
                    mysqli_stmt_execute($stmt);
                    $resultado = mysqli_stmt_get_result($stmt);
                    if ($resultado && mysqli_num_rows($resultado) > 0) {
                        $usuarioSeleccionado = mysqli_fetch_assoc($resultado);
                        $totalCubetas = $usuarioSeleccionado['CubetasTot'];
                        $progresoCubetas = $totalCubetas % 10;
                        $cubetasRestantes = 10 - $progresoCubetas;
                        if ($cubetasRestantes == 10 && $totalCubetas > 0) {
                            $cubetasRestantes = 0;
                        }
                        $metaAlcanzada = ($cubetasRestantes == 0 && $totalCubetas > 0);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } else {
        $mensajeActualizacion = "Error: Datos de entrada inválidos para modificar cubetas.";
    }
}
// Procesar cambio de rol
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiarRol']) && $_SESSION['rol'] === 'SuperAdmin') {
    $idUsuario = (int)filter_var($_POST['idUsuario'], FILTER_SANITIZE_NUMBER_INT);
    $nuevoRol = trim($_POST['nuevoRol']);

    // Validar que el nuevo rol sea permitido
    if (in_array($nuevoRol, ['Admin', 'User'], true) && $idUsuario > 0) {
        try {
            // Actualizar el rol del usuario
            $queryRol = "UPDATE usuarios SET Rol = ? WHERE Id = ?";
            $stmtRol = mysqli_prepare($db, $queryRol);

            if (!$stmtRol) {
                throw new Exception("Error al preparar la consulta: " . mysqli_error($db));
            }

            mysqli_stmt_bind_param($stmtRol, 'si', $nuevoRol, $idUsuario);

            if (!mysqli_stmt_execute($stmtRol)) {
                throw new Exception("Error al actualizar el rol: " . mysqli_stmt_error($stmtRol));
            }

            // Verificar si se hizo algún cambio
            $mensajeActualizacionRol = "";
            if (mysqli_stmt_affected_rows($stmtRol) > 0) {
                $mensajeActualizacionRol = "<div class='mensaje-rol-actualizado'>¡Rol actualizado correctamente a <strong>" . htmlspecialchars($nuevoRol) . "</strong>!</div>";
            } else {
                $mensajeActualizacionRol = "<div class='mensaje-rol-sin-cambios'>No se realizaron cambios en el rol.</div>";
            }

            mysqli_stmt_close($stmtRol);

            // Consultar los datos actualizados del usuario (IGUAL QUE EN AUMENTAR CUBETAS)
            $queryRefresh = "SELECT Id, Nombre, ApPat, ApMat, CubetasTot, IdCentroAcopio, Rol, InformacionUsuario
                           FROM usuarios 
                           WHERE Id = ?";

            $stmtRefresh = mysqli_prepare($db, $queryRefresh);

            if (!$stmtRefresh) {
                throw new Exception("Error al preparar la consulta de actualización: " . mysqli_error($db));
            }

            mysqli_stmt_bind_param($stmtRefresh, 'i', $idUsuario);
            
            if (!mysqli_stmt_execute($stmtRefresh)) {
                throw new Exception("Error al obtener datos actualizados: " . mysqli_stmt_error($stmtRefresh));
            }

            $resultadoRefresh = mysqli_stmt_get_result($stmtRefresh);

            if ($resultadoRefresh && mysqli_num_rows($resultadoRefresh) > 0) {
                $usuarioSeleccionado = mysqli_fetch_assoc($resultadoRefresh);

                // Recalcular progreso (igual que en aumentar cubetas)
                $totalCubetas = $usuarioSeleccionado['CubetasTot'];
                $progresoCubetas = $totalCubetas % 10;
                $cubetasRestantes = 10 - $progresoCubetas;
                if ($cubetasRestantes == 10 && $totalCubetas > 0) {
                    $cubetasRestantes = 0;
                }

                $metaAlcanzada = ($cubetasRestantes == 0 && $totalCubetas > 0);

            } else {
                throw new Exception("No se pudo obtener la información actualizada del usuario.");
            }

            mysqli_stmt_close($stmtRefresh);

            // Guardar en sesión y redirigir (PATRÓN PRG)
            $_SESSION['mensajeActualizacionRol'] = $mensajeActualizacionRol;
            $_SESSION['usuario_previo'] = $usuarioSeleccionado;
            $_SESSION['progreso_anterior'] = $progresoCubetas;
            $_SESSION['cubetasAnteriores'] = $cubetasRestantes;
            $_SESSION['metaAnterior'] = $metaAlcanzada;
            $_SESSION['InfodeUsuario'] = $usuarioSeleccionado['InformacionUsuario'];

            header("Location: busqueda.php");
            exit();

        } catch (Exception $e) {
            $mensajeActualizacionRol = "<div class='mensaje-rol-error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";

            // Recargar datos del usuario en caso de error
            if ($idUsuario > 0) {
                $query = "SELECT Id, Nombre, ApPat, ApMat, CubetasTot, IdCentroAcopio, Rol, InformacionUsuario FROM usuarios WHERE Id = ?";
                $stmt = mysqli_prepare($db, $query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
                    mysqli_stmt_execute($stmt);
                    $resultado = mysqli_stmt_get_result($stmt);
                    if ($resultado && mysqli_num_rows($resultado) > 0) {
                        $usuarioSeleccionado = mysqli_fetch_assoc($resultado);
                        $totalCubetas = $usuarioSeleccionado['CubetasTot'];
                        $progresoCubetas = $totalCubetas % 10;
                        $cubetasRestantes = 10 - $progresoCubetas;
                        if ($cubetasRestantes == 10 && $totalCubetas > 0) {
                            $cubetasRestantes = 0;
                        }
                        $metaAlcanzada = ($cubetasRestantes == 0 && $totalCubetas > 0);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    } else {
        $mensajeActualizacionRol = "<div class='mensaje-rol-error'>Error: Rol no válido o ID de usuario incorrecto.</div>";
    }
}

// Incluir el header
incluirTemplate('header');
?>

<!-- Sección de registro rápido -->
<div class="seccion-registro">
    <a href="/Admin/AdUsers/registro.php" class="btn-registro">
        <i class="fa fa-user-plus"></i> Registro
    </a>
</div>

<!-- Sección de búsqueda -->
<section class="busqueda-usuarios">
    <div class="encabezado-busqueda">
        <h1>Buscar Usuario</h1>
    </div>

    <!-- Formulario de búsqueda -->
    <div class="buscador-global">
        <form method="POST" action="">
            <div class="opciones-busqueda">
                <a href="/Admin/index.php" class="link-opcion">Volver a Centros de Acopio</a>
            </div>
            <div class="campo-busqueda">
                <input type="text" name="terminoBusqueda" id="terminoBusqueda"
                    placeholder="Buscar por nombre, apellido, teléfono o correo..."
                    value="<?php echo isset($_POST['terminoBusqueda']) ? s($_POST['terminoBusqueda']) : ''; ?>"
                    required>
                <button type="submit" name="buscarUsuario" class="btn-buscar">
                    <i class="fa fa-search"></i> Buscar
                </button>
            </div>
        </form>
    </div>

    <!-- Mensaje de la búsqueda -->
    <?php if (!empty($mensajeBusqueda)): ?>
        <p class="mensaje"><?php echo s($mensajeBusqueda); ?></p>
    <?php endif; ?>
</section>
<!--Mensaje de insignias y cubetas-->
<?php if (isset($_SESSION['mensajeActualizacion'])): ?>
    <?php echo $_SESSION['mensajeActualizacion']; ?>
    <?php unset($_SESSION['mensajeActualizacion']); ?>
<?php endif; ?>
<?php
    // Al inicio de busqueda.php
    if (isset($_SESSION['mensajeActualizacionEditUser'])) {
        echo '<div class="alerta alerta--exito">';
        echo '<h3 class="mensajeDeReestablecimientodeDatos">' . s($_SESSION['mensajeActualizacionEditUser']) . '</h3>';
        echo '</div>';
        unset($_SESSION['mensajeActualizacionEditUser']);
    }

    if (isset($_SESSION['errores']) && !empty($_SESSION['errores'])) {
        echo '<div class="MensajesErrorEditUserBusqueda">';
        echo '<h3 class="ActualizacionDatosNoConcluida">Errores encontrados</h3>';
        echo '<ul>';
        foreach ($_SESSION['errores'] as $error) {
            echo '<li>' . s($error) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
        unset($_SESSION['errores']);
    }
?>

<?php
    // Recuperar mensaje de actualización de ROL si existe
    $mensajeActualizacionRol = '';
    $rolActualizado = false;
    if (isset($_SESSION['mensajeActualizacionRol'])) {
        $mensajeActualizacionRol = $_SESSION['mensajeActualizacionRol'];
        // Verificar si contiene la clase de éxito para activar el scroll
        $rolActualizado = (strpos($mensajeActualizacionRol, 'mensaje-rol-actualizado') !== false);
        unset($_SESSION['mensajeActualizacionRol']); // Limpiar después de usar
    }
    ?>
    
<!-- Mensaje de actualización ROlsi existe -->
<?php if (!empty($mensajeActualizacionRol)): ?>
    <?php echo $mensajeActualizacionRol; ?>
    <?php if (isset($rolActualizado) && $rolActualizado): ?>
    <script>        
        // Hacer scroll suave hasta el mensaje cuando se actualiza el rol
        document.addEventListener('DOMContentLoaded', function() {
            const mensaje = document.querySelector('.mensaje-rol-actualizado');
            if (mensaje) {
                mensaje.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Hacer que el mensaje destaque
                mensaje.style.boxShadow = '0 0 15px rgba(40, 167, 69, 0.5)';
                setTimeout(() => {
                    mensaje.style.boxShadow = '0 0 5px rgba(0, 0, 0, 0.1)';
                }, 1500);
            }
        });
    </script>
    <?php endif; ?>
<?php endif; ?>

<!-- Mostrar usuario seleccionado y formulario para aumentar cubetas -->
<?php if ($usuarioSeleccionado && $usuarioSeleccionado['Rol']!== 'SuperAdmin'): ?>
    <div class="detalle-usuario">
        <div class="detalle-usuario__header">
            <h3 class="detalle-usuario__titulo">Usuario Seleccionado:</h3>
            <p class="detalle-usuario__nombre">
                <?php echo s($usuarioSeleccionado['Nombre'] . ' ' . $usuarioSeleccionado['ApPat'] . ' ' . $usuarioSeleccionado['ApMat']); ?>
            </p>
        </div>

        <div class="detalle-usuario__contenido">
            <!-- Columna izquierda: Formulario y datos de cubetas -->
            <div class="detalle-usuario__columna">
                <div class="detalle-usuario__cubetas">
                    <h4 class="detalle-usuario__subtitulo ">Aumentar Cubetas</h4>

                    <form method="POST" action="" class="detalle-usuario__form">
                        <input type="hidden" name="idUsuario" value="<?php echo s($usuarioSeleccionado['Id'] ?? ''); ?>">
                        <input type="hidden" name="idCentroAcopio" value="<?php echo s($usuarioSeleccionado['IdCentroAcopio'] ?? ''); ?>">

                        <div class="detalle-usuario__campo-grupo">
                            <p>Cantidad:</p>
                            <div class="detalle-usuario__input-wrapper">
                                <input type="number" name="cubetasAgregar" id="cubetasAgregar" min="1" max="100" value="1" class="detalle-usuario__input">
                            </div>

                            <button type="submit" name="aumentarCubetas" class="detalle-usuario__btn">Aumentar</button>
                        </div>
                    </form>

                    <div class="detalle-usuario__progreso-contenedor">
                        <p class="detalle-usuario__progreso-texto">
                            <?php if ($metaAlcanzada): ?>
                                ¡Meta alcanzada! El usuario puede recibir composta.
                            <?php else: ?>
                                Faltan <?php echo s($cubetasRestantes); ?> cubetas para alcanzar la meta.
                            <?php endif; ?>
                        </p>

                        <div class="detalle-usuario__barra-progreso">
                            <div class="detalle-usuario__progreso" style="width: <?php echo ($progresoCubetas / 10) * 100; ?>%"></div>
                        </div>

                        <?php if ($metaAlcanzada): ?>
                            <div class="detalle-usuario__meta-alcanzada">
                                <span class="detalle-usuario__composta-icon">🌱</span>
                                <p class="detalle-usuario__composta-texto">¡Entregar Composta!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php
                        $cubetastotales = $usuarioSeleccionado['CubetasTot'];
                        $FormularioBolsasHTML = '';

                        if ($cubetastotales > 9):
                            $IdUsuario = $usuarioSeleccionado['Id'];

                            $BolsasRegistradasUser = EncontrarNumeroUltimaBolsa($IdUsuario, $db);
                            $BolsasReales = intval($cubetastotales / 10);

                            while ($BolsasReales > $BolsasRegistradasUser) {
                                RegistrarBolsaAUsuario($db, $IdUsuario, $BolsasRegistradasUser);
                                $BolsasRegistradasUser++;
                            }

                            while ($BolsasReales < $BolsasRegistradasUser) {
                                EliminarBolsaAUsuario($db, $IdUsuario, $BolsasRegistradasUser);
                                $BolsasRegistradasUser--;
                            }

                            $bolsas = ObtenerUltimasBolsasUsuario($db, $IdUsuario);

                            ob_start();
                            ?>
                            <?php if (count($bolsas) > 0): ?>
                                <form action="actualizar_estatus.php" method="POST" class="TablaAdminBolsas">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>N° Bolsa</th>
                                                <th>Estatus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($bolsas as $bolsa): ?>
                                                <tr>
                                                    <td><?php echo s($bolsa['NoBolsa']); ?></td>
                                                    <td>
                                                        <input 
                                                            type="checkbox" 
                                                            name="estatus_bolsas[<?php echo $bolsa['NoBolsa']; ?>]" 
                                                            value="1"
                                                            <?php echo ($bolsa['EstatusEntrega'] == 1) ? 'checked' : ''; ?>
                                                        >
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <input type="hidden" name="id_usuario" value="<?php echo $IdUsuario; ?>">
                                    <input type="hidden" name="usuario_previo_serializado" value="<?php echo s(serialize($usuarioSeleccionado)); ?>">
                                    <input type="hidden" name="progreso_anterior" value="<?php echo s($progresoCubetas); ?>">
                                    <input type="hidden" name="cubetas_anteriores" value="<?php echo s($cubetasRestantes); ?>">
                                    <input type="hidden" name="meta_anterior" value="<?php echo s($metaAlcanzada); ?>">
                                    <!-- Entrada para la infor del usuario -->
                                    <input type="hidden" name="informacion_usuario" value="<?php echo s($InfoUsuarioActual ?? ''); ?>">

                                    <button type="submit" class="BtnEnviarEstatus">Actualizar estatus</button>
                                </form>
                            <?php else: ?>
                                <p>No hay bolsas registradas aún.</p>
                            <?php
                            endif;
                            $FormularioBolsasHTML = ob_get_clean();
                        endif;
                    ?>

                    <!-- Formulario: Mobile -->
                    <?php if (!empty($FormularioBolsasHTML)): ?>
                        <section class="mobile-only tabla_AprobacionBolsasComposta">
                            <?php echo $FormularioBolsasHTML; ?>
                        </section>
                    <?php endif; ?>

                    <!-- FORM DE NOTAS SEPARADO -->
                    <form action="guardar_informacion_usuario.php" method="POST" class="FormRecuadroInfo">
                        <div class="RecuadroInfo">
                            <textarea 
                                name="informacion_usuario"
                                placeholder="Sección para notas de usuario..." 
                                class="input-recuadro"
                            ><?php echo s($InfoUsuarioActual ?? ''); ?></textarea>
                            
                            <input type="hidden" name="id_usuario" value="<?php echo $IdUsuario; ?>">
                            <input type="hidden" name="usuario_previo_serializado" value="<?php echo s(serialize($usuarioSeleccionado)); ?>">
                            <input type="hidden" name="progreso_anterior" value="<?php echo s($progresoCubetas); ?>">
                            <input type="hidden" name="cubetas_anteriores" value="<?php echo s($cubetasRestantes); ?>">
                            <input type="hidden" name="meta_anterior" value="<?php echo s($metaAlcanzada); ?>">
                            
                            <button type="submit" class="BtnGuardarInfo">Guardar información</button>
                        </div>
                    </form>

                    <form method="POST" action="" class="detalle-usuario__form editar_cubetas_totales" id="formModificar">
                        <input type="hidden" name="idUsuario" value="<?php echo s($usuarioSeleccionado['Id']); ?>">
                        <input type="hidden" name="idCentroAcopio" value="<?php echo s($usuarioSeleccionado['IdCentroAcopio']); ?>">

                        <div class="detalle-usuario__campo-grupo">
                            <p>Total de cubetas:</p>
                            <div class="detalle-usuario__input-wrapper numeroCubetasEditable">
                                <input type="number" name="nuevasCubetas" id="nuevasCubetas"
                                    value="<?php echo s($usuarioSeleccionado['CubetasTot']); ?>" 
                                    class="detalle-usuario__input">
                            </div>

                            <button type="submit" name="modificarCubetas" class="detalle-usuario__btn detalle-usuario__btn--modificar">
                                Modificar
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Columna derecha: Imagen + Formulario desktop -->
            <div class="detalle-usuario__columna detalle-usuario__columna--imagen">
                <?php if (!empty($FormularioBolsasHTML)): ?>
                    <section class="desktop-only tabla_AprobacionBolsasComposta">
                        <?php echo $FormularioBolsasHTML; ?>
                    </section>
                <?php endif; ?>

                <div class="detalle-usuario__imagen-contenedor">
                    <?php if ($metaAlcanzada): ?>
                        <img src="/vectores/Admin/Busqueda/COMPOSTA VIVE.png" alt="Meta de cubetas alcanzada" class="detalle-usuario__imagen">
                    <?php else: ?>
                        <img src="/vectores/Admin/Busqueda/CUBETA Y COMPOSTA.png" alt="Progreso de cubetas" class="detalle-usuario__imagen">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<!-- Funciones que utilizaremos para el control de las bolsas de composta recibidas por usuario -->
<?php 
function EncontrarNumeroUltimaBolsa($IdUsuario, $db) {
    $query = 'SELECT MAX(NoBolsa) AS UltimaBolsa FROM bolsascompostausuario WHERE IdUsuario = ?';
    $Consulta = mysqli_prepare($db, $query);

    if ($Consulta) {
        mysqli_stmt_bind_param($Consulta, 'i', $IdUsuario);
        mysqli_stmt_execute($Consulta);
        mysqli_stmt_bind_result($Consulta, $UltimaBolsa);
        mysqli_stmt_fetch($Consulta);
        mysqli_stmt_close($Consulta);
        return $UltimaBolsa ?? 0;
    }
    return 0;
}

function RegistrarBolsaAUsuario($db, $ID, $NumBolsaActual) {
    $NumBolsaActual++; // incrementamos para registrar la siguiente bolsa
    $fechaActual = date('Y-m-d'); // o puedes usar datetime si lo prefieres

    $query = 'INSERT INTO bolsascompostausuario (IdUsuario, FechadeBolsa, NoBolsa, EstatusEntrega) VALUES (?, ?, ?, 0)';
    $stmt = mysqli_prepare($db, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'isi', $ID, $fechaActual, $NumBolsaActual);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function EliminarBolsaAUsuario($db, $ID, $NumBolsaActual) {
    // Borra la bolsa del usuario con el número específico (la última)
    $query = 'DELETE FROM bolsascompostausuario WHERE IdUsuario = ? AND NoBolsa = ? LIMIT 1';
    $stmt = mysqli_prepare($db, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $ID, $NumBolsaActual);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function ObtenerUltimasBolsasUsuario($db, $IdUsuario, $limite = 3) {
    $query = 'SELECT NoBolsa, EstatusEntrega FROM bolsascompostausuario WHERE IdUsuario = ? ORDER BY NoBolsa DESC LIMIT ?';
    $stmt = mysqli_prepare($db, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ii', $IdUsuario, $limite);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $NoBolsa, $EstatusEntrega);

        $bolsas = [];
        while (mysqli_stmt_fetch($stmt)) {
            $bolsas[] = [
                'NoBolsa' => $NoBolsa,
                'EstatusEntrega' => $EstatusEntrega
            ];
        }

        mysqli_stmt_close($stmt);
        return $bolsas;
    }

    return [];
}

?>
<!-- Sección de resultados de búsqueda (separada de la sección de búsqueda) -->
<?php if (!empty($resultadosBusqueda)): ?>
    <div class="tabla-resultados-busqueda">
        <h3 class="tabla-resultados-busqueda__titulo">Resultados de la búsqueda (<?php echo $totalResultados; ?>)</h3>

        <!-- Mostrar usuarios del centro actual -->
        <?php if (!empty($usuariosCentroActual)): ?>
            <div class="seccion-usuarios">
                <h4 class="seccion-usuarios__titulo">
                    <i class="fa fa-map-marker"></i>
                    Usuarios del Centro: <?php echo htmlspecialchars($nombreCentroActual); ?>
                    <span class="seccion-usuarios__contador">(<?php echo count($usuariosCentroActual); ?>)</span>
                </h4>
                
                <div class="tabla-resultados-busqueda__contenedor">
                    <table class="tabla-resultados-busqueda__tabla">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Cubetas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuariosCentroActual as $usuario): ?>
                                <tr class="fila-centro-actual">
                                    <td><?php echo $usuario['Id']; ?></td>
                                    <td><?php echo s($usuario['Nombre']); ?></td>
                                    <td><?php echo s($usuario['ApPat'] . ' ' . $usuario['ApMat'] ?? ''); ?></td>
                                    <td>
                                        <span class="cubetas-badge"><?php echo s($usuario['CubetasTot']); ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="idUsuarioSeleccionado" value="<?php echo $usuario['Id']; ?>">
                                            <button type="submit" name="seleccionarUsuario" class="tabla-resultados-busqueda__boton tabla-resultados-busqueda__boton--centro">
                                                <i class="fa fa-user"></i> Ver perfil
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Mostrar otros usuarios si existen -->
        <?php if (!empty($otrosUsuarios)): ?>
            <div class="seccion-usuarios seccion-usuarios--otros">
                <h4 class="seccion-usuarios__titulo seccion-usuarios__titulo--otros">
                    <i class="fa fa-users"></i>
                    Otros Resultados
                    <span class="seccion-usuarios__contador">(<?php echo count($otrosUsuarios); ?>)</span>
                </h4>
                
                <div class="tabla-resultados-busqueda__contenedor">
                    <table class="tabla-resultados-busqueda__tabla">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Centro</th>
                                <th class="CentradoTexto">Cubetas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($otrosUsuarios as $usuario): ?>
                                <tr class="fila-otros-centros">
                                    <td><?php echo $usuario['Id']; ?></td>
                                    <td><?php echo s($usuario['Nombre']); ?></td>
                                    <td><?php echo s($usuario['ApPat'] . ' ' . $usuario['ApMat'] ?? ''); ?></td>
                                    <td>
                                        <span class="centro-badge">
                                            <?php echo s($usuario['NombreCentro'] ?? 'Sin asignar'); ?>
                                        </span>
                                    </td>
                                    <td class="CentradoTexto">
                                        <span class="cubetas-badge "><?php echo s($usuario['CubetasTot']); ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="idUsuarioSeleccionado" value="<?php echo $usuario['Id']; ?>">
                                            <button type="submit" name="seleccionarUsuario" class="tabla-resultados-busqueda__boton">
                                                <i class="fa fa-user"></i> Ver perfil
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($usuarioSeleccionado && isset($_SESSION['rol']) && $_SESSION['rol'] === 'SuperAdmin'): ?>
    <div class="detalle-usuario__cambiar-rol">
        <h4 class="btnDeRol detalle-usuario__subtitulo">Cambiar Rol</h4>

        <form method="POST" action="" class="detalle-usuario__form-rol" id="formCambiarRol">
            <input type="hidden" name="idUsuario" value="<?php echo s($usuarioSeleccionado['Id']); ?>">
            <input type="hidden" name="cambiarRol" value="1">

            <div class="detalle-usuario__rol-actual">
                <p>Rol actual: <strong>
                        <?php echo isset($usuarioSeleccionado['Rol']) ? s($usuarioSeleccionado['Rol']) : 'User'; ?>
                    </strong></p>
            </div>

            <p class="detalle-usuario__rol-label">Nuevo rol:</p>

            <div class="detalle-usuario__botones-rol">
                <button type="button" class="detalle-usuario__btn-rol <?php echo (isset($usuarioSeleccionado['Rol']) && $usuarioSeleccionado['Rol'] === 'Admin') ? 'activo' : ''; ?>" data-rol="Admin">
                    Admin
                </button>

                <button type="button" class="detalle-usuario__btn-rol <?php echo (!isset($usuarioSeleccionado['Rol']) || $usuarioSeleccionado['Rol'] === 'User') ? 'activo' : ''; ?>" data-rol="User">
                    User
                </button>
            </div>

            <input type="hidden" name="nuevoRol" id="nuevoRol" value="">
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const botones = document.querySelectorAll('.detalle-usuario__btn-rol');
        const inputRol = document.getElementById('nuevoRol');
        const form = document.getElementById('formCambiarRol');
        
        botones.forEach(btn => {
            btn.addEventListener('click', function() {
                const rolSeleccionado = this.getAttribute('data-rol');
                
                // Remover clase activo de todos
                botones.forEach(b => b.classList.remove('activo'));
                
                // Agregar a este
                this.classList.add('activo');
                
                // Asignar valor y enviar
                inputRol.value = rolSeleccionado;
                form.submit();
            });
        });
    });
    </script>
<?php endif; ?>

<!-- Incluir información adicional del usuario si está seleccionado -->
<?php if ($usuarioSeleccionado): ?>
    <?php include 'InformacionDeUnUsuario.php'; ?>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hacer filas de la tabla clickeables
        const filas = document.querySelectorAll('.tabla-resultados-busqueda__tabla tbody tr');
        filas.forEach(fila => {
            fila.addEventListener('click', function() {
                const boton = this.querySelector('.tabla-resultados-busqueda__boton');
                if (boton) {
                    boton.click();
                }
            });
        });

        // Focus automático en el campo de búsqueda si no hay usuario seleccionado
        const campoBusqueda = document.getElementById('terminoBusqueda');
        const detalleUsuario = document.querySelector('.detalle-usuario');
        if (campoBusqueda && !detalleUsuario) {
            campoBusqueda.focus();
        }

        // Animar la barra de progreso al cargar la página
        const barraProgreso = document.querySelector('.detalle-usuario__progreso');
        if (barraProgreso) {
            // Obtener el ancho actual
            const anchoActual = barraProgreso.style.width;
            // Reiniciar y animar
            barraProgreso.style.width = '0%';
            setTimeout(() => {
                barraProgreso.style.transition = 'width 1s ease-in-out';
                barraProgreso.style.width = anchoActual;
            }, 100);
        }

        // Mejorar la UX del input de AUMENTAR cubetas
        const inputAumentarCubetas = document.getElementById('cubetasAgregar');
        if (inputAumentarCubetas) {
            // Manejar foco en el input
            inputAumentarCubetas.addEventListener('focus', function() {
                this.select(); // Seleccionar todo el texto al hacer focus
            });

            // Hacer que el Enter en el input envíe el formulario de AUMENTAR
            inputAumentarCubetas.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    // Buscar específicamente el botón "aumentarCubetas" dentro del mismo formulario
                    const formularioAumentar = this.closest('form');
                    const botonAumentar = formularioAumentar.querySelector('button[name="aumentarCubetas"]');
                    if (botonAumentar) {
                        botonAumentar.click();
                    }
                }
            });
        }

        // Mejorar la UX del input de MODIFICAR cubetas
        const inputModificarCubetas = document.getElementById('nuevasCubetas');
        if (inputModificarCubetas) {
            // Manejar foco en el input
            inputModificarCubetas.addEventListener('focus', function() {
                this.select(); // Seleccionar todo el texto al hacer focus
            });

            // Hacer que el Enter en el input envíe el formulario de MODIFICAR
            inputModificarCubetas.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    // Buscar específicamente el botón "modificarCubetas" dentro del mismo formulario
                    const formularioModificar = this.closest('form');
                    const botonModificar = formularioModificar.querySelector('button[name="modificarCubetas"]');
                    if (botonModificar) {
                        botonModificar.click();
                    }
                }
            });
        }

        // Efecto visual para el mensaje de meta alcanzada
        const metaAlcanzada = document.querySelector('.detalle-usuario__meta-alcanzada');
        if (metaAlcanzada) {
            // Añadir clase especial después de cargar para activar animaciones adicionales
            setTimeout(() => {
                metaAlcanzada.classList.add('activo');
            }, 500);
        }
        
        // Manejo de botones de rol
        const botonesRol = document.querySelectorAll('.detalle-usuario__btn-rol');
        const inputNuevoRol = document.getElementById('nuevoRol');
        const formRol = document.querySelector('.detalle-usuario__form-rol');

        if (botonesRol.length > 0 && inputNuevoRol && formRol) {
            botonesRol.forEach(boton => {
                boton.addEventListener('click', function() {
                    // Quitar clase 'activo' de todos los botones
                    botonesRol.forEach(btn => btn.classList.remove('activo'));

                    // Añadir clase 'activo' al botón seleccionado
                    this.classList.add('activo');

                    // Obtener el valor del rol del botón (Admin o User)
                    const nuevoRol = this.textContent.trim();

                    // Asignar el valor al campo oculto
                    inputNuevoRol.value = nuevoRol;

                    // Enviar el formulario
                    formRol.submit();
                });
            });
        }
    });
</script>

<?php
// Cerrar conexión a la base de datos si la abrimos en este archivo
if (isset($cerrar_db_al_final) && $cerrar_db_al_final && isset($db)) {
    mysqli_close($db);
}
?>

<script src="/build_previo/js/app.js"></script>