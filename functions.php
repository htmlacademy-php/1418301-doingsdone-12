<?php
/**
 * Создает подключение к БД
 *
 * @param bool $force условие переподключения к БД (если true, создает новое подключение)
 *
 * @return link mysqli Ресурс соединения
 */
function connect_db($force = false)
{
    static $link = null;

    if ($force === true || $link === null) {
        $link = mysqli_connect("localhost", "root", "", "doingsdone");
    }

    return $link;
}

/**
 * Проверяет дату выполнения задачи
 * для отметки задач, до даты выполнения которых осталось менее 24 часов
 *
 * @param $task_date дата выполнения задачи
 *
 * @return bool result результат проверки
 */
function task_date_ckeck($task_date)
{
    $result = true;

    if ($task_date !== NULL && (time() > strtotime($task_date))) {
        $hours = floor(time() / 3600) - floor(strtotime($task_date) / 3600);
        if ($hours > 24) {
            $result = false;
        }
    }

    return $result;
}

/**
 * Проверяет дату выполнения задачи
 * при создании новой задачи, чтобы указанная дата выполнения не была менее текущей
 *
 * @param $task_date дата выполнения задачи
 *
 * @return bool result результат проверки
 */
function add_task_date_ckeck($task_date)
{
    $result = true;

    if (!empty($task_date) && strtotime(date('Y-m-d')) > strtotime($task_date)) {
        $result = false;
    }

    return $result;
}

/**
 * Проверяет является ли id проекта текущим
 * для выделения выбранного пункта в меню проектов
 *
 * @param $project_id id проекта
 * @param $current_project_id id текущего проекта
 *
 * @return bool result результат проверки
 */
function current_project_check($project_id, $current_project_id)
{
    $result = false;

    if ($project_id && $current_project_id && ((int)$project_id === (int)$current_project_id)) {
        $result = true;
    }

    return $result;
}

/**
 * Проверяет переменную GET на соответстие типу INT
 *
 * @param $value_name имя переменной
 *
 * @return bool result результат проверки
 */
function value_int_check($value_name)
{
    $result = true;

    if (!filter_input(INPUT_GET, $value_name, FILTER_SANITIZE_NUMBER_INT)) {
        $result = false;
    }

    return $result;
}

/**
 * Рассылает E-mail сообщения по списку пользователей,
 * у которых есть задачи с приближающейся датой выполнения
 *
 * @param array $rows ассоциативный массив с данными пользователя и задачи
 * Массив содержит данные:
 * email - электронный адрес пользователя
 * name - имя пользователя
 * title - наименование задачи
 * date_execute - дата выполнения задачи
 *
 * @return integer cnt количество разосланых сообщений
 */
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
        $user_name = "";
        if (isset($email)) {
            foreach ($rows as $task) {
                if ((string)$task['email'] === (string)$email) {
                    if(isset($task['title']) && isset($task['date_execute']) && isset($task['name'])) {
                        $text .= "'{$task['title']}' на " . date("d.m.Y", strtotime($task['date_execute'])) . "\r\n";
                        $user_name = $task['name'];
                    }
                }
            }

            $text = "Уважаемый, {$user_name}. У вас запланирована задача:\r\n" . $text;

            $message->setTo([$email => $user_name]);
            $message->setBody($text);

            // Отправка сообщения
            $mailer = new Swift_Mailer($transport);
            $mailer->send($message);

            $cnt++;
        }
    }

    return $cnt;
}

/**
 * Формирует ассоциативный массив с данными проектов из БД
 *
 * @param array $user ассоциативный массив с данными текущего пользователя
 *
 * @return array rows ассоциативный массив с данными проектов
 */
