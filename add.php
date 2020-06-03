<?php
// Запуск сессии
session_start();

// Подключение библиотек
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/functions.php';


// Назначение title
$title = "Дела в порядке";

// Определдение пользователя
$user = get_user();

if (!$user) {
    header("Location: /guest.php");
    exit;
}

// Подключение к БД
$con = connect_db();

// Получение значения текущего id проекта
$current_project_id = $_GET['project_id'] ?? 0;

// Получение списка проектов
$project_rows = get_project_rows($user);

$add = $_POST['add'] ?? false;
if ($add) {
    $task_title = $_POST['name'];
    $task_project_id = $_POST['project'];
    $task_date = $_POST['date'];
    $task_file = $_FILES['file'];

    $errors = validate_task_form($task_title, $task_project_id, $task_date);
    if (count($errors) === 0) {
        if (add_task($user, $task_title, $task_project_id, $task_date, $task_file)) {
            header("Location: /");
            exit;
        }
    }
} else {
    $errors = [];
}

// Меню (список проектов)
$menu = include_template('menu-project.php', compact('current_project_id', 'project_rows'));
// Форма добавления задачи
$page_content = include_template('form-task.php', compact('menu', 'project_rows', 'errors'));


// окончательный HTML код
$layout_content = include_template('layout.php', compact('page_content', 'title', 'user'));

// Вывод контента
print($layout_content);
