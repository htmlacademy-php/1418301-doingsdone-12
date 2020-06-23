<?php
// Запуск сессии
session_start();

if (isset($_SESSION['user'])) {
	unset($_SESSION['user']);
}
header("Location: /guest.php");
