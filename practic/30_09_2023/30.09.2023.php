<?php

declare(strict_types=1);
error_reporting(-1);

use JetBrains\PhpStorm\NoReturn;

/**
 * Выводим удобочитаемую информацию о переменной.
 * @param mixed $data
 * @return void
 */
function dump(mixed $data): void
{
    echo '<pre>'; \print_r($data); echo '</pre>';
}

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
 * Функция записи системных ошибок в файл
 * @param object $message
 */
// Стоит разделить на 2 функции, одна записывает ошибку в файл, другая завершает скрипт. *
#[NoReturn] function writeExceptionFile(object $message): void
{
    $systemErrorFile = 'system_error.log';
    // Записываем системную ошибку в файл.
    \file_put_contents($systemErrorFile, $message . PHP_EOL, FILE_APPEND);
    // Завершаем выполнение скрипта и выводим ошибку на экран.
    \die ($message->getMessage());
}

/**
 * Функция для экранирования элементов массива.
 * @param array $data
 * @return array
 */
function validateData(array &$data): array
{
    $validateData = [];

    foreach ($data as &$element) {
        $validateElement = connectionDB()->quote(\htmlspecialchars(\strip_tags(\trim($element))));
        $validateData[] = $validateElement;
    }

    return (array) $validateData;
}

/**
 * Получаем массив с данными пользователей.
 * @param PDO $connection
 * @param string $order
 * @return array
 */
function getUsers(\PDO $connection, string $order): array
{
    // Обработчик системных ошибок.
    try {
        // Запрос на получение данных всех пользователей.
        $query = "SELECT * FROM users ORDER BY {$order} DESC";

        // Подготавливаем запрос к выполнению.
        $sth = $connection->prepare($query);

        // Запускаем подготовленный запрос на выполнение.
        $sth->execute();

        // Загружаем в переменную оставшиеся строки из набора результатов.
        $result = $sth->fetchAll();

        // Обработчик пользовательских ошибок.
        if (!$result) {
            // Выбрасываем ошибку в конструктор объекта класса и завершаем выполнение скрипта.
            throw new \Error('Users not found');
        }

    // Ловим исключения и обрабатываем их в специальной функции.
    } catch (\Exception $e) {
        // Записываем исключения в файл и выводим ошибку на экран.
        writeExceptionFile($e);
    }

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
    // Обработчик системных ошибок.
    try {
        // Запрос на получение данных одного пользователя по айди.
        $query = 'SELECT * FROM users WHERE id=? LIMIT 1';

        // Подготавливаем запрос к выполнению.
        $sth = $connection->prepare($query);

        // Передаем айди для позиционного параметра на вход и запускаем подготовленный запрос на выполнение.
        $sth->execute([$id]);

        // Извлекаем следующую строку с данными пользователя из результирующего набора.
        $result = $sth->fetch();

        // Обработчик пользовательских ошибок.
        if (!$result) {
            // Выбрасываем ошибку в конструктор объекта класса и завершаем выполнение скрипта.
            throw new \Error('User not found.');
        }
    // Ловим исключения и обрабатываем их в специальной функции.
    } catch (\Exception $e) {
        // Записываем исключения в файл и выводим ошибку на экран.
        writeExceptionFile($e);
    }

    // Возвращаем ассоциативный массив данных пользователя.
    // Ключи берутся из название столбцов.
    return (array) $result;
}


/**
 * Добавляем нового пользователя.
 * @param PDO $connection
 * @param array $data
 * @return int
 */
function saveUser(\PDO $connection, array $data): int
{
    // Обработчик системных ошибок.
    try {
        // Запрос добавляющий пользователя с данными.
        $query = 'INSERT INTO users (name, email, phone_number, password, is_active) VALUES(:name, :email, :phone_number, :password, :is_active)';

        // Подготавливаем запрос к выполнению.
        $sth = $connection->prepare($query);

        // Экранируем вводимые данные пользователя.
        $validateData = validateData($data);

        // Передаем данные пользователя для именованных параметров.
        // И запускаем подготовленный запрос на выполнение.
        $sth->execute([
            ':name' => $validateData[0],
            ':email' => $validateData[1],
            ':phone_number' => $validateData[2],
            ':password' => $validateData[3],
            ':is_active' => $validateData[4],
        ]);

        // Получаем ID последней вставленной строки.
        $result = $connection->lastInsertId();

    // Ловим исключения и обрабатываем их в специальной функции.
    } catch (\Exception $e) {
        // Записываем исключения в файл и выводим ошибку на экран.
        writeExceptionFile($e);
    }

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
    // Обработчик системных ошибок.
    try {
        // Запрос на получение почты пользователя.
        $query = 'SELECT * FROM users WHERE email=? LIMIT 1';

        // Подготавливаем запрос к выполнению.
        $sth = $connection->prepare($query);

        // Экранируем почту.
        $validateEmail = $connection->quote($email);

        // Передаем почту для позиционного параметра.
        // И запускаем подготовленный запрос на выполнение.
        $sth->execute([$validateEmail]);

        // Получаем информацию о пользователе, если почта совпала.
        $result = $sth->fetch();

    // Ловим исключения и обрабатываем их в специальной функции.
    } catch (\Exception $e) {
        // Записываем исключения в файл и выводим ошибку на экран.
        writeExceptionFile($e);
    }

    // Возвращаем true, если пользователь с такой почтой существует, иначе false.
    return (bool) $result ?? false;
}

