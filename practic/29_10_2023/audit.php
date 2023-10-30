<?php
declare(strict_types=1);
// Время выполнения: 2 часа 54 минуты.
error_reporting(-1);

// Папка с файлами аудитов.
const DIRECTORY_AUDIT = 'audit/';

// Данные пользователя.
$user = [
    'username' => 'ivan',
    'date' => date('d-m-Y'),
];

/** Получаю последний созданный файл в директории.
 * @param string $directory
 * @return string|null
 */
function getLastCreatedFile(string $directory): ?string
{
    // Получаю массив всех файлов, переворачивая его.
    $result = \scandir($directory, SCANDIR_SORT_DESCENDING);
    // Удаляю точки.
    $result = \array_diff($result, ['..', '.']);
    // Возвращаю последний созданный файл.
    return $result[0];
}

/** Запись данных в файл.
 * @param string $file
 * @param string $data
 * @return void
 */
function writeFile(string $file, string $data): void
{
    // Открываю файл на запись. Если не существует - создаю.
    $handler = \fopen($file, 'a');
    // Записываю в файл данные.
    \fwrite($handler, $data . "\n");
}

// Привожу данные к нужному формату.
$user = \implode(';', $user);

// Получаю последний созданный файл.
$currentFile = getLastCreatedFile(DIRECTORY_AUDIT);

// Полный путь до файла с записями аудита.
$fileAudit = null;

// Если папка, хранящая аудиты пуста:
if (\is_null($currentFile)) {
    // Путь до первого файла с записями.
    $fileAudit = DIRECTORY_AUDIT . '/audit_1.txt';
    // Создаю новый файл и записываю туда пользователя.
    writeFile($fileAudit, $user);
} else {
    // true, если строк меньше трех, иначе false.
    $isWriteFile = \count(\file(DIRECTORY_AUDIT . $currentFile)) < 3;

    if ($isWriteFile === true) {
        // Путь до последнего файла.
        $fileAudit = DIRECTORY_AUDIT . $currentFile;
        // Записываю в последний файл данные пользователя.
        writeFile($fileAudit, $user);
    } else {
        // Получаю индекс аудита.
        preg_match("/\d+/", $currentFile, $matches);
        // Помещаю индекс аудита в переменную.
        $currentFileIndex = $matches[0];
        // Увеличиваю на единицу индекс аудита.
        $currentFileIndex++;
        // Формирую путь до нового файла с аудитом.
        $fileAudit = DIRECTORY_AUDIT . "audit_{$currentFileIndex}.txt";
        // Создаю файл с аудитом и записываю туда пользователя.
        writeFile($fileAudit, $user);
    }
}

// Если переменная null, то произошла ошибка.
if (is_null($fileAudit)) {
    echo 'Что-то пошло не так';
}
