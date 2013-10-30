ispmanager-php
==============

PHP класс для работы с ISPManager

Использование:

$user = 'root'; // Логин администратора ISP, имеющего права работы с пользователями
$password = 'password'; // Пароль этого администратора
$domain = 'example.com'; // Домен, на котором расположен ISPManager
$ip = '10.0.0.25'; // IP адрес, который будет назначен пользователю по-умолчанию
$preset = 'free'; // Название шаблона прав пользователей, который будет назначен пользователям

include __DIR__ . '/libs/ispManager.php';

$isp = new ispmanager($admin, $password, $preset, $domain, $ip);

// Добавление пользователя
$isp->ispUserAdd('username', 'password', 'email@example.com');

// Удаление пользователя
$isp->ispUserDel('username');

// Смена пароля пользователя
$isp->ispPasswd('username', 'newpassword');

// Выключение аккаунта
$isp->ispUserDisable('username');

// Включение аккаунта
$isp->ispUserEnable('username');