function get_project_rows($user)
{
    $rows = [];

    if (isset($user['id'])) {
        $link = connect_db();

        $sql = "SELECT `p`.`id`, `p`.`title`, COUNT(`t`.`id`) AS `task_count` FROM `projects` AS `p` LEFT JOIN `tasks` AS `t` ON `t`.`id_project` = `p`.`id` WHERE `p`.`id_user` = {$user['id']} GROUP BY `p`.`id`";
        $sql_result = mysqli_query($link, $sql);

        if ($sql_result && isset($sql_result->num_rows) && $sql_result->num_rows > 0) {
            $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);
        }
    }

    return $rows;
}

/**
 * Формирует ассоциативный массив с данными задач из БД
 *
 * @param array $user ассоциативный массив с данными текущего пользователя
 * @param $project_id id текущего проекта
 * @param $query строка поиска в наименовании задачи
 * @param $filter параметр фильтрации
 * может принимать следующие значения:
 * 1 - все задачи, т.е. фильтр не используется
 * 2 - задачи у которых дата выполнения совпадает с текущей
 * 3 - задачи у которых дата выполнения равна следующему дню
 * 4 - задачи у которых дата выполнения меньше текущей, т.е просроченные задачи
 *
 * @return array rows ассоциативный массив с данными проектов
 */
function get_task_rows($user, $project_id = 0, $query = '', $filter = '')
{
    $rows = [];
    if (isset($user['id'])) {
        $link = connect_db();

        $query_text = "";
        $filter_text = "";

        if ($query) {
            $query_text = " AND MATCH(`t`.`title`) AGAINST('{$query}')";
        }

        if ((string)$filter === '2') {
            $filter_text = " AND `date_execute` = DATE(NOW())";
        } elseif ((string)$filter === '3') {
            $filter_text = " AND `date_execute` > DATE(NOW()) AND `date_execute` < (DATE(NOW()) + INTERVAL 2 DAY)";
        } elseif ((string)$filter === '4') {
            $filter_text = " AND `date_execute` < DATE(NOW())";
        }

        if ($project_id) {
            $sql = "SELECT `t`.*, `p`.`title` AS `category` FROM `tasks` AS `t` JOIN `projects` AS `p` ON `p`.`id` = `t`.`id_project` WHERE `t`.`id_user` = {$user['id']} AND `t`.`id_project` = " . $project_id . $query_text . $filter_text;
        } else {
            $sql = "SELECT `t`.*, `p`.`title` AS `category` FROM `tasks` AS `t` JOIN `projects` AS `p` ON `p`.`id` = `t`.`id_project` WHERE `t`.`id_user` = {$user['id']}" . $query_text . $filter_text;
        }

        $sql_result = mysqli_query($link, $sql);

        if ($sql_result && isset($sql_result->num_rows) && $sql_result->num_rows > 0) {
            $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);
        }
    }

    return $rows;
}

/**
 * Формирование ассоциативного массива пользователей и задач,
 * у которых приближается время выполнения, из БД для рассылки
 *
 * @return array rows ассоциативный массив с данными пользователей и задач
 */
function get_mailing_task_rows()
{
    $rows = [];

    $link = connect_db();

    $sql = "SELECT `t`.*, `u`.`name`, `u`.`email` FROM `tasks` AS `t` JOIN `users` as `u` ON `u`.`id` = `t`.`id_user` WHERE `t`.`status` = 0 AND `t`.`date_execute` = DATE(NOW()) ORDER BY `u`.`email` ";

    $sql_result = mysqli_query($link, $sql);

    if ($sql_result && isset($sql_result->num_rows) && $sql_result->num_rows > 0) {
        $rows = mysqli_fetch_all($sql_result, MYSQLI_ASSOC);
    }

    return $rows;
}

/**
 * Проверяет на существование проекта в БД по id
 * при создании новой задачи, чтобы задача принадлежала существующему проекту
 *
 * @param $project_id id проекта
 *
 * @return bool result результат проверки
 */
function project_existence_check($project_id)
{
    $result = false;

    $link = connect_db();

    if ($project_id) {

        $rows = [];

        $sql = "SELECT * FROM `projects` WHERE `id` = " . (string)$project_id;
        $sql_result = mysqli_query($link, $sql);

        if ($sql_result && isset($sql_result->num_rows) && $sql_result->num_rows > 0) {
            $result = true;
        }
    }

    return $result;
}

