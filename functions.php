<?php

/* Подключение к БД */
function connect_db($force = false)
{
    static $con = null;

    if ($force === true || $con === null) {
        $con = mysqli_connect("localhost", "root", "", "doingsdone");
    }
    
    return $con;
}

/* Подсчет кол-ва задач в проекте */
function task_count($arr, $category)
{
    $count = 0;
    foreach ($arr as $val)
    {
        if ((string)$val['category'] === (string)$category) {
            $count++;
        }
    }

    return $count;
}

/* Проверка даты выполнения задачи */
function task_date_ckeck($task_date)
{
    $result = true;

    if ($task_date != null && (time() > strtotime($task_date))) {
        $hours = floor(time() / 3600) - floor(strtotime($task_date) / 3600);
        if ($hours > 24) {
            $result = false;
        }
    }
    
    return $result;
}

/* Проверка даты выполнения задачи */
function add_task_date_ckeck($task_date)
{
    $result = true;

    if (!empty($task_date) && strtotime(date('Y-m-d')) > strtotime($task_date)) {
        $result = false;
    }
    
    return $result;
}

/* Проверка соответствия id проекта с выбранным */
function current_project_check($project_id, $current_project_id)
{
    $result = false;

    if ($current_project_id && ((int)$project_id === (int)$current_project_id)) {
        $result = true;
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

/* Рассылка сообщений */
function send_mail($rows)
{
    // Конфигурация траспорта
    $transport = (new Swift_SmtpTransport('phpdemo.ru', 25))
     ->setUsername('keks@phpdemo.ru')
     ->setPassword('htmlacademy');

    // Формирование сообщения
    $message = new Swift_Message("Уведомления о предстоящих задачах");

    $message->setFrom("keks@phpdemo.ru", "Дела в порядке");

    
    $cnt = 0;
    $emails = array_column($rows, 'email');
    $emails = array_unique($emails);
    foreach ($emails as $email) {
        $text = "";
        foreach ($rows as $task) {
            if ((string)$task['email'] === (string)$email) {
                $text .= "'{$task['title']}' на ". date("d.m.Y",strtotime($task['date_execute'])) ."\r\n";
                $user_name = $task['name'];
            }
        }
        $text = "Уважаемый, {$user_name}. У вас запланирована задача:\r\n". $text;

        $message->setTo([$email => $user_name]);
        $message->setBody($text);

        // Отправка сообщения
        $mailer = new Swift_Mailer($transport);
        $mailer->send($message);

        $cnt++;
    }
    
    return $cnt;
}

/* Формирование списка проектов из БД */
function get_project_rows($user)
{
    $rows = [];
    if (isset($user['id'])) {
        $con = connect_db();

        $sql = "SELECT `p`.`id`, `p`.`title`, COUNT(`t`.`id`) AS `task_count` FROM `projects` AS `p` LEFT JOIN `tasks` AS `t` ON `t`.`id_project` = `p`.`id` WHERE `p`.`id_user` = {$user['id']} GROUP BY `p`.`id`";
        $sql_result = mysqli_query($con, $sql);
        $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);
    }
    
    return $rows;
}

/* Формирование списка задач из БД */
function get_task_rows($user, $project_id = 0, $query = '', $filter = '')
{
    $rows = [];
    if (isset($user['id'])) {
        $con = connect_db();

        if ($query) 
            $query_text = " AND MATCH(`t`.`title`) AGAINST('{$query}')";

        if ((string)$filter === '2') {
            $filter_text = " AND `date_execute` = DATE(NOW())";
        } else if ((string)$filter === '3') {
            $filter_text = " AND `date_execute` > DATE(NOW()) AND `date_execute` < (DATE(NOW()) + INTERVAL 2 DAY)";
        } else if ((string)$filter === '4') {
            $filter_text = " AND `date_execute` < DATE(NOW())";
        }

        if ($project_id) {
            $sql = "SELECT `t`.*, `p`.`title` AS `category` FROM `tasks` AS `t` JOIN `projects` AS `p` ON `p`.`id` = `t`.`id_project` WHERE `t`.`id_user` = {$user['id']} AND `t`.`id_project` = ". $project_id .$query_text .$filter_text;
        } else {
            $sql = "SELECT `t`.*, `p`.`title` AS `category` FROM `tasks` AS `t` JOIN `projects` AS `p` ON `p`.`id` = `t`.`id_project` WHERE `t`.`id_user` = {$user['id']}" .$query_text .$filter_text;
        }

        $sql_result = mysqli_query($con, $sql);
        $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);
    }

    return $rows;
}

