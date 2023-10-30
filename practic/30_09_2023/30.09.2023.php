<?php
/*применить именованные параметры к функцям.
 *
 */
declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

// Установка значения настройки конфигурации протоколирования ошибок.
\ini_set('display_errors', '1');
// Сообщаем обо всех ошибках.
\error_reporting(-1);

/** Пользовательский обработчик исключений.
 * @param $e
 * @return void
 */
#[NoReturn] function myExceptionHandler ($e): void
{
    // Отправляем сообщение об ошибке, заданному обработчику ошибок.
    \error_log($e->getMessage());
    // Устанавливаем код ответа HTTP.
    \http_response_code(500);
    // Устанавливаем режим отображения ошибок.
    if (\ini_get('display_errors') == 1) {
        // Выводим все системных ошибки.
        echo $e;
    } elseif (\ini_get('display_errors') == 0) {
        // Выводим "оправдание" для пользователя.
        echo '<h1>Ошибка 500</h1>';
    }
}

// Задаем пользовательский обработчик ИСКЛЮЧЕНИЙ.
\set_exception_handler('myExceptionHandler');

// Задаем пользовательский обработчик ОШИБОК.
set_error_handler(/**
 * @throws ErrorException
 */ function ($level, $message, $file = '', $line = 0)
{
    // Выбрасываем в класс ErrorException нужную информацию.
    throw new \ErrorException($message, 0, $level, $file, $line);
});

// Выполняем заданную функцию если работа приложения завершилась.
\register_shutdown_function(function ()
{
    // Получаем информацию о последней произошедшей ошибке.
    $error = \error_get_last();
    // Если появилась ошибка:
    if ($error !== null) {
        // Создаем экземпляр класса с настройками.
        $e = new ErrorException(
            $error['message'], 0, $error['type'], $error['file'], $error['line']
        );
        // Передаем в пользовательский обработчик исключений информацию об ошибке.
        myExceptionHandler($e);
    }
});

/**
 * Выводим удобочитаемую информацию о переменной.
 * @param mixed $data
 * @return void
 */
function dump(mixed $data): void
{
    echo '<pre>';
    \print_r($data);
    echo '</pre>';
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
    // При последующих вызовах этой функции, переменная не будет пересоздаваться.
    static $dbh = null;

    // Предотвращаем пересоздание объекта PDO.
    if (!\is_null($dbh)) {
        return $dbh;
    }

    // Задаем настройки для подключения к БД.
    $options = [
        // Режим сообщения об ошибках. Выбрасывает исключения в PDOException.
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,

        // Режим выборки. Возвращает массив индексированный именами столбцов результирующего набора.
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,

        // При подключении автоматически выполняем команду установки кодировки.
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DB_CHARSET,
    ];

    // Определяем параметры для строки источника данных.
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    // Создаем объект для подключения к БД с настройками.
    $dbh = new \PDO($dsn, DB_USER, DB_PASSWORD, $options);

    // Возвращаем объект PDO с настройками.
    return $dbh;
}

$connectionDB = connectionDB();

// Определяем метод взаимодействия с данными пользователя.
$action = 'getUsers';

// Определяем айди пользователя.
$id = 69;

// Определяем данные пользователя.
$data = [
    'name' => 'qwdqwdqw',
    'email' => 'zxcdas@gmail.com',
    'phone_number' => '7521541231',
    'password' => '1zxcvzxcvDda2s2',
];

// Определяем данные для проверки на существование в базе данных.
$checkData = [
    'email' => 'zxcdas@gmail.1com',
    'phone_number' => '75215141231',
];