/**
 * Проверяет на существование проекта в БД по наименованию
 * при создании нового проета, чтобы избежать проекта с одинаковыми наименованиями
 *
 * @param $project_title наименование проекта, который указали при создании
 *
 * @return bool result результат проверки
 */
function project_title_existence_check($project_title)
{
    $result = true;

    $link = connect_db();

    $user = get_user();

    if ($project_title && isset($user['id'])) {

        $rows = [];

        $sql = "SELECT * FROM `projects` WHERE `title` = '" . $project_title . "' AND `id_user` = " . (string)$user['id'];
        $sql_result = mysqli_query($link, $sql);

        if ($sql_result && isset($sql_result->num_rows) && $sql_result->num_rows > 0) {
            $result = false;
        }
    }

    return $result;
}

/**
 * Проверяет на сущестование E-mail
 * при регистрации нового пользователя, чтобы избежать дублирования адресов у пользователей
 *
 * @param $email E-mail нового пользователя, который указали при регистрации
 *
 * @return bool result результат проверки
 */
function email_existence_check($email)
{
    $result = true;

    $link = connect_db();

    if ($email) {

        $rows = [];

        $sql = "SELECT * FROM `users` WHERE `email` = '" . $email . "'";
        $sql_result = mysqli_query($link, $sql);

        if ($sql_result && isset($sql_result->num_rows) && $sql_result->num_rows > 0) {
            $result = false;
        }
    }

    return $result;
}

/**
 * Проверяет id проекта на корректность
 *
 * @param $project_id id проекта
 *
 * @return bool result результат проверки
 */
function validate_project_id($project_id)
{
    $result = false;

    if (value_int_check('project_id') && project_existence_check($project_id)) {
        $result = true;
    }

    return $result;
}

/**
 * Проверка на существование переменной в массиве и ее формирование
 * по имени ключа
 *
 * @param array $arr массив, из которого извлекается переменная
 * @param string $name имя ключа в массиве
 *
 * @return value извлеченнная переменная
 */
function getVal($arr = [], $name = '')
{
    $value = '';

    if (is_array($arr) && isset($arr[$name])) {
        $value = strip_tags((string)$arr[$name]);
    }
    
    return $value;
}

/**
 * Извлекает POST переменную
 *
 * @param $name наименование переменной
 *
 * @return value извлеченнная переменная
 */
function getPostVal($name = '')
{
    $value = '';

    if (isset($_POST[$name])) {
        $value = strip_tags($_POST[$name]);
    }
    
    return $value;
}

/**
 * Извлекает GET переменную
 *
 * @param $name наименование переменной
 *
 * @return value извлеченнная переменная
 */
function getGetVal($name = '')
{
    $value = '';
    
    if (isset($_GET[$name])) {
        $value = strip_tags($_GET[$name]);
    }
    
    return $value;
}

/**
 * Проверяет переданные данные в форме создания проекта
 *
 * @param $project_title наименование проекта
 *
 * @return array errors ассоциативный массив с ошибками
 */
function validate_project_form($project_title)
{
    $errors = [];

    if (empty($project_title)) {
        $errors['project_title'] = 'Поле не заполнено';
    } elseif (strlen($project_title) > 150) {
        $errors['project_title'] = 'Длина названия не должна превышать 150 символов';
    } elseif (!project_title_existence_check($project_title)) {
        $errors['project_title'] = 'Проект с таким именем уже существует';
    }

    return $errors;
}

/**
 * Проверяет переданные данные в форме создания задачи
 *
 * @param $task_title наименование задачи
 * @param $task_project_id id проекта
 * @param $task_date дата выполнения проекта в формате 'ГГГГ-ММ-ДД'
 *
 * @return array errors ассоциативный массив с ошибками
 */
