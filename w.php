<?php

$calories = 150;
$colour = 'red';

$sth = $dbh->prepare('SELECT name, colour, calories FROM fruit
WHERE calories < :calories AND colour = :colour');

$sth->bindParam(':calories', $calories, PDO::PARAM_INT);
$sth->bindValue(':colour', $colour, PDO::PARAM_STR, 12);
$sth->execute();

$sth->debugDumpParams();

