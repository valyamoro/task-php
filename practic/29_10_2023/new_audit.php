<?php

function getLastFile(string $directory): string
{
    $result = \scandir($directory, SCANDIR_SORT_DESCENDING);
    return $result[0];
}

function writeAllText(string $content, string $filePath, string $mode = 'a'): bool
{
    $handler = \fopen($filePath, $mode);
    return (bool)\fwrite($handler, $content . "\n");
}

function auditManager(string $content, string $folder): ?string
{
    $currentFile = getLastFile($folder);

    if ($currentFile === '..') {
        $firstFile = $folder . '/audit_1.txt';
        return writeAllText($content, $firstFile, 'a') ? $content : null;
    }

    $filePath = $folder . '/' . $currentFile;
    $isWriteFile = \count(\file($filePath)) < 3;

    if ($isWriteFile) {
        return writeAllText($content, $filePath, 'a') ? $content : null;
    }

    \preg_match("/\d+/", $currentFile, $match);
    $filePath = $folder . '/audit_' . ++$match[0] . '.txt';

    return writeAllText($content, $filePath, 'a') ? $content : null;
}

auditManager('ivan;21303213', 'audit');
