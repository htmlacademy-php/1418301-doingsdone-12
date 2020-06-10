<?php
// Запуск сессии
session_start();

// Подключение библиотек
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/functions.php';


// Назначение title
$title = "Дела в порядке";

// Определдение пользователя
$user = get_user();

if (!$user) {
    header("Location: /guest.php");
    exit;
}

// Подключение к БД
$link = connect_db();

// Получение значения текущего id проекта
$current_project_id = $_GET['project_id'] ?? 0;
$current_project_id = mysqli_real_escape_string($link, (string)$current_project_id);

// Получение списка проектов
$project_rows = get_project_rows($user);

// Инициализируем массив с ошибками
$errors = [];

$add = $_POST['add'] ?? false;
if ($add) {
    $task_title = mysqli_real_escape_string($link, $_POST['name']);
    $task_project_id = mysqli_real_escape_string($link, $_POST['project']) ?? 0;
    $task_date = $_POST['date'];
    $task_file = $_FILES['file'];

    $errors = validate_task_form($task_title, $task_project_id, $task_date);
    if (count($errors) === 0) {
        if (add_task($user, $task_title, $task_project_id, $task_date, $task_file)) {
            header("Location: /");
            exit;
        }
    }
}

// Меню (список проектов)
$menu = include_template('menu-project.php', compact('current_project_id', 'project_rows'));
// Форма добавления задачи
$page_content = include_template('form-task.php', compact('menu', 'project_rows', 'errors'));


// окончательный HTML код
$layout_content = include_template('layout.php', compact('page_content', 'title', 'user'));

// Вывод контента
print($layout_content);
