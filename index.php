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

// Получение значения текущего id проекта
$current_project_id = $_GET['prj_id'] ?? 0;
// Проверка на корректность id проекта. Если не корректный, то возвращаем код 404
if ($current_project_id) {
    $validate = validate_project_id($current_project_id, $con);
    if (!$validate) {
        http_response_code(404);
        exit;
    }
}


// Получение списка проектов
$project_rows = get_project_rows($con);

// Получение списока задач
$task_rows = get_task_rows($current_project_id, $con);



// HTML код главной страницы
$page_content = include_template('main.php', compact('show_complete_tasks', 'current_project_id', 'project_rows', 'task_rows'));

// окончательный HTML код
$layout_content = include_template('layout.php', compact('page_content', 'title'));

// Вывод контента
print($layout_content);
