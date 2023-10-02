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

// Функция возвращает либо объект класса из глобальной области видимости PDO либо значение null
function connectionDB(): ?\PDO
{
    // Создаем экземпляр глобального класса PDO
    $dbh = new \PDO(
        // Задаем строку DSN содержащая информацию для подключения к mysql
        'mysql:host=localhost;dbname=db-tt;charset=utf8mb4',
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
    // Возвращаем заполненный настройками объект глобального класса PDO
    return $dbh;
}

//добавить комменты ко всем строчкам не бездумные
//сделать обработку ошибок

// Функция для получения пользователей.
// Принимает переменную содержащую объект глобального класса PDO.
// Принимает строку с заказом, по которой будет выполняться сортировка.
// Возвращает массив данных о всех пользователях.
function getUsers(\PDO $connection, string $order): array
{
    // Запрос сортирующий заказы по убыванию.
    $query = "select * from users order by {$order} desc";

    // Повышем производительность и безопасность методом кэширования
    // метаданных и экранирования строки.
    $sth = $connection->prepare($query);

    // Выполняем подготовленный sql-запрос.
    $sth->execute();

    // Создаем массив содержащий все строки из набора результатов.
    $result = $sth->fetchAll();

    // Возвращаем переменную с массивом данных всех строк.
    return $result;
}

// Функция для получения одного пользователя.
// Принимает переменную содеражщую объект глобального класса pDO.
// Принимает целочисленную переменную содержащую айди нужного пользователя.
// Возвращает массив содержащий информацию о пользователе.
function getUser(\PDO $connection, int $id): array
{
    // Запрос сортирующий данных из таблицы users по id с плейсхолдером,
    // оператор limit допускает возвращение только одного значения.
    $query = 'select * from users where id=? limit 1';

    // Повышем производительность и безопасность методом кэширования
    // метаданных и экранирование строки.
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
    //var_dump($result);
    //return $result;
    //return $result !== false ? $result : [];

    // Возвращаем переменную с массивом данных пользователя.
    return $result;
}
//
//name
//email
//phone
//password
//is_active

// Функция добавляющая данные о пользователе в MYSQL.
// Принимает объект глобального класса PDO.
// Принимает данные пользователя в виде массива.
// Возвращает айди добавленного пользователя.
function saveUser(\PDO $connection, array $data): int
{
    // Запрос добавляющий запись данных пользователя в таблицу users.
    // Запрос содержит плейсхолдеры для будущих значений.
    $query = 'insert into users (name, email, phone, password, is_active) VALUES (?,?,?,?,?)';

    // Повышаем производительность и безопасность методом кэширования
    // метаданных и экранирования строки.
    $sth = $connection->prepare($query);

    // Выполняем подготовленный запрос, передвая массив содержащий данные пользователя
    // в качестве параметра функции array_values для переиндексирования массива.
    $sth->execute(\array_values($data));

    // Создаем переменную содержащую последний созданный айди в БД
    $result = $connection->lastInsertId();

    // Возвращаем айди пользователя.
    return (int)$result;
}
//функция проверки пользователя по email и телефону
//если есть сказать об этом
//обновления данных пользователя
//удаление пользователя

// Создаем переменную содержащую результат функции с информацией для подключения к БД.
$connectionDB = connectionDB();

// Создаем переменную содержащую информацию о всех пользователях.
// Передаем переменную с информацией о БД и айди заказа в качестве параметров.
$users = getUsers($connectionDB, 'id');

// Выводим всех пользователей.
print_r($users);

// Модель исключений "вылавливающая возможные ошибки"
try {
    // Определяем айди пользователя.
    $id = 1;

    // Передаем в функцию параметры переменную с информацией о БД и айди пользователя.
    $user = getUser($connectionDB, $id);

// Блок определяющий как реагировать на выброшенное исключение.
} catch (\Error $e) {
    // Записываем в файл информацию об ошибке определенной в классе Error в функции getUser.
    file_put_contents('errors.log', $e->getMessage() . PHP_EOL, FILE_APPEND);
    // Заваршаем выполнения скрипта и отправляем ошибку
    die ($e->getMessage());
} finally  {
    // В любом другом случаи записываем в файл определенную информацию.
    file_put_contents('user22.txt', 'get user' . PHP_EOL, FILE_APPEND);
}
// Выводим информацию о нужном пользователе.
print_r($user);

// Формируем данные с пользователем для записи в БД.
$data = [
    'name' => 'Ivangus',
    'email' => 'ivn@mail.ru',
    'phone' => '79404443301',
    // Обрабатываем пароль через функцию password_hash.
    'password' => password_hash('12345j', PASSWORD_DEFAULT),
    'is_active' => 1,
];

// Создаем переменную содержащую айди последнего созданного пользователя.
// Функция принимает информацию о БД и данные о пользователе в виде параметров.
$lastId = saveUser($connectionDB, $data);

// Выводим айди последнего созданного пользователя.
print_r($lastId);

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
