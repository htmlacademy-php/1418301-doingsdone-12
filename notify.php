<?php
// Подключение библиотек
require_once __DIR__.'/vendor/autoload.php';

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

// Подключение к БД
$con = connect_db();

$mailing_task_rows = get_mailing_task_rows();
if ($mailing_task_rows) {
    $cnt = send_mail($mailing_task_rows);
}


print "Отправлено писем: " .$cnt;