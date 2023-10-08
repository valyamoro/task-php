<?php

declare(strict_types=1);
error_reporting(-1);

/**
 * Выводим удобочитаемую информацию о переменной.
 * @param mixed $data
 * @return void
 */
function dump(mixed $data): void
{
    echo '<pre>'; \print_r($data); echo '</pre>';
}

// Указываем параметры для подключения к БД.
const DB_HOST = 'localhost';
const DB_NAME = 'mvc-int-shop';
const DB_CHARSET = 'utf8mb4';
const DB_USER = 'root';
const DB_PASSWORD = '';

/**
 * Подключемся к БД через глобальный класс PDO
 * @return PDO|null
 */
function connectionDB(): ?\PDO
{
    // При последующих вызовах этой функции переменная не будет пересоздаваться.
    static $dbh = null;

    // Предотвращаем пересоздание объекта PDO.
    if (!\is_null($dbh)) {
        return $dbh;
    }

    // Конструкция для обработки исключений.
    try {
        // Задаем настройки для подключения к БД.
        $options = [
            // Режим сообщения об ошибках. Выбрасывает PDOException.
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,

            // Режим выборки. Возвращает массив индексированный именами столбцов результирующего набора.
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,

            // При подключении автоматически выполняем команду установки кодировки.
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DB_CHARSET,
        ];

        // Определяем параметры для строки источника данных.
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        // Создаем объект для подключения к БД с настройками.
        $dbh = new PDO($dsn, DB_USER, DB_PASSWORD, $options);

    // Ловим исключения.
    } catch (\PDOException $e) {
        // Прерываем выполнение скрипта и выводим ошибку на экран.
        die ('Connection error: ' . $e->getMessage());
    }

    // Возвращаем объект PDO с настройками.
    return $dbh;
}

/**
 * Функция записи ошибок в файл
 * @param string $message
 * @return void
 */
function writeError(string $message): void
{
    // Записываем в переменную путь до файла с ошибками.
    $errorLog = 'errors.log';
    // Записываем ошибку в файл.
    \file_put_contents($errorLog, $message . PHP_EOL, FILE_APPEND);
}

/**
 * Функция обработки ошибок.
 * @param array $result
 * @param string $message
 * @return void
 */
function throwError(mixed $result, string $message): void
{
    // Если результат функции возвращает неожиданный результат.
    if (empty($result)) {
        // Функция записи ошибки в файл.
        writeError($message);
        // Завершаем скрипт и выводим ошибку на экран.
        die("Error: {$message}");
    }
}

/**
 * Функция для валидации данных.
 * @param mixed $data
 * @return mixed
 */
function validateData(mixed &$data): mixed
{
    // Если приходит массив.
    if (\is_array($data)) {
        // Перебираем пришедший массив.
        foreach ($data as $key => $value) {
            // Экранируем каждый элемент массива.
            $data[$key] = connectionDB()->quote(\htmlspecialchars(\strip_tags(\trim($value))));
        }
    // Если приходит не массив.
    } else {
        // Экранируем пришедшую строку.
        $data = connectionDB()->quote(\htmlspecialchars(\strip_tags(\trim($data))));
    }

    // Возвращаем экранированные данные.
    return $data;
}

/**
 * Получаем массив с данными пользователей.
 * @param PDO $connection
 * @param string $order
 * @return array
 */
function getUsers(\PDO $connection, string $order): array
{
    // Запрос на получение данных всех пользователей.
    $query = "SELECT * FROM users ORDER BY ? DESC";

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Запускаем подготовленный запрос на выполнение.
    $sth->execute([$order]);

    // Получаем оставшиеся строки из набора результатов.
    $result = $sth->fetchAll();

    // Если в базе данных нет пользователей, то выбрасываем ошибку.
    throwError($result, 'Users not found');

    // Возвращаем ассоциативный массив данных всех пользователей.
    // Ключи берем из имени столбцов.
    return (array) $result;
}

/**
 * Получаем массив с данными пользователя.
 * @param PDO $connection
 * @param int $id
 * @return array
 */
function getUser(\PDO $connection, int $id): array
{
    // Запрос на получение данных одного пользователя по айди.
    $query = 'SELECT * FROM users WHERE id=? LIMIT 1';

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Передаем айди для позиционного параметра на вход и запускаем подготовленный запрос на выполнение.
    $sth->execute([$id]);

    // Извлекаем следующую строку с данными пользователя из результирующего набора.
    $result = $sth->fetch();

    // Если пользователь не найден, то выбрасываем ошибку.
    throwError($result, 'User not found');

    // Возвращаем ассоциативный массив данных пользователя.
    // Ключи берутся из название столбцов.
    return (array) $result;
}

/**
 * Добавляем нового пользователя.
 * @param PDO $connection
 * @param array $data
 * @return int
 * Для этой функции стоит применить транзакции, например если она не возвращает айди последней созданной записи
 * То откатываем запрос и смотрим что пошло не так * *
 */
function saveUser(\PDO $connection, array $data): int
{
    // Запрос добавляющий пользователя с данными.
    $query = 'INSERT INTO users (name, email, phone_number, password, is_active)
    VALUES(:name, :email, :phone_number, :password, :is_active)';

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Передаем данные пользователя для именованных параметров.
    // И запускаем подготовленный запрос на выполнение.
    $sth->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':phone_number' => $data['phone_number'],
        ':password' => $data['password'],
        ':is_active' => $data['is_active'],
    ]);

    // Получаем ID последней вставленной строки.
    $result = $connection->lastInsertId();

    // Возвращаем айди последней созданной записи.
    return (int) $result;
}

/**
 * Проверка на наличие пользователя в БД по почте.
 * @param PDO $connection
 * @param string $email
 * @return bool
 */
