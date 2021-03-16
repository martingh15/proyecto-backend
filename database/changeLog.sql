-- Tabla Usuarios
CREATE TABLE `usuarios` (
    `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
    `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `tokenEmail` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `tokenReset` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `fechaTokenReset` datetime NULL DEFAULT NULL,
    `habilitado` tinyint(1) NOT NULL DEFAULT '0',
    `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `ultHoraMdf` datetime NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `usuarios_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla Zonas
CREATE TABLE `zonas` (
     `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
     `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablas Animales
CREATE TABLE `animales` (
        `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
        `idZona` int unsigned NOT NULL,
        `idBarrio` int unsigned NULL,
        `nombre` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `imagenPrincipal` varchar(191) DEFAULT NULL,
        `fileImagenPrincipal` varchar(191) DEFAULT NULL,
        `imagenSecundaria` varchar(191) DEFAULT NULL,
        `fileImagenSecundaria` varchar(191) DEFAULT NULL,
        `sexo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
        `tipo` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
        `edadAproximada` int unsigned NULL,
        `castrado` tinyint(1) NOT NULL,
        `tamanio` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
        `particularidades` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `idCreador` int unsigned NOT NULL,
        `ultUsuarioMdf` int unsigned NOT NULL,
        `ultHoraMdf` datetime NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `animales_idcreador_foreign` (`idCreador`),
        KEY `animales_ultusuariomdf_foreign` (`ultUsuarioMdf`),
        CONSTRAINT `animales_idcreador_foreign` FOREIGN KEY (`idCreador`) REFERENCES `usuarios` (`id`),
        CONSTRAINT `animales_ultusuariomdf_foreign` FOREIGN KEY (`ultUsuarioMdf`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE animales ADD FOREIGN KEY (`idZona`) REFERENCES zonas(`id`);

--  Tabla Animales Perdidos
CREATE TABLE `animales_perdidos` (
     `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
     `idAnimal` int unsigned NOT NULL,
     `fecha` datetime NOT NULL,
     `celularDuenio` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
     `celularSecundario` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `habilitado` tinyint(1) NOT NULL DEFAULT '0',
     `resuelto`  tinyint(1) NOT NULL DEFAULT '0',
     `idCreador` int unsigned NOT NULL,
     `ultUsuarioMdf` int unsigned NOT NULL,
     `ultHoraMdf` datetime NULL DEFAULT NULL,
     PRIMARY KEY (`id`),
     KEY `animales_perdidos_idcreador_foreign` (`idCreador`),
     KEY `animales_perdidos_ultusuariomdf_foreign` (`ultUsuarioMdf`),
     CONSTRAINT `animales_perdidos_idcreador_foreign` FOREIGN KEY (`idCreador`) REFERENCES `usuarios` (`id`),
     CONSTRAINT `animales_perdidos_ultusuariomdf_foreign` FOREIGN KEY (`ultUsuarioMdf`) REFERENCES `usuarios` (`id`),
     UNIQUE KEY `animales_perdidos_unique` (`idAnimal`,`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE UNIQUE INDEX index_animales_perdidos ON animales_perdidos (idAnimal);

-- Tabla Animales Encontrados
CREATE TABLE `animales_encontrados` (
    `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
     `idAnimal` int unsigned NOT NULL,
     `fecha` datetime NOT NULL,
     `celularDuenio` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
     `celularSecundario` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `habilitado` tinyint(1) NOT NULL DEFAULT '0',
     `resuelto`  tinyint(1) NOT NULL DEFAULT '0',
     `idCreador` int unsigned NOT NULL,
     `ultUsuarioMdf` int unsigned NOT NULL,
     `ultHoraMdf` datetime NULL DEFAULT NULL,
     PRIMARY KEY (`id`),
     KEY `animales_encontrados_idcreador_foreign` (`idCreador`),
     KEY `animales_encontrados_ultusuariomdf_foreign` (`ultUsuarioMdf`),
     CONSTRAINT `animales_encontrados_idcreador_foreign` FOREIGN KEY (`idCreador`) REFERENCES `usuarios` (`id`),
     CONSTRAINT `animales_encontrados_ultusuariomdf_foreign` FOREIGN KEY (`ultUsuarioMdf`) REFERENCES `usuarios` (`id`),
    UNIQUE KEY `animales_encontrados_unique` (`idAnimal`, `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE UNIQUE INDEX index_animales_encontrados_idAnimal ON animales_encontrados (idAnimal);

-- Tabla Barrios
CREATE TABLE `barrios` (
   `id` int(4) unsigned NOT NULL AUTO_INCREMENT,
   `nombre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Claves foráneas de Animales Encontrados
ALTER TABLE animales_encontrados ADD FOREIGN KEY (`idAnimal`) REFERENCES animales(`id`);

-- Claves foráneas de Animales Perdidos
ALTER TABLE animales_perdidos ADD FOREIGN KEY (`idAnimal`) REFERENCES animales(`id`);

insert into `barrios` values(default,'Abasto');
insert into `barrios` values(default,'Alberdi');
insert into `barrios` values(default,'Lisandro de la Torre');
insert into `barrios` values(default,'Parque');
insert into `barrios` values(default,'Belgrano');
insert into `barrios` values(default,'Domingo Matheu');
insert into `barrios` values(default,'Echesortu');
insert into `barrios` values(default,'Empalpe Graneros');
insert into `barrios` values(default,'Fisherton');
insert into `barrios` values(default,'General San Martín');
insert into `barrios` values(default,'Grandoli');
insert into `barrios` values(default,'Ludueña');
insert into `barrios` values(default,'Martin');
insert into `barrios` values(default,'Parque Field');
insert into `barrios` values(default,'Pichincha');
insert into `barrios` values(default,'Puerto Norte');
insert into `barrios` values(default,'Islas Malvinas');
insert into `barrios` values(default,'República de la Sexta');
insert into `barrios` values(default,'Roque Saenz Peña');
insert into `barrios` values(default,'José Ignacio Rucci');
insert into `barrios` values(default,'Saladillo');
insert into `barrios` values(default,'Sorrento');

insert into `zonas` values(default,'Distrito Centro "Antonio Berni"');
insert into `zonas` values(default,'Distrito Norte "Villa Hortensia"');
insert into `zonas` values(default,'Distrito Noroeste "Olga y Leticia Cossettini"');
insert into `zonas` values(default,'Distrito Oeste "Felipe Moré"');
insert into `zonas` values(default,'Distrito Sudoeste "Emilia Bertolé"');
insert into `zonas` values(default,'Distrito Sur "Rosa Ziperovich"');

insert into usuarios values(default, 'Martin', 'martinghiotti2013@gmail.com', null,null,null,1, '$2y$10$YwIBg.N2k3NiV4tuXDtBme.cAORjAIlBqiuBraF4iXfiZQvV574BG', '2020-05-07 00:00:00');


insert into animales values(default,2,16,'Gaia','1-1-5eb3e79942910.jpeg','gaia.jpeg',null,null,0,'perro',2,1,'1,','No tiene nada particular',1,1,'2020-05-07 00:00:00');
insert into animales values(default,2,16,'Toby','2-1-5eb3e82da5672.jpeg','toby.jpeg',null,null,0,'perro',1,1,'1,','Le falta una patita',1,1,'2020-05-08 00:00:00');
insert into animales values(default,2,16,'Tom','3-1-5eb3e855b77c6.jpeg','tom.jpeg',null,null,0,'gato',1,1,'1,2','Le falta una oreja',1,1,'2020-05-09 00:00:00');
insert into animales values(default,2,14,'Willy','4-1-5eb3e8c908f38.jpeg','willy.jpeg',null,null,1,'gato',2,1,'2,3,','No tiene nada particular',1,1,'2020-05-09 00:00:00');
insert into animales values(default,6,19,'Piri','5-1-5eb3f17cb5719.png','piri.jpeg',null,null,1,'gato',3,0,2,'',1,1,'2020-04-09 00:00:00');
insert into animales values(default,6,19,'Jack','6-1-5eb46e5f38fe5.jpeg','jack.jpeg',null,null,1,'perro',1,1,1,'',1,1,'2020-04-09 00:00:00');

insert into animales_perdidos values(default,1,'2020-04-29 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_perdidos values(default,2,'2020-04-29 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_perdidos values(default,3,'2020-04-29 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_perdidos values(default,4,'2020-04-27 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_perdidos values(default,5,'2020-04-28 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_perdidos values(default,6,'2020-04-30 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');


insert into animales_encontrados values(default,1,'2020-05-29 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_encontrados values(default,2,'2020-05-29 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_encontrados values(default,3,'2020-05-29 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_encontrados values(default,4,'2020-05-27 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_encontrados values(default,5,'2020-05-28 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
insert into animales_encontrados values(default,6,'2020-05-30 00:00:00','3411918193',null,0,0,1,1,'2020-05-07 00:00:00');
