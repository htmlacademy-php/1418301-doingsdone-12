<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// определяю массив проектов
$project_array = ["Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];

// определяю массив задач
$task_array = [
    [
        "name" => "Собеседование в IT компании",
        "date" => strtotime("01.11.2020"),
        "category" => "Работа",
        "completed" => false
    ],
    [
        "name" => "Выполнить тестовое задание",
        "date" => strtotime("25.12.2019"),
        "category" => "Работа",
        "completed" => false
    ],
    [
        "name" => "Сделать задание первого раздела",
        "date" => strtotime("21.12.2019"),
        "category" => "Учеба",
        "completed" => true
    ],
    [
        "name" => "Встреча с другом",
        "date" => strtotime("05.04.2020 20:00"),
        "category" => "Входящие",
        "completed" => false
    ],
    [
        "name" => "Купить корм для кота",
        "date" => null,
        "category" => "Домашние дела",
        "completed" => false
    ],
    [
        "name" => "Заказать пиццу",
        "date" => null,
        "category" => "Домашние дела",
        "completed" => false
    ]
];

function task_count($arr, $category)
{
    $count = 0;
    foreach ($arr as $val)
    {
        if ($val['category'] === $category)
        {
            $count++;
        }
    }

    return $count;
}

function task_date_ckeck($task_date)
{
    if ($task_date != null)
    {
        $hours_now = floor(time() / 86400);
        $hours_task = floor($task_date / 86400);
        if ($hours_now > $hours_task)
        {
            $hours = $hours_now - $hours_task;
            if ($hours > 24)
            {
                $result = false;
            }
            else
            {
                $result = true;
            }
        }
        else
        {
            $result = true;
        }
    }
    else
    {
        $result = true;
    }
    
    return $result;
}

require_once('helpers.php');

// HTML код главной страницы
$page_content = include_template('main.php', ['show_complete_tasks' => $show_complete_tasks, 'project_array' => $project_array, 'task_array' => $task_array]);

// окончательный HTML код
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);

print($layout_content);