/* Формирование списка задач, у которых приближается время выполнения, из БД для рассылки */
function get_mailing_task_rows()
{
    $con = connect_db();

    $sql = "SELECT `t`.*, `u`.`name`, `u`.`email` FROM `tasks` AS `t` JOIN `users` as `u` ON `u`.`id` = `t`.`id_user` WHERE `t`.`status` = 0 AND `t`.`date_execute` = DATE(NOW()) ORDER BY `u`.`email` ";

    $sql_result = mysqli_query($con, $sql);
    $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);

    return $rows;
}

/* Проверка на сущестование проекта в БД по id */
function project_existence_check($project_id)
{
    $result = false;

    $con = connect_db();

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

/* Проверка на сущестование проекта в БД по наименованию */
function project_title_existence_check($project_title)
{
    $result = true;

    $con = connect_db();

    $user = get_user();

    if ($project_title && $user) {
        $sql = "SELECT * FROM `projects` WHERE `title` = '". $project_title ."' AND `id_user` = ". $user['id'];
        $sql_result = mysqli_query($con, $sql);
        $rows = mysqli_fetch_all($sql_result);

        if (count($rows) > 0) {
            $result = false;
        }
    }

    return $result;
}

/* Проверка на сущестование E-mail */
function email_existence_check($email)
{
    $result = true;

    $con = connect_db();

    if ($email) {
        $sql = "SELECT * FROM `users` WHERE `email` = '". $email ."'";
        $sql_result = mysqli_query($con, $sql);
        $rows = mysqli_fetch_all($sql_result);

        if (count($rows) > 0) {
            $result = false;
        }
    }

    return $result;
}

/* Проверка id проекта на корректность */
function validate_project_id($project_id)
{
    $result = false;

    if (value_int_check('project_id') && project_existence_check($project_id)) {
        $result = true;
    }

    return $result;
}

/* Извлечение POST переменной */
function getPostVal($name)
{
    return $_POST[$name] ?? "";
}

/* Извлечение GET переменной */
function getGetVal($name)
{
    return $_GET[$name] ?? "";
}


/* Проверка переданных данных в форме создания задачи */
function validate_project_form($project_title)
{
    $errors = [];
    if (empty($project_title)) {
        $errors['project_title'] = 'Поле не заполнено';
    } else if (!project_title_existence_check($project_title)) {
        $errors['project_title'] = 'Проект с таким именем уже существует';
    }

    return $errors;
}

/* Проверка переданных данных в форме создания задачи */
function validate_task_form($task_title, $task_project_id, $task_date)
{
    $errors = [];
    if (empty($task_title)) {
        $errors['task_title'] = 'Поле не заполнено';
    }
    if (!project_existence_check($task_project_id)) {
        $errors['task_project_id'] = 'Указан не существующий проект';
    }
    if (!is_date_valid($task_date)) {
        $errors['task_date'] = 'Неверный формат даты';
    } else if (!add_task_date_ckeck($task_date)) {
        $errors['task_date'] = 'Дата должна быть больше или равна текущей';
    }

    return $errors;
}

/* Проверка переданных данных в форме регистрации */
function validate_registration_form($reg_email, $reg_password, $reg_name)
{
    $errors = [];
    if (empty($reg_email)) {
        $errors['reg_email'] = 'Поле не заполнено';
    } else if (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
        $errors['reg_email'] = 'E-mail должен быть корректным';
    } else if (!email_existence_check($reg_email)) {
        $errors['reg_email'] = 'Пользователь с таким E-mail уже существует';
    }
    if (empty($reg_password)) {
        $errors['reg_password'] = 'Поле не заполнено';
    }
    if (empty($reg_name)) {
        $errors['reg_name'] = 'Поле не заполнено';
    }

    return $errors;
}

/* Проверка переданных данных в форме входа */
function validate_auth_form($auth_email, $auth_password)
{
    $errors = [];
    if (empty($auth_email)) {
        $errors['auth_email'] = 'Поле не заполнено';
    } else if (!filter_var($auth_email, FILTER_VALIDATE_EMAIL)) {
        $errors['auth_email'] = 'E-mail должен быть корректным';
    }
    if (empty($auth_password)) {
        $errors['auth_password'] = 'Поле не заполнено';
    }

    return $errors;
}

/* Добавление нового проекта */
function add_project($user, $project_title)
{
    if (isset($user['id'])) {

        $con = connect_db();

        $sql = "INSERT INTO `projects` (`title`, `id_user`) "
          ."VALUES ('{$project_title}', {$user['id']})";
    
        // Добавляем провект в базу
        $sql_result = mysqli_query($con, $sql);

        return $sql_result;
    }

    return false;
}

function set_task_execute($task_id, $status, $user)
{
    if (isset($user['id'])) {
        $con = connect_db();

        if ((string)$status === '0') {
            $status_txt = '1';
        } else {
            $status_txt = '0';
        }

        $sql = "UPDATE `tasks` SET `status` = {$status_txt} WHERE `id` = {$task_id} AND `id_user` = {$user['id']} ";
        $sql_result = mysqli_query($con, $sql);

        return $sql_result;
    }

    return false;
}

/* Загрузка файла задачи */
function upload_task_file($task_file)
{
    $file_name = $task_file['name'];
    $file_path = __DIR__ . '/uploads/';

    move_uploaded_file($task_file['tmp_name'], $file_path . $file_name);
}

/* Добавление новой задачи */
function add_task($user, $task_title, $task_project_id, $task_date, $task_file)
{
    if (isset($user['id'])) {
        $con = connect_db();

        if (!empty($task_file['name'])) {
            // Загрузка фала
            upload_task_file($task_file);
            $file_url = '/uploads/' . $task_file['name'];

            $sql = "INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `date_execute`, `status`, `file`) "
              ."VALUES ('{$task_title}', {$user['id']}, {$task_project_id}, NOW(), '{$task_date}', 0, '{$file_url}')";
        } else {
            $sql = "INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `date_execute`, `status`) "
              ."VALUES ('{$task_title}', {$user['id']}, {$task_project_id}, NOW(), '{$task_date}', 0)";
        }
        
        // Добавляем задачу в базу
        $sql_result = mysqli_query($con, $sql);

        return $sql_result;
    } else {
        return [];
    }
}

/* Добавление нового пользователя */
function add_user($reg_email, $reg_password, $reg_name)
{
    $con = connect_db();

    $hash = password_hash($reg_password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO `users` (`name`, `password`, `email`) "
          ."VALUES ('{$reg_name}', '{$hash}', '{$reg_email}')";
    
    // Добавляем пользователя в базу
    $sql_result = mysqli_query($con, $sql);

    return $sql_result;
}

/* Аутентификация пользователя */
function auth($auth_email, $auth_password)
{
    $result = false;

    $con = connect_db();

    $sql = "SELECT * FROM `users` WHERE `email` = '{$auth_email}'";
    
    // Делаем запрос
    $sql_result = mysqli_query($con, $sql);

    if ($sql_result) {
        $row = mysqli_fetch_assoc($sql_result);
        if (password_verify($auth_password, $row['password'])) {
            $_SESSION['user'] = $row;
            $result = true;
        }
    }

    return $result;
}

function get_user()
{
    $user = [];

    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }

    return $user;
}

function get_filter_request($project_id, $filter = 1)
{
    $url = '/';
    $query = compact('project_id', 'filter');
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }
    return $url;
}