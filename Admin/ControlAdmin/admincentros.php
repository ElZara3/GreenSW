<?php
    verificarSesionActiva('Admin');
?>

<section class="administrar-centros">
    <div class="tabla-centros">
        <!-- Título principal que abarca todo el ancho -->
        <div class="fila-titulo">
            <span>Administrar Usuarios</span>
        </div>

        <!-- Subtítulo que abarca todo el ancho -->
        <div class="fila-subtitulo">
            <span>Centros de Acopio</span>
        </div>

        <!-- Fila con botón de estadísticas -->
        <div class="fila-estadisticas">
            <a href="/Admin/AdUsers/estadisticas.php" class="boton-estadisticas">Estadísticas</a>
        </div>

        <!-- Fila con centros de acopio -->
        <div class="fila-centros">
            <!-- Centro 1: Satélite -->
            <div class="centro-acopio">
                <div class="centro-contenido">
                    <div class="centro-nombre">Zona Azúl</div>
                    <div class="centro-icono">
                        <img src="/vectores/Admin/CentrosAdmin/ZONA AZUL ADMIN.svg" alt="1 Satélite zona azul">
                    </div>
                </div>
                <div class="centro-botones">
                    <a href="/Admin/AdUsers/registro.php?centro=1" class="boton-registro">Registro</a>
                    <a href="/Admin/AdUsers/busqueda.php?centro=1" class="boton-busqueda">
                        <i class="fa fa-search"></i> Búsqueda
                    </a>
                </div>
            </div>

            <!-- Centro 2: Chiluca -->
            <div class="centro-acopio">
                <div class="centro-contenido">
                    <div class="centro-nombre">Chiluca</div>
                    <div class="centro-icono">
                        <img src="/vectores/Admin/CentrosAdmin/CHILUCA.svg" alt="2 Chiluca">
                    </div>
                </div>
                <div class="centro-botones">
                    <a href="/Admin/AdUsers/registro.php?centro=2" class="boton-registro">Registro</a>
                    <a href="/Admin/AdUsers/busqueda.php?centro=2" class="boton-busqueda">
                        <i class="fa fa-search"></i> Búsqueda
                    </a>
                </div>
            </div>

            <!-- Centro 3: La Florida -->
            <div class="centro-acopio">
                <div class="centro-contenido">
                    <div class="centro-nombre">La Florida</div>
                    <div class="centro-icono">
                        <img src="/vectores/Admin/CentrosAdmin/LA FLORIDA ICO.svg" alt="3 La florida">
                    </div>
                </div>
                <div class="centro-botones">
                    <a href="/Admin/AdUsers/registro.php?centro=3" class="boton-registro">Registro</a>
                    <a href="/Admin/AdUsers/busqueda.php?centro=3" class="boton-busqueda">
                        <i class="fa fa-search"></i> Búsqueda
                    </a>
                </div>
            </div>

            <!-- Centro 4: Paseo Echegaray -->
            <div class="centro-acopio">
                <div class="centro-contenido">
                    <div class="centro-nombre">Paseo Echegaray</div>
                    <div class="centro-icono">
                        <img src="/vectores/Admin/CentrosAdmin/PASEO ECHEGARAI.svg" alt="4 Paseo de echegaray">
                    </div>
                </div>
                <div class="centro-botones">
                    <a href="/Admin/AdUsers/registro.php?centro=4" class="boton-registro">Registro</a>
                    <a href="/Admin/AdUsers/busqueda.php?centro=4" class="boton-busqueda">
                        <i class="fa fa-search"></i> Búsqueda
                    </a>
                </div>
            </div>

            <!-- Centro 5: Sayavedra -->
            <div class="centro-acopio">
                <div class="centro-contenido">
                    <div class="centro-nombre">Sayavedra</div>
                    <div class="centro-icono">
                        <img src="/vectores/Admin/CentrosAdmin/SAYAVEDRA.svg" alt="5 Condado de sayavedra">
                    </div>
                </div>
                <div class="centro-botones">
                    <a href="/Admin/AdUsers/registro.php?centro=5" class="boton-registro">Registro</a>
                    <a href="/Admin/AdUsers/busqueda.php?centro=5" class="boton-busqueda">
                        <i class="fa fa-search"></i> Búsqueda
                    </a>
                </div>
            </div>

            <!-- Centro 6: Zona Azul -->
            <div class="centro-acopio">
                <div class="centro-contenido">
                    <div class="centro-nombre">Centro Cívico</div>
                    <div class="centro-icono">
                        <img src="/vectores/Admin/CentrosAdmin/SATÉLITE.svg" alt="6 Satélite centro civico">
                    </div>
                </div>
                <div class="centro-botones">
                    <a href="/Admin/AdUsers/registro.php?centro=6" class="boton-registro">Registro</a>
                    <a href="/Admin/AdUsers/busqueda.php?centro=6" class="boton-busqueda">
                        <i class="fa fa-search"></i> Búsqueda
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
