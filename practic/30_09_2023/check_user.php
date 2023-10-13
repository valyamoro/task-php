<?php

// Логин пользователя.
$userLogin = 'r1321e211';
// Дата входа пользователя на сайт.
$userLoginDate = date('Y-m-d');

// Массив с данными всех пользователей.
$dataUsers = file('check_user.txt');

// Получаем текующую дату.
$currentDate = date('Y-m-d');

// Преобразуем даты в формат timestamp.
$userLoginDate = strtotime($userLoginDate);
$currentDate = strtotime($currentDate);

// Заходил ли сегодня пользователь.
$isUserVisited = false;

// Перебираем данные пользователей.
foreach ($dataUsers as $q) {
    // Разбиваем данные пользователя на логин и дату захода на сайт.
    $user = explode('|', $q);

    // Получаем разницу в секунду между датами.
    $diff = $currentDate - $user[1];

    // Убираем знак минуса.
    $diff = abs($diff);

    // Приводим к int и получаем разницу в днях.
    $diffDay = intval($diff / (3600 * 24));

    // Если пользователь заходил сегодня.
    if ($diffDay !== 0) {
        // Пользователь сегодня заходил.
        $isUserVisited = true;
    }
}

// Проверяем заходил ли сегодня пользователь.
if ($isUserVisited) {
    // Выводим информацию.
    echo 'Пользователь уже заходил сегодня';
    // Завершаем скрипт.
    die;
}

// Открываем файл, данные будут открыты в двоичном режиме и не будут перезаписаны.
$handlerDataUser = fopen('check_user.txt', 'a+b');

// Формируем строку с данными пользователя, его логином и датой захода на сайт.
$userData = "{$userLogin}|{$userLoginDate}";

// Записываем данные пользователя в файл, переводя на другую строку.
fwrite($handlerDataUser, $userData . PHP_EOL);
// Закрываем доступ к файлу.
fclose($handlerDataUser);

