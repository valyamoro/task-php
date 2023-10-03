<?php
declare(strict_types=1);
error_reporting(-1);

// Функция конвертирует данные в более удобный формат.
function dump(mixed $data): void
{
    echo '<pre>'; \print_r($data); echo '</pre>';
}

// Функция возвращает либо объект класса из глобальной области видимости PDO либо значение null
function connectionDB(): ?\PDO
{
    static $dbh;

    if (!\is_null($dbh)) {
        return $dbh;
    }
    // Создаем экземпляр глобального класса PDO
    try {
        $dbh = new \PDO(
        // Задаем строку DSN содержащая информацию для подключения к mysql
            'mysql:host=localhost;dbname=mvc-int-shop;charset=utf8mb4',
            // Задаем имя пользователя для строки DSN
            'root',
            // Задаем пароль для строки DSN
            '',
            // Задаем для драйвера настройки подключения.
            [
                // Устанавливаем режим сообщения об ошибках, выбрасывающий
                // исключение PDOException, отправляющий код ошибки и ее описание.
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,

                // Указываем режим извлечения данных, в виде ассоциативных массивов.
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,

                // Устанавливаем расширенную версию utf-8 более подходящую для работы с БД
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
            ]
        );
    // Обработка возможных исключений.
    } catch (\PDOException $e) {
        // Если не получается подключится, то отправляет ошибку в класс Error
        die ('Connection error:' . $e->getMessage());
    }
    // Возвращаем заполненный настройками объект глобального класса PDO
    return $dbh;
}


/**
 * @param PDO $connection
 * @param string $order
 * @return array
 */
function getUsers(\PDO $connection, string $order): array
{
    // Запрос сортирующий пользователей по таблицам по убыванию.
    $query = "SELECT * FROM users ORDER BY {$order} DESC";

    // Подготавливаем запрос для исполнения.
    $sth = $connection->prepare($query);

    // Выполняем запрос.
    $sth->execute();

    // Создаем массив содержащий все строки из набора результатов.
    $result = $sth->fetchAll();

    if (!$result) {
        // Создаем экземпляр глобального класса Error и отправляем туда информацию
        // об ошибке.
        throw new \Error('Users not found');
    }
    // Возвращаем переменную с массивом данных всех строк.
    return (array) $result;
}

/**
 * @param PDO $connection
 * @param int $id
 * @return array
 */
function getUser(\PDO $connection, int $id): array
{
    // Запрос сортирующий данные из таблицы users по id с плейсхолдером,
    // оператор limit допускает возвращение только одного значения.
    $query = 'SELECT * FROM users WHERE id=? LIMIT 1';

    // Подготовка запроса.
    $sth = $connection->prepare($query);

    // Выполняем подготовленный sql-запрос с параметром содержащий айди пользователя.
    // Сюда передается айди пользователя для обеспечения безопасности приложения от SQL-инъекций.
    $sth->execute([$id]);

    // Создаем массив содержащий одну строку набора результата.
    $result = $sth->fetch();
    if (!$result) {
        // Создаем экземпляр глобального класса Error и отправляем туда информацию
        // об ошибке.
        throw new \Error('User not found.');
    }

    // Возвращаем переменную с массивом данных пользователя.
    return (array) $result;
}

/**
 * @param PDO $connection
 * @param array $data
 * @return int
 */
