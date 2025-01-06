USE vivec

CREATE TABLE Usuarios (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    ApPat VARCHAR(100) NOT NULL,
    ApMat VARCHAR(100),
    Telefono VARCHAR(15),
    FNacimiento DATE,
    FRegistro DATE NOT NULL,
    Rol VARCHAR(50),
    Correo VARCHAR(150),
    IdCentroAcopio INT,
    FOREIGN KEY (IdCentroAcopio) REFERENCES CentrosAcopio(Id)
);

CREATE TABLE CentrosAcopio (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Horarios VARCHAR(100),
    Ubicacion VARCHAR(200)
);

CREATE TABLE VisitasCentroAcopio (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    IdCentroAcopio INT NOT NULL,
    NVisitas INT DEFAULT 0,
    FOREIGN KEY (IdCentroAcopio) REFERENCES CentrosAcopio(Id)
);

CREATE TABLE VisitasUsuario (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    IdUsuario INT NOT NULL,
    IdCentroAcopio INT NOT NULL,
    Fecha DATE NOT NULL,
    FOREIGN KEY (IdUsuario) REFERENCES Usuarios(Id),
    FOREIGN KEY (IdCentroAcopio) REFERENCES CentrosAcopio(Id)
);

CREATE TABLE Testimonios (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    IdUsuario INT NOT NULL,
    Testimonio TEXT,
    Fecha DATE NOT NULL,
    FOREIGN KEY (IdUsuario) REFERENCES Usuarios(Id)
);

CREATE TABLE Insignias (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Descripcion VARCHAR(200),
    CubetasNecesarias INT NOT NULL
);

CREATE TABLE InsigniasUsuario (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    IdUsuario INT NOT NULL,
    IdInsignias INT NOT NULL,
    Fecha DATE NOT NULL,
    FOREIGN KEY (IdUsuario) REFERENCES Usuarios(Id),
    FOREIGN KEY (IdInsignias) REFERENCES Insignias(Id)
);

ALTER TABLE vivecomposta.usuarios 
ADD COLUMN CubetasTot DOUBLE NOT NULL DEFAULT 0 AFTER IdCentroAcopio;


ALTER TABLE usuarios 
ADD COLUMN Contrasena VARCHAR(255) NOT NULL AFTER IdCentroAcopio;


ALTER TABLE `vivecomposta`.`usuarios` 
CHANGE COLUMN `Rol` `Rol` VARCHAR(50) NOT NULL DEFAULT 'User' ;


ALTER TABLE `vivecomposta`.`usuarios` 
CHANGE COLUMN `Telefono` `Telefono` VARCHAR(15) NOT NULL ,
ADD UNIQUE INDEX `Telefono_UNIQUE` (`Telefono` ASC) VISIBLE;
;