function checkUserEmail(\PDO $connection, string $email): bool
{
    // Запрос на получение почты пользователя.
    $query = 'SELECT * FROM users WHERE email=? LIMIT 1';

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Передаем почту для позиционного параметра.
    // И запускаем подготовленный запрос на выполнение.
    $sth->execute([$email]);

    // Получаем информацию о пользователе, если почта совпала.
    $result = $sth->fetch();

    // Возвращаем true, если пользователь с такой почтой существует, иначе false.
    return (bool) $result;
}

/**
 * Проверка на наличие пользователя в БД по номеру телефона.
 * @param PDO $connection
 * @param string $phoneNumber
 * @return bool
 */
function checkUserPhoneNumber(\PDO $connection, string $phoneNumber): bool
{
    // Запрос на получение номера телефона пользователя.
    $query = 'SELECT * FROM users where phone_number=? LIMIT 1';

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Передаем номер телефона для позиционного параметра.
    // И запускаем подготовленный запрос на выполнение.
    $sth->execute([$phoneNumber]);

    // Получаем информацию о пользователе, если номер телефона совпал.
    $result = $sth->fetch();

    // Возвращаем true, если пользователь с таким номером телефона существует, иначе false.
    return (bool) $result;
}

/**
 * Удаляем пользователя с его данными.
 * @param PDO $connection
 * @param int $userId
 * @return bool
 */
function deleteUser(\PDO $connection, int $userId): bool
{
    // Запрос для удаления пользователя по айди.
    $query = 'DELETE FROM users WHERE id=? LIMIT 1';

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Передаем значение айди для позиционного параметра.
    // И запускаем подготовленный запрос на выполнение.
    $result = $sth->execute([$userId]);

    // Возвращаем true, если пользователя удалили.
    return (bool) $result;
}

/**
 * Обновляем данные пользователя
 * @param PDO $connection
 * @param array $data
 * @param int $userId
 * @return array
 */
function updateUser(\PDO $connection, array $data, int $userId): array
{
    // Запрос для обновления данных пользователя.
    $query = 'UPDATE users SET name = :name, email = :email, phone_number = :phone_number, password = :password 
    WHERE id = :id LIMIT 1';

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Передаем измененные данные пользователя для именованных параметров.
    // И запускаем подготовленный запрос на выполнение.
    $sth->execute([
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':phone_number' => $data['phone_number'],
        ':password' => $data['password'],
        // Айди не проходит экранирование, т.к он будет получен с сессии, либо введен админом *
        ':id' => $userId,
    ]);

    // Возвращаем массив измененных данных пользователя.
    return (array) $data;
}


$connectionDB = connectionDB();

// Модель исключений
try {
    // Определяем метод взаимодействия с данными пользователя.
    $action = 'getUsers';

    // Определяем айди пользователя.
    $id = 49;
    if ($action === 'delete') {
        // Удаляем пользователя.
        $deleteUser = deleteUser($connectionDB, $id);
    } elseif ($action === 'update') {
        // Обновляем пользователя.
        $data = [
            'name' => 'aszxcd',
            'email' => 'tasdads3@gmail.com',
            'phone_number' => '7591541231',
            'password' => \password_hash('fada', PASSWORD_DEFAULT),
        ];
        validateData($data);
        $updateUser = updateUser($connectionDB, $data, $id);
    } elseif ($action === 'save') {
        // Добавляем пользователя.
        $data = [
            'name' => 'zxcclmnk',
            'email' => 'lmnk@gmail.com',
            'phone_number' => '1321asd32312',
            'password' => \password_hash('1234asd124', PASSWORD_DEFAULT),
            'is_active' => '1'
        ];
        validateData($data);
        $saveUser = saveUser($connectionDB, $data);
    } elseif ($action === 'check') {
        // Проверяем наличие вводимых данных в БД.
        $email = 'tezst@gmail.com';
        $phone = '7051241w2432';
        validateData($email);
        validateData($phone);
        $checkUserEmail = checkUserEmail($connectionDB, $email);
        $checkUserPhoneNumber = checkUserPhoneNumber($connectionDB, $phone);
        if (!($checkUserPhoneNumber || $checkUserEmail)) {
            echo 'Пользователя с такими данными нет!';
        } else {
            echo 'Пользователь с этими данными существует';
        }
    } elseif ($action === 'getUsers') {
        // Получаем всех пользователей в виде ассоциативного массива.

        // Название таблицы
        $table = 'name';
        validateData($table);
        $getUsers = getUsers($connectionDB, $table);
        dump($getUsers);
    } elseif ($action === 'getUser') {
        // Получаем данные пользователя через его айди.
        $getUser = getUser($connectionDB, $id);
        dump($getUser);
    }
// Блок определяющий как реагировать на выброшенное исключение.
}  catch (\PDOException $e) {
    // Записываем в файл информацию об ошибке определенной в классе Error в функциях.
    \file_put_contents('errors.log', $e->getMessage() . PHP_EOL, FILE_APPEND);
    // Заваршаем выполнения скрипта и отправляем ошибку
    die ($e->getMessage());
} finally  {
    // В любом другом случаи записываем в файл определенную информацию.
    \file_put_contents('user22.txt', 'get user' . PHP_EOL, FILE_APPEND);
}

// ----------------------------------

//class MyPDO extends PDO
//{
//    public function __construct($file = 'my_setting.ini')
//    {
//        if (!$settings = parse_ini_file($file, true)) {
//            throw new exception('Unable to open ' . $file . '.');
//        }
//
//        $dns = $settings['database']['driver'] .
//            ':host=' . $settings['database']['host'] .
//            ((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .
//            ';dbname=' . $settings['database']['schema'];
//
//        parent::__construct($dns, $settings['database']['username'], $settings['database']['password']);
//    }
//}
