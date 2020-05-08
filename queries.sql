USE `doingsdone`;

/* Список пользователей */

INSERT INTO `users` (`email`, `password`, `name`) VALUES ('admin@doingsdone.ru', 'qwerty1', 'Администратор'); /* id = 1 */
INSERT INTO `users` (`email`, `password`, `name`) VALUES ('user@doingsdone.ru', 'qwerty2', 'Пользователь'); /* id = 2 */

/* Список проектов */

INSERT INTO `projects` (`title`, `id_user`) VALUES ('Входящие', 1); /* id = 1 */
INSERT INTO `projects` (`title`, `id_user`) VALUES ('Учеба', 1); /* id = 2 */
INSERT INTO `projects` (`title`, `id_user`) VALUES ('Работа', 1); /* id = 3 */
INSERT INTO `projects` (`title`, `id_user`) VALUES ('Домашние дела', 1); /* id = 4 */
INSERT INTO `projects` (`title`, `id_user`) VALUES ('Авто', 1); /* id = 5 */


/* Список задач */

INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `status`, `date_execute`) VALUES ('Собеседование в IT компании', 1, 3, NOW(), 0, '2020-11-01');
INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `status`, `date_execute`) VALUES ('Выполнить тестовое задание', 1, 3, NOW(), 0, '2019-12-25');
INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `status`, `date_execute`) VALUES ('Сделать задание первого раздела', 1, 2, NOW(), 1, '2019-12-21');
INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `status`, `date_execute`) VALUES ('Встреча с другом', 1, 1, NOW(), 0, '2020-05-04');
INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `status`, `date_execute`) VALUES ('Купить корм для кота', 1, 1, NOW(), 0, NULL);
INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `status`, `date_execute`) VALUES ('Заказать пиццу', 1, 4, NOW(), 0, NULL);


/* Запросы на выборку */

/* получить список из всех проектов для одного пользователя. c id_user = 1. это Администратор */
SELECT * FROM `projects` WHERE `id_user` = 1;

/* получить список из всех задач для одного проекта. с id_project = 3. это Работа */
SELECT * FROM `tasks` WHERE `id_project` = 3;

/* пометить задачу как выполненную. задачу с id = 4 */
UPDATE `tasks` SET `status` = 1 WHERE `id` = 4;

/* обновить название задачи по её идентификатору. задачу с id = 4 */
UPDATE `tasks` SET `title` = 'Встреча с лучщим другом' WHERE `id` = 4;