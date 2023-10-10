<?php
/*
 * Применить транзакции, там где нужно.
 * Использовать лимиты везде где только можно.
 * Вынести интерфейс бекенда наверх.
 * Создать отдельные функции для каждой валидации и затем вызвать их внутри другой функции.
 * Изменить валидацию номера телефона. Она работает неправильно.
 * Изменить валидацию пароля, нельзя ограничивать символы.
 * Нужно полностью обезопасить приложение.
 * Применить кодировку через declare.
 */
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

$connectionDB = connectionDB();

// Модель исключений
try {
    // Определяем метод взаимодействия с данными пользователя.
    $action = 'save';

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
        quoteData($data);
        $updateUser = updateUser($connectionDB, $data, $id);
    } elseif ($action === 'save') {
        // Добавляем пользователя.
        $data = [
            'name' => 'gkutlu1mbek',
            'email' => 'lmn.com',
            'phone_number' => 'as890935ads23212ddd',
            'password' => '1xr#be%r123',
            'is_active' => '1',
        ];
        dump(validateData($data));
        quoteData($data);
        die;
        $saveUser = saveUser($connectionDB, $data);
    } elseif ($action === 'check') {
        // Проверяем наличие вводимых данных в БД.
        $email = 'tasdads3@gmail.com';
        $phone = '7051241w2432';
        $userData = quoteData(['email' => $email, 'phone_number' => $phone]);

        $checkUserEmail = checkUserEmail($connectionDB, $userData['email']);
        $checkUserPhoneNumber = checkUserPhoneNumber($connectionDB, $userData['phone_number']);

        if (!($checkUserPhoneNumber || $checkUserEmail)) {
            echo 'Пользователя с такими данными нет';
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
}  catch (\PDOException $e) {
    // Записываем в файл информацию об ошибке определенной в классе Error в функциях.
    \file_put_contents('errors.log', $e->getMessage() . PHP_EOL, FILE_APPEND);
    // Заваршаем выполнения скрипта и отправляем ошибку
    die ($e->getMessage());
} finally  {
    // В любом другом случаи записываем в файл определенную информацию.
    \file_put_contents('user22.txt', 'get user' . PHP_EOL, FILE_APPEND);
}

function validateData($data)
{
    $msg[] = validateEmail($data['email']);
    $msg[] = validatePhoneNumber($data['phone_number']);
    $msg[] .= validatePassword($data['password']);
    $msg[] .= validateUserName($data['name']);

    return $msg;
}
function validatePhoneNumber($phoneNumber)
{
    $msg = '';

    if (empty($phoneNumber)) {
        $msg .= 'Заполните поле номер' . PHP_EOL;
    } elseif (!preg_match('/((8|\+7)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/',
        $phoneNumber)) {
        $msg .= 'Некоректный номер' . $phoneNumber . PHP_EOL;
    }

    return $msg;
}
function validateEmail(string $email): string
{
    $msg = '';

    if (empty($email)) {
        $msg .= 'Заполните поле почты' . PHP_EOL;
    } elseif (!preg_match("/[0-9a-z]+@[a-z]/", $email)) {
        $msg .= 'Почта содержит недопустимые данные' . PHP_EOL;
    }

    return $msg;
}

function validatePassword(string $password): string
{
    $msg = '';

    if (empty($password)) {
        $msg .= 'Заполните поле пароль' . PHP_EOL;
    } elseif (!preg_match('/^(?![0-9]+$).+/', $password)) {
        $msg .= 'Пароль не должен содержать только цифры' . PHP_EOL;
    } elseif (!preg_match('/^[^!№;]+$/u', $password)) {
        $msg .= 'Пароль содержит недопустимые символы' . PHP_EOL;
    } elseif (!preg_match('/^(?![A-Za-z]+$).+/', $password)) {
        $msg .= 'Пароль не должен состоять только из букв' . PHP_EOL;
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $msg .= 'Пароль должен содержать минимум одну заглавную букву' . PHP_EOL;
    } elseif (mb_strlen($password, 'utf8') <= 5) {
        $msg .= 'Пароль содержит меньше 5 символов' . PHP_EOL;
    } elseif (mb_strlen($password, 'utf8') > 15) {
        $msg .= 'Пароль больше 15 символов' . PHP_EOL;
    }

    return $msg;
}

function validateUserName(string $userName)
{
    $msg = '';

    if (empty($userName)) {
        $msg .= 'Заполните поле имя' . PHP_EOL;
    } elseif (preg_match('#[^а-яa-z]#ui', $userName)) {
        $msg .= 'Имя содержит недопустимые символы' . PHP_EOL;
    } elseif (mb_strlen($userName, 'utf8') > 15) {
        $msg .= 'Имя содержит больше 15 символов' . $userName . PHP_EOL;
    } elseif (mb_strlen($userName, 'utf8') <= 3) {
        $msg .= 'Имя содержит менее 4 символов'. $userName . PHP_EOL;
    }

    return $msg;
}

function editPhoneNumber(string $phoneNumber): string
{
    $editedPhoneNumber = str_replace(['+', '8'], '', $phoneNumber);
    if (strlen($phoneNumber) === 10 && substr($phoneNumber, 0, 1) !== '7') {
        $editedPhoneNumber = '7' . $phoneNumber;
    }

    return (string) $editedPhoneNumber;
}

/**
 * Экранирование данных.
 * @param mixed $data
 * @return array
 */
function quoteData(array $data): array
{
    // Перебираем массив с приходящими данными.
    foreach ($data as $key => $value) {
        // Экранируем каждый элемент массива.
        $quoteData[$key] = connectionDB()->quote(($value));
    }
    // Возвращаем экранированные данные.
    return $quoteData;
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
    $query = 'SELECT * FROM users ORDER BY ? DESC';

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
