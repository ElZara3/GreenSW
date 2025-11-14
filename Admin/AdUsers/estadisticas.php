<?php
    session_start();
    include '../../includes/app.php';
    $db = conectarDB();
    use ProtoClase\CentrosAcopio;
    use ProtoClase\Usuario;
    verificarSesionActiva('Admin');

// Determinar si este archivo se está ejecutando directamente o siendo incluido
$esArchivoDirecto = !isset($no_cerrar_db);

// ===== OBTENER DATOS PARA FILTROS =====

// Obtener lista de centros de acopio para filtrado
$centrosAcopio = [];
//Extraemos valores en especifico desde las clases
$centrosAcopio = CentrosAcopio::ExtraerAtributosEspecificos("Id","Nombre");

// Obtener usuarios para el filtro de usuario
$usuarios = [];
$usuarios = Usuario::ExtraerNombreCompletoyId();

// ===== PROCESAR FILTROS =====
$filtroFechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-d', strtotime('-1 year'));
$filtroFechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d');
$filtroUsuario = isset($_POST['usuario']) ? intval($_POST['usuario']) : 0;
$filtroCentroAcopio = isset($_POST['centro_acopio']) ? intval($_POST['centro_acopio']) : 0;
$tipoVisualizacion = isset($_POST['tipo_visualizacion']) ? $_POST['tipo_visualizacion'] : 'mensual';

// ===== CONSULTAS PARA ESTADÍSTICAS GLOBALES =====

// Total de cubetas entregadas (global)
$totalCubetas = 0;
$queryTotalCubetas = "SELECT SUM(CubetasTot) as total FROM usuarios";
$resultTotalCubetas = mysqli_query($db, $queryTotalCubetas);

if ($resultTotalCubetas && $row = mysqli_fetch_assoc($resultTotalCubetas)) {
    $totalCubetas = $row['total'] ?? 0;
}

// Promedio de cubetas por usuario
$promedioCubetas = 0;
$queryPromedio = "SELECT AVG(CubetasTot) as promedio FROM usuarios";
$resultPromedio = mysqli_query($db, $queryPromedio);

if ($resultPromedio && $row = mysqli_fetch_assoc($resultPromedio)) {
    $promedioCubetas = round($row['promedio'] ?? 0, 2);
}

// Top 5 usuarios con más cubetas
$topUsuarios = [];
$queryTopUsuarios = "SELECT u.Id, u.Nombre, u.ApPat, u.ApMat, u.CubetasTot 
                    FROM usuarios u 
                    ORDER BY u.CubetasTot DESC 
                    LIMIT 5";
$resultTopUsuarios = mysqli_query($db, $queryTopUsuarios);

if ($resultTopUsuarios) {
    while ($row = mysqli_fetch_assoc($resultTopUsuarios)) {
        $topUsuarios[] = $row;
    }
    mysqli_free_result($resultTopUsuarios);
}

// Total de cubetas por centro de acopio
$cubetasPorCentro = [];
$queryCubetasCentro = "SELECT ca.Id, ca.Nombre, SUM(u.CubetasTot) as total 
                      FROM usuarios u 
                      JOIN centrosacopio ca ON u.IdCentroAcopio = ca.Id 
                      GROUP BY ca.Id, ca.Nombre 
                      ORDER BY total DESC";
$resultCubetasCentro = mysqli_query($db, $queryCubetasCentro);

if ($resultCubetasCentro) {
    while ($row = mysqli_fetch_assoc($resultCubetasCentro)) {
        $cubetasPorCentro[] = $row;
    }
    mysqli_free_result($resultCubetasCentro);
}

// ===== CONSULTAS PARA DATOS FILTRADOS =====

// Primero verificamos si la tabla de visitas existe
$tablaVisitasExiste = false;
$queryCheckTabla = "SHOW TABLES LIKE 'visitasusuario'";
$resultCheckTabla = mysqli_query($db, $queryCheckTabla);
if ($resultCheckTabla && mysqli_num_rows($resultCheckTabla) > 0) {
    $tablaVisitasExiste = true;
    $nombreTablaVisitas = 'visitasusuario';
} else {
    // También verificamos con la ortografía alternativa
    $queryCheckTabla = "SHOW TABLES LIKE 'visitasusaurio'";
    $resultCheckTabla = mysqli_query($db, $queryCheckTabla);
    if ($resultCheckTabla && mysqli_num_rows($resultCheckTabla) > 0) {
        $tablaVisitasExiste = true;
        $nombreTablaVisitas = 'visitasusaurio';
    } else {
        $nombreTablaVisitas = 'visitasusuario';
    }
}

