<?php
declare(strict_types=1);
// Время выполнения: 2 часа 54 минуты.
error_reporting(-1);

function getFiles(string $folder): ?string
{
    $files = \scandir($folder, SCANDIR_SORT_ASCENDING);
    $files = \array_diff($files, ['..', '.']);

    return $files[0] ?? null;
}

function writeAllText(string $content, string $filePath, string $mode = 'a+'): bool
{
    $handler = \fopen($filePath, $mode);

    return (bool)\fwrite($handler, $content . "\n");
}

function auditManager(string $visitorName, string $timeOfDate, string $folder): ?string
{
    $content = $visitorName . ';' . $timeOfDate;

    $lastFile = getFiles($folder);

    if (is_null($lastFile)) {
        $newFile = $folder . '/audit_1.txt';
        return writeAllText($content, $newFile) ? $content : null;
    }

    $filePath = $folder . '/' . $lastFile;
    $isWriteFile = \count(\file($filePath)) < 3;

    if ($isWriteFile === true) {
        return writeAllText($content, $filePath, 'a+') ? $content : null;
    }

    preg_match("/\d+/", $lastFile, $matches);
    $currentFileIndex = $matches[0] + 1;
    $filePath = $folder . '/' . "audit_{$currentFileIndex}.txt";
    return writeAllText($content, $filePath, 'a+') ? $content : null;


}

/////////////////////////////

$userName = 'Ivan';
$timeOfDate = \date('d-m-Y H:i:s');
$folder = __DIR__ . '/audit';

$result = auditManager($userName, $timeOfDate, $folder);

print_r($result);



// Папка с файлами аудитов.
//const DIRECTORY_AUDIT = 'audit';
//
//// Данные пользователя.
//$user = [
//    'username' => 'ivan',
//    'date' => date('d-m-Y'),
//];
//
///** Получаю последний созданный файл в директории.
// * @param string $directory
// * @return string|null
// */
//function getLastCreatedFile(string $directory): ?string
//{
//    // Получаю массив всех файлов, переворачивая его.
//    $result = \scandir($directory, SCANDIR_SORT_DESCENDING);
//    // Удаляю точки.
//    $result = \array_diff($result, ['..', '.']);
//    // Возвращаю последний созданный файл.
//    return $result[0];
//}
//
///** Запись данных в файл.
// * @param string $file
// * @param string $data
// * @return void
// */
//function writeFile(string $file, string $data): void
//{
//    // Открываю файл на запись. Если не существует - создаю.
//    $handler = \fopen($file, 'a');
//    // Записываю в файл данные.
//    \fwrite($handler, $data . "\n");
//}
//
//// Привожу данные к нужному формату.
//$user = \implode(';', $user);
//
//// Получаю последний созданный файл.
//$currentFile = getLastCreatedFile(DIRECTORY_AUDIT);
//
//// Полный путь до файла с записями аудита.
//$fileAudit = null;
//
//// Если папка, хранящая аудиты пуста:
//if (\is_null($currentFile)) {
//    // Путь до первого файла с записями.
//    $fileAudit = DIRECTORY_AUDIT . '/audit_1.txt';
//    // Создаю новый файл и записываю туда пользователя.
//    writeFile($fileAudit, $user);
//} else {
//    // true, если строк меньше трех, иначе false.
//    $isWriteFile = \count(\file(DIRECTORY_AUDIT . $currentFile)) < 3;
//
//    if ($isWriteFile === true) {
//        // Путь до последнего файла.
//        $fileAudit = DIRECTORY_AUDIT . $currentFile;
//        // Записываю в последний файл данные пользователя.
//        writeFile($fileAudit, $user);
//    } else {
//        // Получаю индекс аудита.
//        preg_match("/\d+/", $currentFile, $matches);
//        // Помещаю индекс аудита в переменную.
//        $currentFileIndex = $matches[0];
//        // Увеличиваю на единицу индекс аудита.
//        $currentFileIndex++;
//        // Формирую путь до нового файла с аудитом.
//        $fileAudit = DIRECTORY_AUDIT . "/audit_{$currentFileIndex}.txt";
//        // Создаю файл с аудитом и записываю туда пользователя.
//        writeFile($fileAudit, $user);
//    }
//}
//
//// Если переменная null, то произошла ошибка.
//if (is_null($fileAudit)) {
//    echo 'Что-то пошло не так';
//}
