<?php
// Подключение библиотек
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/functions.php';

// Назначение title
$title = "Дела в порядке";
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);


// Подключение к БД
$con = connect_db();

// Получение списока проектов
$sql_projects = "SELECT * FROM `projects` WHERE `id_user` = 1";
$result_projects = mysqli_query($con, $sql_projects);
$project_rows = mysqli_fetch_all($result_projects, MYSQLI_ASSOC);

// Получаем список задач
$sql_tasks = "SELECT `t`.`title`, `t`.`status`, `t`.`date_execute`, `p`.`title` AS `category` FROM `tasks` AS `t` JOIN `projects` AS `p` ON `p`.`id` = `t`.`id_project` WHERE `t`.`id_user` = 1";
$result_tasks = mysqli_query($con, $sql_tasks);
$task_rows = mysqli_fetch_all($result_tasks, MYSQLI_ASSOC);




// HTML код главной страницы
$page_content = include_template('main.php', compact('show_complete_tasks', 'project_rows', 'task_rows'));

// окончательный HTML код
$layout_content = include_template('layout.php', compact('page_content', 'title'));

// Вывод контента
print($layout_content);
