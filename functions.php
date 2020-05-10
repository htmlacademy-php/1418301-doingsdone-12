<?php

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

/* Подключение к БД */
function connect_db ()
{
    static $host = "localhost";
    static $user = "root";
    static $password = "";
    static $db = "doingsdone";

    $con = mysqli_connect($host, $user, $password, $db);

    return $con;
}