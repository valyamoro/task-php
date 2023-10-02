<?php

function connectionDB(): ?\PDO
{
    $dbh = new PDO(
        'mysql:host=localhost;dbname=db-tt;charset=utf8mb4',
        'root',
        '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"
        ]
    );

    return $dbh;
}
