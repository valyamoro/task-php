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

/**
 * Подключемся к БД через глобальный класс PDO
 * @return PDO|null
 */
function connectionDB(): ?\PDO
{
    // При последующих вызовах этой функции значение переменной сохранится.
    static $dbh = null;

    // Если функцию уже вызывали, то возвращаем текущее значение.
    if (!\is_null($dbh)) {
        return $dbh;
    }

    // Модель исключений.
    try {
        // Создаем объект, и задаем настройки для подключения к БД.
        $dbh = new \PDO(
            // Строка с источником данных.
            'mysql:host=localhost;dbname=mvc-int-shop;charset=utf8mb4',
            // Имя хоста.
            'root',
            // Пароль для подключения.
            '',
            // Опции объекта.
            [
                // Режим сообщения об ошибок в режиме выбрасывания PDOException.
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,

                // Режим выборки, при котором каждая строка из БД возвращается в виде ассоциативного массива.
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,

                // При подключении автоматически выполняем команду установки кодировки.
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            ]
        );
    // Если есть ошибки, то они преобразуются в PDOException и вылавливаются тут:
    } catch (\PDOException $e) {
        // Прерываем выполнение скрипта и выводим ошибку на экран.
        die ('Connection error: ' . $e->getMessage());
    }

    // Возвращаем объект PDO с настройками.
    return $dbh;
}

// ФУНКЦИЯ ОБРАБОТКИ ОШИБОК!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// Должна отправлять ошибки в файл.
// И выводить на экран.

/**
 * Получаем массив с данными пользователей.
 * @param PDO $connection
 * @param string $order
 * @return array
 */
