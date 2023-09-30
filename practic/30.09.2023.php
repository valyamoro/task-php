<?php
//
//function getUsers($connection, string $order, ?int $id = null): array
//{
//	if ($id) {
//		$query = 'select * from users where id=? limit 1';
//	} else {
//		$query = "select * from users order by {$order} desc";
//	}
//	$sth = $connection->prepare($query);
//	$sth->execute(\is_null($id) ? [] : [$id]);
//	$result = $sth->fetchAll();
//	return $result;
//}
function connectionDB(): ?\PDO
{
    $dbh = new \PDO(
        'mysql:host=localhost;dbname=db-tt;charset=utf8mb4',
        'root',
        '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
        ]
    );

    return $dbh;
}
//добавить комменты ко всем строчкам не бездумные
//сделать обработку ошибок
function getUsers(\PDO $connection, string $order): array
{
    $query = "select * from users order by {$order} desc";
    $sth = $connection->prepare($query);
    $sth->execute();
    $result = $sth->fetchAll();
    return $result;
}

function getUser(\PDO $connection, int $id): array
{
    $query = 'select * from users where id=? limit 1';
    $sth = $connection->prepare($query);
    $sth->execute([$id]);
    $result = $sth->fetch();
    if (!$result) {
        throw new \Error('User not found.');
    }
    //var_dump($result);
    //return $result;
    //return $result !== false ? $result : [];
    return $result;
}
//
//name
//email
//phone
//password
//is_active
function saveUser(\PDO $connection, array $data): int
{
    $query = 'insert into users (name, email, phone, password, is_active) VALUES (?,?,?,?,?)';
    $sth = $connection->prepare($query);
    $sth->execute(\array_values($data));
    $result = $connection->lastInsertId();

    return (int)$result;
}
//функция проверки пользователя по email и телефону
//если есть сказать об этом
//обновления данных пользователя
//удаление пользователя

$connectionDB = connectionDB();
$users = getUsers($connectionDB, 'id');
print_r($users);
try {
    $id = 1;
    $user = getUser($connectionDB, $id);
} catch (\Error $e) {
    file_put_contents('errors.log', $e->getMessage() . PHP_EOL, FILE_APPEND);
    die ($e->getMessage());
} finally  {
    file_put_contents('user22.txt', 'get user' . PHP_EOL, FILE_APPEND);
}
print_r($user);
$data = [
    'name' => 'Ivangus',
    'email' => 'ivn@mail.ru',
    'phone' => '79404443301',
    'password' => password_hash('12345j', PASSWORD_DEFAULT),
    'is_active' => 1,
];
$lastId = saveUser($connectionDB, $data);
print_r($lastId);