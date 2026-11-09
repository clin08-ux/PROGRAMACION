CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL,
    `correo` VARCHAR(254) NOT NULL,
    `sexo` ENUM(
        'hombre',
        'mujer',
        'prefiero no decirlo'
    ) NOT NULL,
    `edad` TINYINT UNSIGNED NOT NULL,
    `carrera` ENUM(
        'administracion',
        'ingenieria en tecnologias de la informacion',
        'enfermeria',
        'mecatronica',
        'logistica',
        'ingenieria en alimentos',
        'ingenieria industrial',
        'robotica'
    ) NOT NULL,
    `contrasena` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
