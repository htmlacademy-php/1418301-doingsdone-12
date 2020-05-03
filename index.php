<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

// определяю массив проектов
$project_array = ["Входящие", "Учеба", "Работа", "Домашние дела", "Авто"];

// определяю массив задач
$task_array = [
    [
        "name" => "Собеседование в IT компании",
        "date" => "01.12.2019",
        "category" => "Работа",
        "completed" => false
    ],
    [
        "name" => "Выполнить тестовое задание",
        "date" => "25.12.2019",
        "category" => "Работа",
        "completed" => false
    ],
    [
        "name" => "Сделать задание первого раздела",
        "date" => "21.12.2019",
        "category" => "Учеба",
        "completed" => true
    ],
    [
        "name" => "Встреча с другом",
        "date" => "22.12.2019",
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

require_once('helpers.php');

// HTML код главной страницы
$page_content = include_template('main.php', ['show_complete_tasks' => $show_complete_tasks, 'project_array' => $project_array, 'task_array' => $task_array]);

// окончательный HTML код
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);

print($layout_content);
