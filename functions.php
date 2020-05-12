<?php

/* Подключение к БД */
function connect_db()
{
    static $host = "localhost";
    static $user = "root";
    static $password = "";
    static $db = "doingsdone";

    $con = mysqli_connect($host, $user, $password, $db);

    return $con;
}

/* Подсчет кол-ва задач в проекте */
function task_count($arr, $category)
{
    $count = 0;
    foreach ($arr as $val)
    {
        if ($val['category'] === $category) {
            $count++;
        }
    }

    return $count;
}

/* Проверка даты выполнения задачи */
function task_date_ckeck($task_date)
{
    $result = true;

    if ($task_date != null) {
        $task_date = strtotime($task_date);
        $hours_now = floor(time() / 3600);
        $hours_task = floor($task_date / 3600);
        if ($hours_now > $hours_task) {
            $hours = $hours_now - $hours_task;
            if ($hours > 24) {
                $result = false;
            }
        }
    }
    
    return $result;
}

/* Проверка даты выполнения задачи */
function add_task_date_ckeck($task_date)
{
    $result = true;

    if (!empty($task_date)) {
        $task_time = strtotime($task_date);
        $now_time = strtotime(date('Y-m-d'));
        if ($now_time > $task_time) {
            $result = false;
        }
    }
    
    return $result;
}

/* Проверка соответствия id проекта с выбранным */
function current_project_check($project_id, $current_project_id)
{
    $result = false;

    if ($current_project_id) {
        if ($project_id == $current_project_id) {
            $result = true;
        }
    }
    
    return $result;
}

/* Проверка переменной GET на соответстие типу INT */
function value_int_check($value_name)
{
    $result = true;

    if (!filter_input(INPUT_GET, $value_name, FILTER_SANITIZE_NUMBER_INT)) {
        $result = false;
    }

    return $result;
}

/* Формирование списка проектов из БД */
function get_project_rows($user_id, $con)
{
    $sql = "SELECT `p`.`id`, `p`.`title`, COUNT(`t`.`id`) AS `task_count` FROM `projects` AS `p` LEFT JOIN `tasks` AS `t` ON `t`.`id_project` = `p`.`id` WHERE `p`.`id_user` = {$user_id} GROUP BY `p`.`id`";
    $sql_result = mysqli_query($con, $sql);
    $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);

    return $rows;
}

/* Формирование списка задач из БД */
function get_task_rows($user_id, $project_id, $con)
{
    if ($project_id) {
        $sql = "SELECT `t`.*, `p`.`title` AS `category` FROM `tasks` AS `t` JOIN `projects` AS `p` ON `p`.`id` = `t`.`id_project` WHERE `t`.`id_user` = {$user_id} AND `t`.`id_project` = ". $project_id;
    } else {
        $sql = "SELECT `t`.*, `p`.`title` AS `category` FROM `tasks` AS `t` JOIN `projects` AS `p` ON `p`.`id` = `t`.`id_project` WHERE `t`.`id_user` = {$user_id}";
    }

    $sql_result = mysqli_query($con, $sql);
    $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);

    return $rows;
}

/* Проверка на сущестование проекта в БД по id */
function project_existence_check($project_id, $con)
{
    $result = false;

    if ($project_id) {
        $sql = "SELECT * FROM `projects` WHERE `id` = ". $project_id;
        $sql_result = mysqli_query($con, $sql);
        $rows = mysqli_fetch_all($sql_result);

        if (count($rows) > 0) {
            $result = true;
        }
    }

    return $result;
}

/* Проверка id проекта на корректность */
function validate_project_id($project_id, $con)
{
    $result = false;

    if (value_int_check('prj_id')) {
        if (project_existence_check($project_id, $con)) {
            $result = true;
        }
    }

    return $result;
}

/* Извлечение POST переменной */
function getPostVal($name)
{
    return $_POST[$name] ?? "";
}

/* Загрузка файла задачи */
function upload_task_file($task_file)
{
    $file_name = $task_file['name'];
    $file_path = __DIR__ . '/uploads/';

    move_uploaded_file($task_file['tmp_name'], $file_path . $file_name);
}

/* Проверка переданных данных в форме создания задачи */
function validate_task_form($task_title, $task_project_id, $task_date, $con)
{
    $errors = [];
    if (empty($task_title)) {
        $errors['task_title'] = 'Поле не заполнено';
    }
    if (!project_existence_check($task_project_id, $con)) {
        $errors['task_project_id'] = 'Указан не существующий проект';
    }
    if (!is_date_valid($task_date)) {
        $errors['task_date'] = 'Неверный формат даты';
    } else if (!add_task_date_ckeck($task_date)) {
        $errors['task_date'] = 'Дата должна быть больше или равна текущей '.date('Y-m-d');
    }

    return $errors;
}

/* Добавление новой задачи */
function add_task($user_id, $task_title, $task_project_id, $task_date, $task_file, $con)
{
    if (!empty($task_file)) {
        // Загрузка фала
        upload_task_file($task_file);
        $file_url = '/uploads/' . $task_file['name'];

        $sql = "INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `date_execute`, `status`, `file`) "
          ."VALUES ('{$task_title}', {$user_id}, {$task_project_id}, NOW(), '{$task_date}', 0, '{$file_url}')";
    } else {
        $sql = "INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `date_execute`, `status`,) "
          ."VALUES ('{$task_title}', {$user_id}, {$task_project_id}, NOW(), '{$task_date}', 0)";
    }
    
    // Добавляем задачу в базу
    $sql_result = mysqli_query($con, $sql);

    return $sql_result;
}