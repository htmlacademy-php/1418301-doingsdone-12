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
$current_project_id = $_GET['project_id'] ?? '';
$current_project_id = mysqli_real_escape_string($link, (string)$current_project_id);

// Получение списка проектов
$project_rows = get_project_rows($user);

// Инициализируем массив с ошибками
$errors = [];

$add = $_POST['add'] ?? false;
if ($add) {
    $project_title = mysqli_real_escape_string($link, getPostVal('name'));

    $errors = validate_project_form($project_title);
    if (count($errors) === 0) {
        if (add_project($user, $project_title)) {
            header("Location: /");
            exit;
        }
    }
}

// Меню (список проектов)
$menu = include_template('menu-project.php', compact('current_project_id', 'project_rows'));
// Форма добавления задачи
$page_content = include_template('form-project.php', compact('menu', 'project_rows', 'errors'));


// окончательный HTML код
$layout_content = include_template('layout.php', compact('page_content', 'title', 'user'));

// Вывод контента
print($layout_content);
