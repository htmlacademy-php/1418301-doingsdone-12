<?php
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);


// Подключение к БД
$con = mysqli_connect("localhost", "root", "", "doingsdone");

// Получаем список проектов
$sql_projects = "SELECT * FROM `projects` WHERE `id_user` = 1";
$result_projects = mysqli_query($con, $sql_projects);
$project_rows = mysqli_fetch_all($result_projects, MYSQLI_ASSOC);

// Получаем список задач
$sql_tasks = "SELECT `t`.`title`, `t`.`status`, `t`.`date_execute`, `p`.`title` AS `category` FROM `tasks` AS `t` JOIN `projects` AS `p` ON `p`.`id` = `t`.`id_project` WHERE `t`.`id_user` = 1";
$result_tasks = mysqli_query($con, $sql_tasks);
$task_rows = mysqli_fetch_all($result_tasks, MYSQLI_ASSOC);



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

function task_date_ckeck($task_date)
{
    if ($task_date != null) {
        $task_date = strtotime($task_date);
        $hours_now = floor(time() / 3600);
        $hours_task = floor($task_date / 3600);
        if ($hours_now > $hours_task) {
            $hours = $hours_now - $hours_task;
            if ($hours > 24) {
                $result = false;
            } else {
                $result = true;
            }
        } else {
            $result = true;
        }
    } else {
        $result = true;
    }
    
    return $result;
}

require_once('helpers.php');

// HTML код главной страницы
$page_content = include_template('main.php', ['show_complete_tasks' => $show_complete_tasks, 'project_array' => $project_rows, 'task_array' => $task_rows]);

// окончательный HTML код
$layout_content = include_template('layout.php', ['content' => $page_content, 'title' => 'Дела в порядке']);

print($layout_content);
