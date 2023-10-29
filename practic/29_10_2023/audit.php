<?php
declare(strict_types=1);
error_reporting(-1);

// Данные пользователя.
$user = [
    'username' => 'ivan',
    'date' => date('Y-m-d'),
];

// Привожу данные к нужному формату.
$user = implode(';', $user);

for ($i = 1; true; $i++) {
    $fileAudit = "audit_{$i}.txt";
    if (!file_exists($fileAudit)) {
        $i--;
        $fileAudit = "audit_{$i}.txt";
        break;
    }
}

count(file($fileAudit)) < 3 ? $isWriteFile = true : $isWriteFile = false;

if (!$isWriteFile) {
    $auditId = substr($fileAudit, 6, 1);
    $auditId++;
    $fileAudit = "audit_{$auditId}.txt";
    file_put_contents($fileAudit, $user . PHP_EOL);
} else {
    $handler = fopen($fileAudit, 'a+b');
    fwrite($handler, $user . PHP_EOL);
}