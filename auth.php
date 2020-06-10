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

// Подключение к БД
$link = connect_db();

// Инициализируем массив с ошибками
$errors = [];

$auth = $_POST['auth'] ?? false;
if ($auth) {
    $auth_email = mysqli_real_escape_string($link, $_POST['email']);
    $auth_password = mysqli_real_escape_string($link, $_POST['password']);

    $errors = validate_auth_form($auth_email, $auth_password);
    if (count($errors) === 0) {
        if (auth($auth_email, $auth_password)) {
            header("Location: /");
            exit;
        } else {
            $errors['auth'] = 'Не правильное сочетание логин пароль';
        }
    }
}

// Форма входа
$page_content = include_template('auth.php', compact('errors'));

// окончательный HTML код
$layout_content = include_template('layout.php', compact('page_content', 'title', 'user'));

// Вывод контента
print($layout_content);