function saveUser(\PDO $connection, array $data): int
{
    // Запрос добавляющий запись данных пользователя в таблицу users.
    // Запрос содержит плейсхолдеры для будущих значений.
    $query = 'insert into users (name, email, phone_number, password, is_active) VALUES (?,?,?,?,?)';

    // Подготовка запроса.
    $sth = $connection->prepare($query);

    // Выполняем подготовленный запрос, передавая массив содержащий данные пользователя
    // в качестве параметра в функции array_values для получения значения массива для подстановки в запрос.
    $sth->execute(\array_values($data));

    // Получаем айди последней созданной записи.
    $result = $connection->lastInsertId();
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
    // Собираем все почты всех пользователей.
    $query = "SELECT * FROM users WHERE email=?";

    // Подготовка запроса.
    $sth = $connection->prepare($query);
    // Исполняем запрос передавая туда почту пользователя.
    $sth->execute([$email]);
    // Создаем переменную содержащую всю информацию о пользователе, иначе false.
    $result = $sth->fetch();

    // Возвращаем булево значение true, если пользователь с такой почтой существует.
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
    // Собираем все номера телефонов всех пользователей.
    $query = "SELECT * FROM users where phone_number=?";

    // Подготовка запроса.
    $sth = $connection->prepare($query);

    // Исполняем запрос передвая туда номер телефона пользователя.
    $sth->execute([$phoneNumber]);

    // Создаем переменную содержащую всю информацию о пользователе, иначе false
    $result = $sth->fetch();
    // Возвращаем true, если пользователь с таким номером существует.
    return (bool) $result;
}

// Функция обновляющая информацию о пользователе
// Принимает объект глобального класса PDO
// Принимает айди нужного пользователя.
// Принимает переменные с новыми данными
// Ничего не возвращает.
function updateUser(\PDO $connection, int $userId, string $newName, string $newEmail, string $newPhone): never
{
    // Запрос обновляющий информацию о пользователе по айди.
    $sql = "UPDATE users SET name = :name, email = :email, phone_number = :phone_number WHERE id = :id";

    // Подготовка запроса.
    $sth = $connection->prepare($sql);

    // Связываем ИМЕНОВАННЫЕ параметры запроса с нужной переменной.
    $sth->bindParam(':name', $newName, PDO::PARAM_STR);
    $sth->bindParam(':email', $newEmail, PDO::PARAM_STR);
    $sth->bindParam(':phone_number', $newPhone, PDO::PARAM_STR);
    $sth->bindParam(':id', $userId, PDO::PARAM_INT);

    // Выполняем запрос.
    $sth->execute();
    // Прерываем выполнение функции.
    die;
}

// Функция удаления пользователя из БД.
// Принимает на вход объект глобального класса PDO.
// Принимает айди нужного пользователя.
// Ничего не возвращает.
function deleteUser(\PDO $connection, int $userId): never
{
    // Запрос удаляющий пользователя из БД по айди.
    $sql = 'DELETE FROM users WHERE id= :id';

    // Подготовка запроса.
    $sth = $connection->prepare($sql);

    // Связываем именной параметр с переменной айди пользователя.
    $sth->bindParam(':id', $userId, PDO::PARAM_INT);

    // Выполняем запрос
    $result = $sth->execute();

    $sth->execute();

    // Прерываем выполнение функции.
    die;
}

$connectionDB = connectionDB();


// Создаем переменную содержащую информацию о всех пользователях.
// Передаем переменную с информацией о БД и таблицу в качестве параметров.
$users = getUsers($connectionDB, 'email');


// Модель исключений "вылавливающая возможные ошибки"
try {
    // Определяем метод взаимодействия с данными пользователя.
    $action = 'getUsers';

    // Определяем айди пользователя.
    $id = 22;


    if ($action === 'delete') {
        // Удаляем пользователя.
        $deleteUser = deleteUser($connectionDB, $id);
    } elseif ($action === 'update') {
        // Обновляем пользователя.
        $name = 'test2';
        $email = 'test2@gmail.com';
        $phone = '7891341231';
        $updateUser = updateUser($connectionDB, $id, $name, $email, $phone);
    } elseif ($action === 'save') {
        // Добавляем пользователя.
        $data = [
            'name' => 'test',
            'email' => 'test@gmail.com',
            'phone_number' => '790735472',
            'password' => password_hash('1234124', PASSWORD_DEFAULT),
            'is_active' => 1
        ];
        $saveUser = saveUser($connectionDB, $data);
    } elseif ($action === 'check') {
        // Проверяем наличие вводимых данных в БД.
        $email = 'test2@gmail.com';
        $phone = '79';
        $checkUser = checkUser($connectionDB, $email, $phone);
        var_dump($checkUser);
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