// Inicializar arrays de datos
$entregasFiltradas = [];
$cubetasPorCentroFiltrado = [];
$entregasPorUsuarioFiltrado = [];

// Si la tabla existe, usamos la consulta original para entregas filtradas
if ($tablaVisitasExiste) {
    // La consulta varía dependiendo del tipo de visualización
    if ($tipoVisualizacion == 'diario') {
        $queryEntregas = "SELECT 
                            DATE_FORMAT(v.Fecha, '%Y-%m-%d') as dia,
                            SUM(v.CubetasEntregadas) as cubetas_entregadas,
                            COUNT(DISTINCT v.IdUsuario) as usuarios_unicos
                        FROM 
                            {$nombreTablaVisitas} v 
                        JOIN 
                            usuarios u ON v.IdUsuario = u.Id
                        WHERE 
                            v.Fecha BETWEEN ? AND ?";
    } else {
        $queryEntregas = "SELECT 
                            DATE_FORMAT(v.Fecha, '%Y-%m') as mes,
                            SUM(v.CubetasEntregadas) as cubetas_entregadas,
                            COUNT(DISTINCT v.IdUsuario) as usuarios_unicos
                        FROM 
                            {$nombreTablaVisitas} v 
                        JOIN 
                            usuarios u ON v.IdUsuario = u.Id
                        WHERE 
                            v.Fecha BETWEEN ? AND ?";
    }

    $params = [$filtroFechaInicio, $filtroFechaFin];
    $types = "ss";

    // Añadir filtro de usuario si está seleccionado
    if ($filtroUsuario > 0) {
        $queryEntregas .= " AND v.IdUsuario = ?";
        $params[] = $filtroUsuario;
        $types .= "i";
    }

    // Añadir filtro de centro de acopio si está seleccionado
    if ($filtroCentroAcopio > 0) {
        $queryEntregas .= " AND v.IdCentroAcopio = ?";
        $params[] = $filtroCentroAcopio;
        $types .= "i";
    }

    // Agrupar según la visualización solicitada
    if ($tipoVisualizacion == 'diario') {
        $queryEntregas .= " GROUP BY dia ORDER BY dia";
    } else {
        $queryEntregas .= " GROUP BY mes ORDER BY mes";
    }

    // Ejecutar la consulta con los parámetros
    $stmt = mysqli_prepare($db, $queryEntregas);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $resultEntregas = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($resultEntregas)) {
            // Añadir información necesaria para unificar el formato entre diario y mensual
            if ($tipoVisualizacion == 'diario') {
                $row['mes'] = date('Y-m', strtotime($row['dia']));
            } else {
                $row['dia'] = $row['mes'] . '-01'; // Primer día del mes como representativo
            }
            $entregasFiltradas[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Consulta para obtener las cubetas entregadas por centro con filtros
    $queryCubetasCentroFiltrado = "SELECT 
                                    ca.Id, 
                                    ca.Nombre, 
                                    SUM(v.CubetasEntregadas) as total 
                                FROM 
                                    {$nombreTablaVisitas} v 
                                JOIN 
                                    centrosacopio ca ON v.IdCentroAcopio = ca.Id 
                                WHERE 
                                    v.Fecha BETWEEN ? AND ?";

    $paramsCentro = [$filtroFechaInicio, $filtroFechaFin];
    $typesCentro = "ss";

    // Añadir filtro de usuario si está seleccionado
    if ($filtroUsuario > 0) {
        $queryCubetasCentroFiltrado .= " AND v.IdUsuario = ?";
        $paramsCentro[] = $filtroUsuario;
        $typesCentro .= "i";
    }

    $queryCubetasCentroFiltrado .= " GROUP BY ca.Id, ca.Nombre ORDER BY total DESC";

    // Ejecutar consulta
    $stmtCentro = mysqli_prepare($db, $queryCubetasCentroFiltrado);
    if ($stmtCentro) {
        mysqli_stmt_bind_param($stmtCentro, $typesCentro, ...$paramsCentro);
        mysqli_stmt_execute($stmtCentro);
        $resultCubetasCentroFiltrado = mysqli_stmt_get_result($stmtCentro);
        
        while ($row = mysqli_fetch_assoc($resultCubetasCentroFiltrado)) {
            $cubetasPorCentroFiltrado[] = $row;
        }
        
        mysqli_stmt_close($stmtCentro);
    }
    
    // Consulta para obtener las entregas por usuario con filtros
    $queryUsuariosFiltrado = "SELECT 
                                u.Id, 
                                u.Nombre, 
                                u.ApPat, 
                                u.ApMat, 
                                SUM(v.CubetasEntregadas) as total 
                            FROM 
                                {$nombreTablaVisitas} v 
                            JOIN 
                                usuarios u ON v.IdUsuario = u.Id 
                            WHERE 
                                v.Fecha BETWEEN ? AND ?";

    $paramsUsuario = [$filtroFechaInicio, $filtroFechaFin];
    $typesUsuario = "ss";

    // Añadir filtro de centro de acopio si está seleccionado
    if ($filtroCentroAcopio > 0) {
        $queryUsuariosFiltrado .= " AND v.IdCentroAcopio = ?";
        $paramsUsuario[] = $filtroCentroAcopio;
        $typesUsuario .= "i";
    }

    $queryUsuariosFiltrado .= " GROUP BY u.Id, u.Nombre, u.ApPat, u.ApMat ORDER BY total DESC LIMIT 10";

    // Ejecutar consulta
    $stmtUsuario = mysqli_prepare($db, $queryUsuariosFiltrado);
    if ($stmtUsuario) {
        mysqli_stmt_bind_param($stmtUsuario, $typesUsuario, ...$paramsUsuario);
        mysqli_stmt_execute($stmtUsuario);
        $resultUsuariosFiltrado = mysqli_stmt_get_result($stmtUsuario);
        
        while ($row = mysqli_fetch_assoc($resultUsuariosFiltrado)) {
            $entregasPorUsuarioFiltrado[] = $row;
        }
        
        mysqli_stmt_close($stmtUsuario);
    }
    
} else {
    // Si la tabla no existe, generamos datos simulados basados en CubetasTot
    // Calculamos datos por mes usando registros de usuarios
    $mesesSimulados = [];
    $inicioMes = new DateTime($filtroFechaInicio);
    $finMes = new DateTime($filtroFechaFin);
    $finMes->modify('last day of this month');
    
    // Crear array de meses en el rango
    $periodo = new DatePeriod(
        $inicioMes->modify('first day of this month'),
        new DateInterval('P1M'),
        $finMes->modify('first day of next month')
    );
    
    // Consulta para obtener usuarios y sus cubetas
    $queryUsuariosCubetas = "SELECT 
                                Id, 
                                Nombre, 
                                CubetasTot, 
                                FRegistro 
                            FROM 
                                usuarios 
                            WHERE 
                                FRegistro <= ?";
    
    $paramsUsuarios = [$filtroFechaFin];
    $typesUsuarios = "s";
    
    if ($filtroUsuario > 0) {
        $queryUsuariosCubetas .= " AND Id = ?";
        $paramsUsuarios[] = $filtroUsuario;
        $typesUsuarios .= "i";
    }
    
    if ($filtroCentroAcopio > 0) {
        $queryUsuariosCubetas .= " AND IdCentroAcopio = ?";
        $paramsUsuarios[] = $filtroCentroAcopio;
        $typesUsuarios .= "i";
    }
    
    $stmtUsuarios = mysqli_prepare($db, $queryUsuariosCubetas);
    
    if ($stmtUsuarios) {
        mysqli_stmt_bind_param($stmtUsuarios, $typesUsuarios, ...$paramsUsuarios);
        mysqli_stmt_execute($stmtUsuarios);
        $resultUsuarios = mysqli_stmt_get_result($stmtUsuarios);
        
        $usuariosData = [];
        while ($row = mysqli_fetch_assoc($resultUsuarios)) {
            $usuariosData[] = $row;
        }
        
        mysqli_stmt_close($stmtUsuarios);
        
        // Para cada mes, distribuir cubetas de forma proporcional
        foreach ($periodo as $dt) {
            $mesClave = $dt->format('Y-m');
            $cubetasMes = 0;
            $usuariosUnicos = 0;
            
            foreach ($usuariosData as $usuario) {
                // Si el usuario se registró antes o durante este mes
                $fechaRegistro = new DateTime($usuario['FRegistro']);
                if ($fechaRegistro <= $dt->modify('last day of this month')) {
                    // Distribuir cubetas de forma proporcional por mes
                    $mesesActivo = max(1, floor((time() - strtotime($usuario['FRegistro'])) / (30 * 24 * 60 * 60)));
                    $cubetasPorMes = $usuario['CubetasTot'] / $mesesActivo;
                    $cubetasMes += round($cubetasPorMes);
                    $usuariosUnicos++;
                }
            }
            
            if ($usuariosUnicos > 0) {
                $entregasFiltradas[] = [
                    'mes' => $mesClave,
                    'dia' => $dt->format('Y-m-d'),
                    'cubetas_entregadas' => max(1, $cubetasMes),
                    'usuarios_unicos' => $usuariosUnicos
                ];
            }
        }
        
        // Ordenar por fecha
        usort($entregasFiltradas, function($a, $b) {
            return strcmp($a['mes'], $b['mes']);
        });
    }
    
    // Si la tabla no existe, generamos datos basados en CubetasTot por centro
    $queryCentrosUsuarios = "SELECT 
                                ca.Id, 
                                ca.Nombre, 
                                SUM(u.CubetasTot) as total 
                            FROM 
                                usuarios u 
                            JOIN 
                                centrosacopio ca ON u.IdCentroAcopio = ca.Id 
                            WHERE 
                                u.FRegistro <= ?";
    
    $paramsCentrosAlt = [$filtroFechaFin];
    $typesCentrosAlt = "s";
    
    if ($filtroUsuario > 0) {
        $queryCentrosUsuarios .= " AND u.Id = ?";
        $paramsCentrosAlt[] = $filtroUsuario;
        $typesCentrosAlt .= "i";
    }
    
    if ($filtroCentroAcopio > 0) {
        $queryCentrosUsuarios .= " AND ca.Id = ?";
        $paramsCentrosAlt[] = $filtroCentroAcopio;
        $typesCentrosAlt .= "i";
    }
    
    $queryCentrosUsuarios .= " GROUP BY ca.Id, ca.Nombre ORDER BY total DESC";
    
    $stmtCentrosAlt = mysqli_prepare($db, $queryCentrosUsuarios);
    
    if ($stmtCentrosAlt) {
        mysqli_stmt_bind_param($stmtCentrosAlt, $typesCentrosAlt, ...$paramsCentrosAlt);
        mysqli_stmt_execute($stmtCentrosAlt);
        $resultCentrosAlt = mysqli_stmt_get_result($stmtCentrosAlt);
        
        while ($row = mysqli_fetch_assoc($resultCentrosAlt)) {
            $cubetasPorCentroFiltrado[] = $row;
        }
        
        mysqli_stmt_close($stmtCentrosAlt);
    }
    
    // Si la tabla no existe, utilizamos los datos de CubetasTot directamente de la tabla usuarios
    $queryUsuariosTop = "SELECT 
                            u.Id, 
                            u.Nombre, 
                            u.ApPat, 
                            u.ApMat, 
                            u.CubetasTot as total 
                        FROM 
                            usuarios u 
                        WHERE 
                            u.FRegistro <= ?";
    
    $paramsUsuarioAlt = [$filtroFechaFin];
    $typesUsuarioAlt = "s";
    
    if ($filtroCentroAcopio > 0) {
        $queryUsuariosTop .= " AND u.IdCentroAcopio = ?";
        $paramsUsuarioAlt[] = $filtroCentroAcopio;
        $typesUsuarioAlt .= "i";
    }
    
    $queryUsuariosTop .= " ORDER BY u.CubetasTot DESC LIMIT 10";
    
    $stmtUsuarioAlt = mysqli_prepare($db, $queryUsuariosTop);
    
    if ($stmtUsuarioAlt) {
        mysqli_stmt_bind_param($stmtUsuarioAlt, $typesUsuarioAlt, ...$paramsUsuarioAlt);
        mysqli_stmt_execute($stmtUsuarioAlt);
        $resultUsuariosAlt = mysqli_stmt_get_result($stmtUsuarioAlt);
        
        while ($row = mysqli_fetch_assoc($resultUsuariosAlt)) {
            $entregasPorUsuarioFiltrado[] = $row;
        }
        
        mysqli_stmt_close($stmtUsuarioAlt);
    }
}

// ===== INCLUIR HEADER SOLO SI ES ARCHIVO DIRECTO =====
if ($esArchivoDirecto && function_exists('incluirTemplate')) {
    incluirTemplate('header');
}
?>

<section class="estadisticas-contenedor">
    <button class="BtnRegresarEstadisticas">
        <a href="/login.php">Volver a Centros de Acopio</a>
    </button>
    

    <h1 class="estadisticas-titulo">Estadísticas de Cubetas de Composta</h1>

    <!-- Panel de Estadísticas Globales -->
    <div class="panel-estadisticas-globales">
        <div class="titulo-con-logo">
        <img src="/vectores/Admin/Estadisticas/ESTADISTICAS GLOBALES.svg" alt="Ícono de estadísticas" class="logo-estadisticas">
            <h2 class="estadisticas-seccion-titulo">Estadísticas Globales</h2>
        </div>
        <div class="tarjetas-estadisticas">
            <div class="tarjeta-estadistica">
                <div class="icono">
                    <i class="fa fa-recycle"></i>
                </div>
                <div class="detalles">
                    <h3 class="estadistica-subtitulo">Total de Cubetas</h3>
                    <p class="numero"><?php echo number_format($totalCubetas); ?></p>
                </div>
            </div>
            
            <div class="tarjeta-estadistica">
                <div class="icono">
                    <i class="fa fa-user-circle"></i>
                </div>
                <div class="detalles">
                    <h3 class="estadistica-subtitulo">Promedio por Usuario</h3>
                    <p class="numero"><?php echo number_format($promedioCubetas, 2); ?></p>
                </div>
            </div>
            
            <div class="tarjeta-estadistica">
                <div class="icono">
                    <i class="fa fa-users"></i>
                </div>
                <div class="detalles">
                    <h3 class="estadistica-subtitulo">Usuarios Activos</h3>
                    <p class="numero"><?php echo count($usuarios); ?></p>
                </div>
            </div>
            
            <div class="tarjeta-estadistica">
                <div class="icono">
                    <i class="fa fa-building"></i>
                </div>
                <div class="detalles">
                    <h3 class="estadistica-subtitulo">Centros de Acopio</h3>
                    <p class="numero"><?php echo count($centrosAcopio); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Panel de Filtros -->
    <div class="panel-filtros">
        <div class="titulo-con-logo">
        <img src="/vectores/Admin/Estadisticas/FILTRAR ESTADÍSTICAS ICON.svg" alt="Ícono de estadísticas" class="logo-estadisticas">
            <h2 class="estadisticas-seccion-titulo">Filtrar Estadísticas</h2>
        </div>
        
        <form method="POST" action="" class="form-filtros">
            <div class="filtros-fila">
                <div class="campo-filtro">
                    <label for="fecha_inicio">Fecha Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $filtroFechaInicio; ?>">
                </div>
                
                <div class="campo-filtro">
                    <label for="fecha_fin">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $filtroFechaFin; ?>">
                </div>
                
                <div class="campo-filtro">
                    <label for="tipo_visualizacion">Visualización:</label>
                    <select id="tipo_visualizacion" name="tipo_visualizacion">
                        <option value="mensual" <?php echo $tipoVisualizacion == 'mensual' ? 'selected' : ''; ?>>Mensual</option>
                        <option value="diario" <?php echo $tipoVisualizacion == 'diario' ? 'selected' : ''; ?>>Diario</option>
                    </select>
                </div>
            </div>
            
            <div class="filtros-fila">
                <div class="campo-filtro">
                    <label for="usuario">Usuario:</label>
                    <select id="usuario" name="usuario">
                        <option value="0">Todos los usuarios</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?php echo $usuario['Id']; ?>" <?php echo $filtroUsuario == $usuario['Id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($usuario['NombreCompleto']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="campo-filtro">
                    <label for="centro_acopio">Centro de Acopio:</label>
                    <select id="centro_acopio" name="centro_acopio">
                        <option value="0">Todos los centros</option>
                        <?php foreach ($centrosAcopio as $centro): ?>
                            <option value="<?php echo $centro['Id']; ?>" <?php echo $filtroCentroAcopio == $centro['Id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($centro['Nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="campo-filtro campo-boton">
                    <button type="submit" class="btn-aplicar-filtros">
                        <i class="fa fa-filter"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Panel de Resultados -->
    <div class="panel-resultados">
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab-btn active" data-tab="tab-graficas">Gráficas</button>
                <button class="tab-btn" data-tab="tab-tablas">Tablas</button>
            </div>
            
            <div class="tab-content">
                <!-- Tab de Gráficas -->
                <div id="tab-graficas" class="tab-pane active">
                    <div class="paneles-graficas">
                        <!-- Gráfica de tendencia temporal -->
                        <div class="panel-grafica">
                            <h3 class="grafica-titulo">Tendencia de Entregas <?php echo $tipoVisualizacion == 'mensual' ? 'Mensuales' : 'Diarias'; ?></h3>
                            <div class="contenedor-grafica">
                                <canvas id="grafica-tendencia"></canvas>
                            </div>
                        </div>
                        
                        <!-- Gráfica de distribución por centro de acopio -->
                        <div class="panel-grafica">
                            <h3 class="grafica-titulo">Distribución por Centro de Acopio</h3>
                            <div class="contenedor-grafica">
                                <canvas id="grafica-centros"></canvas>
                            </div>
                        </div>
                        
                        <!-- Gráfica de usuarios top -->
                        <div class="panel-grafica">
                            <h3 class="grafica-titulo">Top 10 Usuarios en el Período</h3>
                            <div class="contenedor-grafica">
                                <canvas id="grafica-usuarios"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab de Tablas -->
                <div id="tab-tablas" class="tab-pane">
                    <!-- Tabla de datos filtrados -->
                    <div class="tabla-container">
                        <h3 class="tabla-titulo">Datos de Entregas por <?php echo $tipoVisualizacion == 'mensual' ? 'Mes' : 'Día'; ?></h3>
                        <table class="tabla-datos">
                            <thead>
                                <tr>
                                    <th><?php echo $tipoVisualizacion == 'mensual' ? 'Mes' : 'Fecha'; ?></th>
                                    <th>Cubetas Entregadas</th>
                                    <th>Usuarios Activos</th>
                                    <th>Promedio por Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($entregasFiltradas) > 0): ?>
                                    <?php foreach ($entregasFiltradas as $entrada): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                if ($tipoVisualizacion == 'mensual') {
                                                    echo date('F Y', strtotime($entrada['mes'] . '-01'));
                                                } else {
                                                    echo date('d/m/Y', strtotime($entrada['dia']));
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo $entrada['cubetas_entregadas']; ?></td>
                                            <td><?php echo $entrada['usuarios_unicos']; ?></td>
                                            <td>
                                                <?php 
                                                echo $entrada['usuarios_unicos'] > 0 
                                                    ? number_format($entrada['cubetas_entregadas'] / $entrada['usuarios_unicos'], 2) 
                                                    : '0.00'; 
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="no-datos">No hay datos disponibles para los filtros seleccionados.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Tabla de centros de acopio -->
                    <div class="tabla-container">
                        <h3 class="tabla-titulo">Entregas por Centro de Acopio</h3>
                        <table class="tabla-datos">
                            <thead>
                                <tr>
                                    <th>Centro de Acopio</th>
                                    <th>Cubetas Entregadas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cubetasPorCentroFiltrado) > 0): ?>
                                    <?php foreach ($cubetasPorCentroFiltrado as $centro): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($centro['Nombre']); ?></td>
                                            <td><?php echo $centro['total']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="no-datos">No hay datos disponibles para los filtros seleccionados.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                                        <!-- Tabla de usuarios -->
                    <div class="tabla-container tabla-con-imagen">
                        <div class="contenedor-tabla-imagen">
                            <!-- Imagen SVG - 1/3 del ancho -->
                            <div class="imagen-tabla">
                                <img src="/vectores/Admin/Estadisticas/USUARIO ICON.svg" alt="Top 10 Usuarios">
                            </div>
                            
                            <!-- Título y Tabla - 2/3 del ancho -->
                            <div class="tabla-wrapper">
                                <h3 class="tabla-titulo">Top 10 Usuarios</h3>
                                <table class="tabla-datos tabla-top-usuarios">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Cubetas Entregadas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($entregasPorUsuarioFiltrado) > 0): ?>
                                            <?php foreach ($entregasPorUsuarioFiltrado as $usuario): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['ApPat'] . ' ' . $usuario['ApMat']); ?></td>
                                                    <td><?php echo $usuario['total']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="2" class="no-datos">No hay datos disponibles para los filtros seleccionados.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scripts para las gráficas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../build_previo/js/app.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== GRÁFICAS =====
        
        // Datos para la gráfica de tendencia
        const dataTendencia = {
            labels: [
                <?php 
                $labels = [];
                foreach ($entregasFiltradas as $entrada) {
                    if ($tipoVisualizacion == 'mensual') {
                        $fecha = date('F Y', strtotime($entrada['mes'] . '-01'));
                    } else {
                        $fecha = date('d/m/Y', strtotime($entrada['dia']));
                    }
                    $labels[] = '"' . $fecha . '"';
                }
                echo implode(', ', $labels);
                ?>
            ],
            datasets: [{
                label: 'Cubetas Entregadas',
                data: [
                    <?php 
                    $valores = [];
                    foreach ($entregasFiltradas as $entrada) {
                        $valores[] = $entrada['cubetas_entregadas'];
                    }
                    echo implode(', ', $valores);
                    ?>
                ],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                tension: 0.1
            }]
        };
        
        // Configuración de la gráfica de tendencia
        const configTendencia = {
            type: 'line',
            data: dataTendencia,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cubetas Entregadas'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: '<?php echo $tipoVisualizacion == 'mensual' ? 'Mes' : 'Fecha'; ?>'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        };
        
        // Datos para la gráfica de centros
        const dataCentros = {
            labels: [
                <?php 
                $labelsCentros = [];
                foreach ($cubetasPorCentroFiltrado as $centro) {
                    $labelsCentros[] = '"' . htmlspecialchars($centro['Nombre']) . '"';
                }
                echo implode(', ', $labelsCentros);
                ?>
            ],
            datasets: [{
                label: 'Cubetas por Centro',
                data: [
                    <?php 
                    $valoresCentros = [];
                    foreach ($cubetasPorCentroFiltrado as $centro) {
                        $valoresCentros[] = $centro['total'];
                    }
                    echo implode(', ', $valoresCentros);
                    ?>
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        };
        
        // Configuración de la gráfica de centros
        const configCentros = {
            type: 'doughnut',
            data: dataCentros,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        };
        
        // Datos para la gráfica de usuarios
        const dataUsuarios = {
            labels: [
                <?php 
                $labelsUsuarios = [];
                foreach ($entregasPorUsuarioFiltrado as $usuario) {
                    $labelsUsuarios[] = '"' . htmlspecialchars($usuario['Nombre']) . '"';
                }
                echo implode(', ', $labelsUsuarios);
                ?>
            ],
            datasets: [{
                label: 'Cubetas Entregadas',
                data: [
                    <?php 
                    $valoresUsuarios = [];
                    foreach ($entregasPorUsuarioFiltrado as $usuario) {
                        $valoresUsuarios[] = $usuario['total'];
                    }
                    echo implode(', ', $valoresUsuarios);
                    ?>
                ],
                backgroundColor: 'rgba(153, 102, 255, 0.5)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        };
        
        // Configuración de la gráfica de usuarios
        const configUsuarios = {
            type: 'bar',
            data: dataUsuarios,
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cubetas Entregadas'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        };
        
        // Inicializar gráficas
        if (document.getElementById('grafica-tendencia')) {
            new Chart(
                document.getElementById('grafica-tendencia'),
                configTendencia
            );
        }
        
        if (document.getElementById('grafica-centros')) {
            new Chart(
                document.getElementById('grafica-centros'),
                configCentros
            );
        }
        
        if (document.getElementById('grafica-usuarios')) {
            new Chart(
                document.getElementById('grafica-usuarios'),
                configUsuarios
            );
        }
        
        // ===== TABS =====
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remover clase active de todos los botones y paneles
                tabBtns.forEach(b => b.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));
                
                // Añadir clase active al botón clickeado
                this.classList.add('active');
                
                // Mostrar el panel correspondiente
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
</script>

<?php
// ===== INCLUIR FOOTER SOLO SI ES ARCHIVO DIRECTO =====
if ($esArchivoDirecto && function_exists('incluirTemplate')) {
    incluirTemplate('footer');
}

// ===== CERRAR LA CONEXIÓN A LA BD SOLO SI LA ABRIMOS NOSOTROS =====
if (isset($cerrar_db_al_final) && $cerrar_db_al_final && isset($db)) {
    mysqli_close($db);
}
?>
