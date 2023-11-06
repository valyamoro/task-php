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



