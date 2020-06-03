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

// Подключение к БД
$con = connect_db();

// Получение значения текущего id проекта
$current_project_id = $_GET['project_id'] ?? 0;

// Получение списка проектов
$project_rows = get_project_rows($user);

$registration = $_POST['registration'] ?? false;
if ($registration) {
    $reg_email = $_POST['email'];
    $reg_password = $_POST['password'];
    $reg_name = $_POST['name'];

    $errors = validate_registration_form($reg_email, $reg_password, $reg_name);
    if (count($errors) === 0) {
        if (add_user($reg_email, $reg_password, $reg_name)) {
            header("Location: /");
            exit;
        }
    }
} else {
    $errors = [];
}

// Меню (список проектов)
$menu = include_template('menu-project.php', compact('current_project_id', 'project_rows'));
// Форма регистрации
$page_content = include_template('form-registration.php', compact('menu', 'errors'));


// окончательный HTML код
$layout_content = include_template('layout.php', compact('page_content', 'title', 'user'));

// Вывод контента
print($layout_content);