function validate_task_form($task_title, $task_project_id, $task_date)
{
    $errors = [];
    
    if (empty($task_title)) {
        $errors['task_title'] = 'Поле не заполнено';
    } elseif (strlen($task_title) > 150) {
        $errors['task_title'] = 'Длина названия не должна превышать 150 символов';
    }

    if (!project_existence_check($task_project_id)) {
        $errors['task_project_id'] = 'Указан не существующий проект';
    }

    if (!is_date_valid($task_date) && !empty($task_date)) {
        $errors['task_date'] = 'Неверный формат даты';
    } elseif (!add_task_date_ckeck($task_date)) {
        $errors['task_date'] = 'Дата должна быть больше или равна текущей';
    }

    return $errors;
}

/**
 * Проверяет переданные данные в форме регистрации
 *
 * @param $reg_email E-mail пользователя
 * @param $reg_password пароль
 * @param $reg_name имя пользователя
 *
 * @return array errors ассоциативный массив с ошибками
 */
function validate_registration_form($reg_email, $reg_password, $reg_name)
{
    $errors = [];

    if (empty($reg_email)) {
        $errors['reg_email'] = 'Поле не заполнено';
    } elseif (strlen($reg_email) > 128) {
        $errors['reg_email'] = 'Длина E-mail не должна превышать 128 символов';
    } elseif (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
        $errors['reg_email'] = 'E-mail должен быть корректным';
    } elseif (!email_existence_check($reg_email)) {
        $errors['reg_email'] = 'Пользователь с таким E-mail уже существует';
    }

    if (empty($reg_password)) {
        $errors['reg_password'] = 'Поле не заполнено';
    }

    if (empty($reg_name)) {
        $errors['reg_name'] = 'Поле не заполнено';
    } elseif (strlen($reg_name) > 50) {
        $errors['reg_name'] = 'Длина имени не должна превышать более 50 символов';
    }

    return $errors;
}

/**
 * Проверяет переданные данные в форме входа
 *
 * @param $auth_email E-mail пользователя
 * @param $auth_password пароль
 *
 * @return array errors ассоциативный массив с ошибками
 */
function validate_auth_form($auth_email, $auth_password)
{
    $errors = [];

    if (empty($auth_email)) {
        $errors['auth_email'] = 'Поле не заполнено';
    } elseif (strlen($auth_email) > 128) {
        $errors['auth_email'] = 'Длина E-mail не должна превышать 128 символов';
    } elseif (!filter_var($auth_email, FILTER_VALIDATE_EMAIL)) {
        $errors['auth_email'] = 'E-mail должен быть корректным';
    }

    if (empty($auth_password)) {
        $errors['auth_password'] = 'Поле не заполнено';
    }

    return $errors;
}

/**
 * Добавляет новый проекта в БД
 *
 * @param array $user ассоциативный массив с данными текущего пользователя
 * @param $project_title наименование проекта
 *
 * @return bool sql_result результат выполнения добавления
 */
function add_project($user, $project_title)
{
    if (isset($user['id'])) {

        $link = connect_db();

        $sql = "INSERT INTO `projects` (`title`, `id_user`) "
            . "VALUES ('{$project_title}', {$user['id']})";

        // Добавляем провект в базу
        $sql_result = mysqli_query($link, $sql);

        return $sql_result;
    }

    return false;
}

/**
 * Меняет статус задачи
 * менаят значение статуса на противоположный текущему
 *
 * @param $task_id id задачи
 * @param $status текущий статус задачи
 * @param array $user ассоциативный массив с данными текущего пользователя
 *
 * @return bool sql_result результат выполнения обновления
 */
function set_task_execute($task_id, $status, $user)
{
    if (isset($user['id'])) {
        $link = connect_db();

        if ((string)$status === '0') {
            $status_txt = '1';
        } else {
            $status_txt = '0';
        }

        $sql = "UPDATE `tasks` SET `status` = {$status_txt} WHERE `id` = {$task_id} AND `id_user` = {$user['id']} ";
        $sql_result = mysqli_query($link, $sql);

        return $sql_result;
    }

    return false;
}