function getUsers(\PDO $connection, string $order): array
{
    // Запрос на получение данных пользователей.
    $query = "SELECT * FROM users ORDER BY {$order} DESC";

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Запускаем подготовленный запрос на выполнение.
    $sth->execute();

    // Загружаем в переменную оставшиеся строки из набора результатов.
    $result = $sth->fetchAll();

    // Обработчик ошибок.
    if (!$result) {
        // Выбрасываем ошибку в конструктор объекта класса и завершаем выполнение скрипта.
        throw new \Error('Users not found');
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
    // Запрос на получение данных одного пользователя по айди.
    $query = 'SELECT * FROM users WHERE id=? LIMIT 1';

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Передаем айди для именовоного параметра на вход и запускаем подготовленный запрос на выполнение.
    // Прогнать айди через quote *
    $sth->execute([$id]);

    // Извлекаем следующую строку с данными пользователя из результирующего набора.
    $result = $sth->fetch();

    // Обработчик ошибок.
    if (!$result) {
        // Выбрасываем ошибку в конструктор объекта класса и завершаем выполнение скрипта.
        throw new \Error('User not found.');
    }

    // Возвращаем массив данных пользователя.
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
    // Запрос добавляющий пользователя с данными.
    $query = 'INSERT INTO users (name, email, phone_number, password, is_active) VALUES (?,?,?,?,?)';

    // Подготавливаем запрос к выполнению.
    $sth = $connection->prepare($query);

    // Передаем данные пользователя для позиционных параметров, перед этим удаляя все ключи.
    // И запускаем подготовленный запрос на выполнение.
    // ЭКРАНИРОВАТЬ ЭЛЕМЕНТЫ *
    $sth->execute(\array_values($data));

    // Получаем ID последней вставленной строки.
    $result = $connection->lastInsertId();

    // Обработчик ошибок.
    if (!$result) {
        // Создаем объект глобального класса содержащий информацию об ошибке.
        throw new \Error('Error save user');
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
    try {
        // Запрос на получение почт всех пользователей.
        $query = 'SELECT * FROM users WHERE email=?';

        // Подготавливаем запрос к выполнению.
        $sth = $connection->prepare($query);
        // Передаем почту для позиционного параметра.
        // И запускаем подготовленный запрос на выполнение.
        $sth->execute([$email]);

        // Создаем переменную содержащую всю информацию о пользователе, иначе false.
        $result = $sth->fetch();

        // Возвращаем булево значение true, если пользователь с такой почтой существует.
        return (bool) $result;

    } catch (\Exception $e) {
        // Записываем в файл информацию об ошибка, если в есть проблема в синтаксисе запроса.
        file_put_contents('errors.log', $e->getMessage() . PHP_EOL, FILE_APPEND);
        // Заваршаем выполнения скрипта и отправляем ошибку
        die ($e->getMessage());
    }
}

$connectionDB = connectionDB();
print_r(checkUserEmail($connectionDB, 'test3@gmail.com'));

die;

/**
 * Проверка на наличие пользователя в БД по номеру телефона.
 * @param PDO $connection
 * @param string $phoneNumber
 * @return bool
 */
function checkUserPhoneNumber(\PDO $connection, string $phoneNumber): bool
{
    // Собираем все номера телефонов всех пользователей.
    $query = 'SELECT * FROM users where phone_number=?';

    // Подготовка запроса.
    $sth = $connection->prepare($query);

    // Исполняем запрос передвая туда номер телефона пользователя.
    $sth->execute([$phoneNumber]);

    // Создаем переменную содержащую всю информацию о пользователе, иначе false
    $result = $sth->fetch();

    // Использую строгое равенство, т.к null может быть приведен из других типов
    if ($result === null) {
        // Выбрасываем ошибку в класс Error если есть.
        throw new \Error('Error check user phone');
    }
    // Возвращаем true, если пользователь с таким номером существует.
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
    // Запрос обновляющий информацию о пользователе по айди.
    $query = 'UPDATE users SET name = :name, email = :email, phone_number = :phone_number, password = :password WHERE id = :id';

    // Подготовка запроса.
    $sth = $connection->prepare($query);

    /* Решил оставить bindParam, т.к он обеспечивает защиту от SQL-инъекций
    и явно указывает тип принимаемых значений.
    */

    $sth->bindParam(':name', $data['name'], PDO::PARAM_STR);
    $sth->bindParam(':email', $data['email'], PDO::PARAM_STR);
    $sth->bindParam(':phone_number', $data['phone'], PDO::PARAM_STR);
    $sth->bindParam(':password', $data['password'], PDO::PARAM_STR);
    $sth->bindParam(':id', $userId, PDO::PARAM_INT);

    // Выполняем запрос.
    $sth->execute();

    if (!$data) {
        // Выбрасываем ошибку в класс Error если есть.
        throw new \Error('Error update user data');
    }
    // Возвращаем массив измененных данных
    // Т.к валидации нет, то беру $data напрямую.
    return (array) $data;
}

/**
 * Удаляем пользователя с его данными.
 * @param PDO $connection
 * @param int $userId
 * @return bool
 */
function deleteUser(\PDO $connection, int $userId): bool
{
    // Запрос удаляющий пользователя из БД по айди.
    $query = 'DELETE FROM users WHERE id= :id';

    // Подготовка запроса.
    $sth = $connection->prepare($query);

    // Решил не использовать bindParam, т.к тут не нужна защита от SQL-инъекций.

    // Выполняем запрос
    $result = $sth->execute([':id' => $userId]);

    if (!$result) {
        // Выбрасываем ошибку в класс Error если есть.
        throw new \Error('Error delete user');
    }
    // Возвращаем true, если пользователь удалился.
    return (bool) $result;

}

$connectionDB = connectionDB();

// Модель исключений "вылавливающая возможные ошибки"
try {
    // Определяем метод взаимодействия с данными пользователя.
    $action = 'getUsers';

    // Определяем айди пользователя.
    $id = 28;
    if ($action === 'delete') {
        // Удаляем пользователя.
        $deleteUser = deleteUser($connectionDB, $id);
    } elseif ($action === 'update') {
        // Обновляем пользователя.
        $data = [
            'name' => 'test5',
            'email' => 'test3@gmail.com',
            'phone' => '7891541231',
            'password' => password_hash('fada', PASSWORD_DEFAULT),
        ];
        $updateUser = updateUser($connectionDB, $data, $id);
    } elseif ($action === 'save') {
        // Добавляем пользователя.
        $data = [
            'name' => 'test3',
            'email' => 'test3@gmail.com',
            'phone_number' => '132132312',
            'password' => password_hash('1234124', PASSWORD_DEFAULT),
            'is_active' => 1
        ];
        $saveUser = saveUser($connectionDB, $data);
    } elseif ($action === 'check') {
        // Проверяем наличие вводимых данных в БД.
        $email = 'test@gmail.com';
        $phone = '79073547211';
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
    file_put_contents('errors.log', $e->getMessage() . PHP_EOL, FILE_APPEND);
    // Заваршаем выполнения скрипта и отправляем ошибку
    die ($e->getMessage());
} finally  {
    // В любом другом случаи записываем в файл определенную информацию.
    file_put_contents('user22.txt', 'get user' . PHP_EOL, FILE_APPEND);
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