/**
 * Проверка на наличие пользователя в БД по номеру телефона.
 * @param PDO $connection
 * @param string $phoneNumber
 * @return bool
 */
function checkUserPhoneNumber(\PDO $connection, string $phoneNumber): bool
{
    try {
        // Запрос на получение номера телефона пользователя.
        $query = 'SELECT * FROM users where phone_number=? LIMIT 1';

        // Подготавливаем запрос к выполнению.
        $sth = $connection->prepare($query);

        // Экранируем номер телефона.
        $validatePhoneNumber = $connection->quote($phoneNumber);

        // Передаем номер телефона для позиционного параметра.
        // И запускаем подготовленный запрос на выполнение.
        $sth->execute([$validatePhoneNumber]);

        // Получаем информацию о пользователе, если номер телефона совпал.
        $result = $sth->fetch();

    // Ловим исключения и обрабатываем их в специальной функции.
    } catch (\Exception $e) {
        // Записываем исключения в файл и выводим ошибку на экран.
        writeExceptionFile($e);
    }

    // Возвращаем true, если пользователь с таким номером телефона существует, иначе false.
    return (bool) $result ?? false;
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
    // Обработчик системных ошибок.
    try {
        // Запрос для обновления данных пользователя.
        $query = 'UPDATE users SET name = :name, email = :email, phone_number = :phone_number, password = :password WHERE id = :id LIMIT 1';

        // Подготавливаем запрос к выполнению.
        $sth = $connection->prepare($query);

        // Экранируем вводимые данные пользователя.
        $validateData = validateData($data);

        // Передаем измененные данные пользователя для именованных параметров.
        // И запускаем подготовленный запрос на выполнение.
        $sth->execute([
            ':name' => $validateData[0],
            ':email' => $validateData[1],
            ':phone_number' => $validateData[2],
            ':password' => $validateData[3],
            // Айди не проходит экранирование, т.к он будет получен с сессии, либо введен админом *
            ':id' => $userId,
        ]);

        // Получаем количество затронутых строк.
        $rowCount = $sth->rowCount();

        // Обработка пользовательских ошибок.
        if (!$rowCount) {
            // Выбрасываем ошибку, если не тот айди.
            throw new \Error('Error update user data');
        }

    // Ловим исключения и обрабатываем их в специальной функции.
    } catch (\Exception $e) {
        // Записываем исключения в файл и выводим ошибку на экран.
        writeExceptionFile($e);
    }

    // Возвращаем массив измененных данных пользователя.
    return (array) $validateData;
}

/**
 * Удаляем пользователя с его данными.
 * @param PDO $connection
 * @param int $userId
 * @return bool
 */
function deleteUser(\PDO $connection, int $userId): bool
{
    // Обработчик системных ошибок.
    try {
        // Запрос для удаления пользователя по айди.
        $query = 'DELETE FROM users WHERE id=? LIMIT 1';

        // Подготавливаем запрос к выполнению.
        $sth = $connection->prepare($query);

        // Передаем позиционный параметр айди.
        // И запускаем подготовленный запрос на выполнение.
        // Может лучше использовать exec чтобы вернуть кол-во строк?
        $result = $sth->execute([$userId]);

        // Получаем количество удаленых строк.
        $rowCount = $sth->rowCount();

        // Обработчик пользовательских ошибок.
        if (!$rowCount) {
            // Выбрасываем ошибку в класс Error, если кол-во затронутых строк равно нулю.
            throw new \Error('Error delete user');
        }

    // Ловим исключения и обрабатываем их в специальной функции.
    } catch (\Exception $e) {
        // Записываем исключения в файл и выводим ошибку на экран.
        writeExceptionFile($e);
    }

    // Возвращаем true, если пользователя удалили.
    return (bool) $result;
}

$connectionDB = connectionDB();
// Создать функцию для автоматического тестирования этого "интерфейса" *
// В action и айди будут автоматически передаваться нужные названия функций и через БД будет проходить 10% БД *

// Модель исключений "вылавливающая возможные ошибки"
try {
    // Определяем метод взаимодействия с данными пользователя.
    $action = 'getUsers';

    // Определяем айди пользователя.
    $id = 31;
    if ($action === 'delete') {
        // Удаляем пользователя.
        $deleteUser = deleteUser($connectionDB, $id);
    } elseif ($action === 'update') {
        // Обновляем пользователя.
        $data = [
            'name' => 'tvxcvas',
            'email' => 'tasdads3@gmail.com',
            'phone_number' => '7591541231',
            'password' => \password_hash('fada', PASSWORD_DEFAULT),
        ];
        $updateUser = updateUser($connectionDB, $data, $id);
    } elseif ($action === 'save') {
        // Добавляем пользователя.
        $data = [
            'name' => 'lmnk',
            'email' => 'lmnk@gmail.com',
            'phone_number' => '1321asd32312',
            'password' => \password_hash('1234asd124', PASSWORD_DEFAULT),
            'is_active' => '1'
        ];
        $saveUser = saveUser($connectionDB, $data);
    } elseif ($action === 'check') {
        // Проверяем наличие вводимых данных в БД.
        $email = 'tezst@gmail.com';
        $phone = '7051241w2432';
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
        $getUsers = getUsers($connectionDB, $table);
        dump($getUsers);
    } elseif ($action === 'getUser') {
        // Получаем данные пользователя через его айди.
        $getUser = getUser($connectionDB, $id);
        dump($getUser);
    }
// Блок определяющий как реагировать на выброшенное исключение.
}
catch (\Error $e) {
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
