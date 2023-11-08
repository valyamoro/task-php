<?php

$items = ['current_word.txt', 'attempts.txt', 'play_word.txt', 'words.txt'];
foreach ($items as $item) {
    if (empty(\file($item))) {
        \fclose(\fopen($item, 'a'));
        \header('Location: index.php');
    }
}

if (\count(\file('words.txt')) < 100) {
    $url = 'https://sanstv.ru/randomWord/lang-ru/strong-2/count-100/word-%3F%3F%3F%3F%3F%3F';

    $ch = \curl_init($url);
    \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = \curl_exec($ch);
    \curl_close($ch);

    if (!$response) {
        die('Ошибка при выполнении запроса.');
    }

    \preg_match_all('/\b[а-яё]{6}\b/u', $response, $matches);

    $words = \array_unique($matches[0]);

    $words = \array_slice($words, 0, 100);

    $words = \implode(' ' . PHP_EOL, $words);

    $handler = \fopen('words.txt', 'w');
    \fwrite($handler, $words);
}

$words = \file('words.txt');

$attempts = \file('attempts.txt')[0];

if ($_POST['action'] === '1') {
    $handler = \fopen('current_word.txt', 'r');
    $currentWord = \fread($handler, filesize('current_word.txt'));

    $currentWord = \mb_str_split($currentWord);

    $letter = $_POST['letter'];
    $letter = \mb_strtolower($letter, 'utf8');

    $handler = \fopen('play_word.txt', 'r');
    $playWord = \fread($handler, filesize('play_word.txt'));

    foreach ($currentWord as $key => $value) {
        if ($value == $letter) {
            $playWord = \str_replace($key, $value, $playWord);
            $handler = \fopen('play_word.txt', 'c');
            \fwrite($handler, $playWord);
        }
    }

    $handler = \fopen('attempts.txt', 'w');
    \fwrite($handler, --$attempts);
}

if ($_POST['action'] === '0' || $attempts <= 0) {
    $currentWord = \trim($words[mt_rand(0, 99)]);

    $handler = \fopen('current_word.txt', 'w');
    \fwrite($handler, $currentWord);

    $handler = \fopen('attempts.txt', 'w');
    \fwrite($handler, '12');

    $handler = \fopen('play_word.txt', 'w');
    \fwrite($handler, '012345');
}

$isWin = null;

if (\file_get_contents('play_word.txt') === \file_get_contents('current_word.txt')) {
    $isWin = true;
}

$showWord = \file_get_contents('play_word.txt');
$showWord = \mb_str_split($showWord);

if ($attempts <= 0 || $isWin) {
    ?>
    <?php if ($isWin) :?>
    <p>Вы выиграли!</p>
    <?php else: ?>
    <p>Вы проиграли!</p>
    <?php endif; ?>
    <form action="" method="POST">
        <button type="submit" name="action" value="0" class="btn btn-primary">Начать заново</button>
    </form>
    <?php
} else {
    ?>
    <form action="" method="POST">
        <div class="mb-3">
            <label for="letter" class="form-label">Пожалуйста, введите букву:</label>
            <input type="text" name="letter" class="form-control">
        </div>
        <button type="submit" name="action" value="1" class="btn btn-primary">Ввести букву</button>
    </form>
    <form action="" method="POST">
        <button type="submit" name="action" value="0" class="btn btn-primary">Начать заново</button>
    </form>
    <?php foreach ($showWord as $letter): ?>
    <?php echo $letter ?>
    <?php endforeach; ?>

    <?php
}










