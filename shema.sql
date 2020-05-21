/* Создание БД */

CREATE DATABASE `doingsdone`
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE `doingsdone`;
    

/* Создание таблиц */

CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(128) NOT NULL UNIQUE,
    `password` CHAR(64) NOT NULL,
    `name` VARCHAR(50) NOT NULL
);

CREATE TABLE `projects` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `id_user` INT NOT NULL
);

CREATE TABLE `tasks` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `id_project` INT NOT NULL,
    `id_user` INT NOT NULL,
    `date_create` DATETIME NOT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 0,
    `file` VARCHAR(500) NULL,
    `date_execute` DATETIME NULL
);


/* Создание индексов */

CREATE INDEX `user_email` ON `users`(`email`);
CREATE INDEX `project_user` ON `projects`(`id_user`);
CREATE INDEX `task_user` ON `tasks`(`id_user`);
CREATE INDEX `task_project` ON `tasks`(`id_project`);


/* Создание полнотекстового индекса */

CREATE FULLTEXT INDEX `task_ft_search` ON `tasks`(`title`);