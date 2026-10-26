-- Base de datos única del proyecto (Railway MySQL)
-- Importa este archivo en DBeaver sobre la base que uses como DB_NAME
-- (por ejemplo "railway"). La app también crea las tablas sola si no existen.

CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `stock` INT UNSIGNED NOT NULL DEFAULT 0,
    `photo1` VARCHAR(255) NOT NULL,
    `photo2` VARCHAR(255) NOT NULL,
    `photo3` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `upvm_survey_responses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `career` VARCHAR(150) NOT NULL,
    `shift` VARCHAR(100) NOT NULL,
    `teachers_opinion` VARCHAR(255) NOT NULL,
    `improve` VARCHAR(500) NOT NULL,
    `dislike` VARCHAR(500) NOT NULL,
    `likes_career` VARCHAR(100) NOT NULL,
    `constructive_criticism` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL,
    `correo` VARCHAR(254) NOT NULL,
    `sexo` ENUM('hombre','mujer','prefiero no decirlo') NOT NULL,
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