if ($action === 'delete') {
    // Удаляем пользователя.
    $deleteUser = deleteUser($connectionDB, $id);
} elseif ($action === 'update') {
    // Обновляем пользователя.
    validateData($data) ?? die;
    $quoteData = quoteData($data);
    $updateUser = updateUser($connectionDB, $quoteData, $id);
} elseif ($action === 'save') {
    // Добавляем пользователя.
    validateData($data) ?? die;
    $quoteData = quoteData($data);
    $saveUser = addUser($connectionDB, $quoteData);
} elseif ($action === 'check') {
    // Проверяем наличие вводимых данных в БД.
    validateData($checkData) ?? die;
    $quoteData = quoteData($checkData);
    $checkExistData = checkData($quoteData);
    $check = $checkExistData ? 'Данные существуют' : 'Данные не существуют';
    echo $check;
} elseif ($action === 'getUsers') {
    // Получаем всех пользователей.
    $table = 'name';
    $getUsers = getUsers($connectionDB, $table);
    dump($getUsers);
} elseif ($action === 'getUser') {
    // Получаем данные пользователя через его айди.
    $getUser = getUser($connectionDB, $id);
    dump($getUser);
}

/**
 * Функция для проверки существования данных.
 * @param array $data
 * @return bool|null
 */
function checkData(array $data): ?bool
{
    // Создаем пустой массив.
    $existsData = [];

    // Обозначаем какие функции к каким данным нужно применить.
    $checkFunctions = [
        'email' => 'checkUserEmail',
        'phone_number' => 'checkUserPhoneNumber',
    ];
    // Перебираем приходящие данные.
    foreach ($data as $key => $value) {
        // Если в checkFunctions существуют ключи из $data, то выполняем код.
        if (array_key_exists($key, $checkFunctions)) {
            // На каждой итерации присваиваем переменной ключ.
            $checkFunction = $checkFunctions[$key];
            // На каждой итерации присваиваем переменной значение элементов $checkFunctions.
            $existsData[$key] = $checkFunction(connectionDB(), $value);
        }
    }

    // Создаем пустой массив.
    $isUserExist = [];

    // Перебираем результат выполнения функций.
    foreach ($existsData as $element) {
        // Если элемент не пуст, то выполняем.
        if (!empty($element)) {
            // Присваиваем массиву каждый не пустой элемент.
            $isUserExist[] = $element;
        }
    }

    // Возвращаем true, если данные есть в БД.
    return (bool) $isUserExist;
}

/**
 * Функция валидации данных.
 * @param array $data
 * @return array|null
 */
function validateData(array $data): ?array
{
    // Создаем пустой массив с будущими ошибками.
    $errors = [];

    // Обозначаем какие функции к каким данным нужно применить.
    $validationFunctions = [
        'email' => 'validateEmail',
        'phone_number' => 'validatePhoneNumber',
        'password' => 'validatePassword',
        'name' => 'validateUserName',
    ];

    // Перебираем приходящие данные.
    foreach ($data as $key => $value) {
        // Если в $validationFunctions существуют ключи из $data, то выполняем код.
        if (array_key_exists($key, $validationFunctions)) {
            // На каждой итерации присваиваем переменной значение элементов $validationFunctions.
            $validationFunction = $validationFunctions[$key];
            // Присваиваем массиву результат выполнения функций из $validationFunctions.
            $errors[$key] = $validationFunction($value);
        }
    }

    // Создаем пустой массив.
    $checkValidate = [];

    // Перебираем результат выполнения функций.
    foreach ($errors as $element) {
        // Если элемент не пуст, то выполняем.
        if (!empty($element)) {
            // Выводим ошибку валидации.
            echo $element . '<br>';
            // Присваиваем массиву каждый не пустой элемент.
            $checkValidate[] = $element;
        }
    }

    // Возвращаем null, если есть ошибки.
    return $checkValidate ? null : $checkValidate;
}

/** Функция валидации номера телефона.
 * @param string $phoneNumber
 * @return string
 */
