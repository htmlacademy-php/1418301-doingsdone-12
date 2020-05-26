<?php
// Подключение библиотек
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/functions.php';

// Запуск сессии
session_start();

// Назначение title
$title = "Дела в порядке";

// Определдение пользователя
$user = get_user();

if (!$user) {
    header("Location: /guest.php");
    exit;
}

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);


// Подключение к БД
$con = connect_db();

// Получение значения текущего id проекта
$current_project_id = $_GET['project_id'] ?? 0;
// Проверка на корректность id проекта. Если не корректный, то возвращаем код 404
if ($current_project_id) {
    if (!validate_project_id($current_project_id)) {
        http_response_code(404);
        exit;
    }
}
// Получение строки поиска
if (isset($_GET['query'])) {
    $query = htmlspecialchars($_GET['query']);
} else {
    $query = '';
}
// Получение значения фильтра
if (isset($_GET['filter'])) {
    $filter = htmlspecialchars($_GET['filter']);
} else {
    $filter = '';
}

if (isset($_GET['set_task_execute']) && isset($_GET['status'])) {
    $task_id = $_GET['set_task_execute'];
    $status = $_GET['status'];
    if (set_task_execute($task_id, $status, $user)) {
        header("Location: /");
        exit;
    }
}


// Получение списка проектов
$project_rows = get_project_rows($user);

// Получение списка задач
$task_rows = get_task_rows($user, $current_project_id, $query, $filter);

// Меню (список проектов)
$menu = include_template('menu-project.php', compact('current_project_id', 'project_rows'));
// HTML код главной страницы
$page_content = include_template('main.php', compact('menu', 'show_complete_tasks', 'current_project_id', 'task_rows', 'filter'));


// окончательный HTML код
$layout_content = include_template('layout.php', compact('page_content', 'title', 'user'));

// Вывод контента
print($layout_content);
