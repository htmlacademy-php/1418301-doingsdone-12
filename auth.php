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

// Подключение к БД
$con = connect_db();


$auth = $_POST['auth'] ?? false;
if ($auth) {
    $auth_email = $_POST['email'];
    $auth_password = $_POST['password'];

    $errors = validate_auth_form($auth_email, $auth_password);
    if (count($errors) === 0) {
        if (auth($auth_email, $auth_password)) {
            header("Location: /");
            exit;
        } else {
            $errors['auth'] = 'Не правильное сочетание логин пароль';
        }
    }
} else {
    $errors = [];
}


// Форма входа
$page_content = include_template('auth.php', compact('errors'));

// окончательный HTML код
$layout_content = include_template('layout.php', compact('page_content', 'title', 'user'));

// Вывод контента
print($layout_content);