function validatePhoneNumber(string $phoneNumber): string
{
    $msg = '';

    if (empty($phoneNumber)) {
        $msg .= 'Заполните поле номер' . PHP_EOL;
    } elseif (!\preg_match('/((8|\+7)-?)?\(?\d{3,5}\)?-?\d{1}-?\d{1}-?\d{1}-?\d{1}-?\d{1}((-?\d{1})?-?\d{1})?/',
        $phoneNumber)) {
        $msg .= 'Некоректный номер' . $phoneNumber . PHP_EOL;
    } elseif (!\preg_match('/^[^!№;a-z]+$/u', $phoneNumber)) {
        $msg .= 'Недопустимые символы';
    }

    return $msg;
}

/** Функция валидации почты.
 * @param string $email
 * @return string
 */
function validateEmail(string $email): string
{
    $msg = '';

    if (empty($email)) {
        $msg .= 'Заполните поле почты' . PHP_EOL;
    } elseif (!\preg_match("/[0-9a-z]+@[a-z]/", $email)) {
        $msg .= 'Почта содержит недопустимые данные' . PHP_EOL;
    }

    return $msg;
}

/** Функция валидации пароля.
 * @param string $password
 * @return string
 */
function validatePassword(string $password): string
{
    $msg = '';

    if (empty($password)) {
        $msg .= 'Заполните поле пароль' . PHP_EOL;
    } elseif (!\preg_match('/^(?![0-9]+$).+/', $password)) {
        $msg .= 'Пароль не должен содержать только цифры' . PHP_EOL;
    } elseif (!\preg_match('/^(?![A-Za-z]+$).+/', $password)) {
        $msg .= 'Пароль не должен состоять только из букв' . PHP_EOL;
    } elseif (!\preg_match('/[A-Z]/', $password)) {
        $msg .= 'Пароль должен содержать минимум одну заглавную букву' . PHP_EOL;
    } elseif (\mb_strlen($password, 'utf8') <= 5) {
        $msg .= 'Пароль содержит меньше 5 символов' . PHP_EOL;
    } elseif (\mb_strlen($password, 'utf8') > 15) {
        $msg .= 'Пароль больше 15 символов' . PHP_EOL;
    }

    return $msg;
}

/** Функция валидации имени пользователя.
 * @param string $userName
 * @return string
 */
function validateUserName(string $userName): string
{
    $msg = '';

    if (empty($userName)) {
        $msg .= 'Заполните поле имя' . PHP_EOL;
    } elseif (\preg_match('#[^а-яa-z]#ui', $userName)) {
        $msg .= 'Имя содержит недопустимые символы' . PHP_EOL;
    } elseif (\mb_strlen($userName, 'utf8') > 15) {
        $msg .= 'Имя содержит больше 15 символов' . $userName . PHP_EOL;
    } elseif (\mb_strlen($userName, 'utf8') <= 3) {
        $msg .= 'Имя содержит менее 4 символов'. $userName . PHP_EOL;
    }

    return $msg;
}

/**
 * Экранирование данных.
 * @param mixed $data
 * @return array
 */
function quoteData(array $data): array
{
    // Создаем пустой массив.
    $quoteData = [];

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

    // Возвращаем ассоциативный массив данных всех пользователей.
    return (array) $sth->fetchAll();
}

/**
 * Получаем массив с данными пользователя.
 * @param PDO $connection
 * @param int $id 21
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

    // Возвращаем ассоциативный массив данных пользователя.
    return (array) $sth->fetch();
}

/**
 * Добавляем нового пользователя.
 * @param PDO $connection
 * @param array $data
 * @return int
 */
function addUser(\PDO $connection, array $data): int
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

    // Возвращаем айди последней созданной записи.
    return (int) $connection->lastInsertId();
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

    // Возвращаем true, если пользователь с такой почтой существует, иначе false.
    return (bool) $sth->fetch();
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

    // Возвращаем true, если пользователь с таким номером телефона существует, иначе false.
    return (bool) $sth->fetch();
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
        ':id' => $userId,
    ]);

    // Возвращаем массив измененных данных пользователя.
    return (array) $data;
}

