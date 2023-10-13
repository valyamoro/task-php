<?php

$userLogin = 'r1321e211';
$userLoginDate = date('Y-m-d');

$dataUsers = file('check_user.txt');

$currentDate = date('Y-m-d');

$userLoginDate = strtotime($userLoginDate);
$currentDate = strtotime($currentDate);

$isUserExists = false;

foreach ($dataUsers as $q) {
    $userData = explode('|', $q);
    if ($userData[0] === $userLogin) {
        $isUserExists = true;
        break;
    }
}

$checkDate = false;

foreach ($dataUsers as $q) {
    $user = explode('|', $q);

    $diff = $currentDate - $user[1];

    $diff = abs($diff);

    $diff_day = intval($diff / (3600 * 24));

    if ($diff_day != 1) {
        $checkDate = true;
    }
}

if ($isUserExists && $checkDate) {
    echo 'Пользователь уже заходил сегодня';
    die;
}

$handlerDataUser = fopen('check_user.txt', 'a+b');

$userData = "{$userLogin}|{$userLoginDate}";

fwrite($handlerDataUser, $userData . PHP_EOL);
fclose($handlerDataUser);