/**
 * Загружает файл для задачи на сервер
 *
 * @param array $task_file ассоциативный массив с данными файла
 */
function upload_task_file($task_file)
{
    if (isset($task_file['name']) && isset($task_file['tmp_name'])) {
        $file_name = $task_file['name'];
        $file_path = __DIR__ . '/uploads/';

        move_uploaded_file($task_file['tmp_name'], $file_path . $file_name);
    }
}

/**
 * Добавляет новую задачу в БД
 *
 * @param array $user ассоциативный массив с данными текущего пользователя
 * @param $task_title наименование задачи
 * @param $task_project_id id проекта
 * @param $task_date дата выполнения проекта в формате 'ГГГГ-ММ-ДД'
 * @param $task_file ассоциативный массив с данными файла
 *
 * @return bool sql_result результат выполнения добавления
 */
function add_task($user, $task_title, $task_project_id, $task_date, $task_file)
{
    if (isset($user['id'])) {
        $link = connect_db();

        $task_date_text = "NULL";

        if (!empty($task_date)) {
            $task_date_text = "'{$task_date}'";
        }

        if (isset($task_file['name']) && !empty($task_file['name'])) {
            // Загрузка фала
            upload_task_file($task_file);
            $file_url = '/uploads/' . $task_file['name'];

            $sql = "INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `date_execute`, `status`, `file`) "
                . "VALUES ('{$task_title}', {$user['id']}, {$task_project_id}, NOW(), " . $task_date_text . ", 0, '{$file_url}')";
        } else {
            $sql = "INSERT INTO `tasks` (`title`, `id_user`, `id_project`, `date_create`, `date_execute`, `status`) "
                . "VALUES ('{$task_title}', {$user['id']}, {$task_project_id}, NOW(), " . $task_date_text . ", 0)";
        }

        // Добавляем задачу в базу
        $sql_result = mysqli_query($link, $sql);

        return $sql_result;
    }

    return false;
}

/**
 * Добавляет новую пользователя
 *
 * @param $reg_email E-mail пользователя
 * @param $reg_password пароль
 * @param $reg_name иям пользователя
 *
 * @return bool sql_result результат выполнения добавления
 */
function add_user($reg_email, $reg_password, $reg_name)
{
    $link = connect_db();

    $hash = password_hash($reg_password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO `users` (`name`, `password`, `email`) "
        . "VALUES ('{$reg_name}', '{$hash}', '{$reg_email}')";

    // Добавляем пользователя в базу
    $sql_result = mysqli_query($link, $sql);

    return $sql_result;
}

/**
 * Аутентификация пользователя
 *
 * @param $auth_email E-mail пользователя
 * @param $auth_password пароль
 *
 * @return bool result результат аутентификации
 */
function auth($auth_email = '', $auth_password = '')
{
    $result = false;

    $link = connect_db();

    $sql = "SELECT * FROM `users` WHERE `email` = '{$auth_email}'";

    // Делаем запрос
    $sql_result = mysqli_query($link, $sql);

    if ($sql_result && isset($sql_result->num_rows) && $sql_result->num_rows > 0) {
        $row = mysqli_fetch_assoc($sql_result);
        if (isset($row['password']) && password_verify($auth_password, $row['password'])) {
            $_SESSION['user'] = $row;
            $result = true;
        }
    }

    return $result;
}

/**
 * Создает ассоциативный массив с данными текущего пользователя
 *
 * @return array user ассоциативный массив с данными пользователя
 */
function get_user()
{
    $user = [];

    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    }

    return $user;
}

/**
 * Формирует строку запроса для фильта
 *
 * @param $project_id id текущего проекта
 * @param $filter значение параментра фильтрации
 *
 * @return string url сформированная строка запроса
 */
function get_filter_request($project_id, $filter = 1)
{
    $url = '/';

    if ($project_id) {
        $query = compact('project_id', 'filter');
    } else {
        $query = compact('filter');
    }
    
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }
    
    return $url;
}
