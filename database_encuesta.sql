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