<?php


$items = ['current_word.txt', 'attempts.txt', 'play_word.txt'];
foreach ($items as $item) {
    if (empty(\file($item))) {
        \fclose(\fopen($item, 'a'));
        \header('Location: index.php');
    }
}

function getAllWords(): array
{
    $url = 'https://sanstv.ru/randomWord/lang-ru/strong-0/count-100/word-%3F%3F%3F%3F%3F%3F';

    $ch = \curl_init($url);
    \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = \curl_exec($ch);

    \curl_close($ch);

    \preg_match_all('/\b[а-яё]{6}\b/u', $response, $matches);

    $result = \array_unique($matches[0]);

    return \array_slice($result, 0, 100);
}

function writeFile(string $content, string $filePath, string $mode = 'a+'): bool
{
    $handler = \fopen($filePath, $mode);

    return (bool)\fwrite($handler, $content . "\n");
}

function readingFile(string $filePath, string $mode = 'r'): string
{
    $handler = \fopen($filePath, $mode);

    return \fread($handler, filesize($filePath));
}

$attempts = \file('attempts.txt')[0];

if ($_POST['action'] === '1') {
    $currentWord = \readingFile('current_word.txt');

    $currentWord = \mb_str_split($currentWord);

    $letter = $_POST['letter'];
    $letter = \mb_strtolower($letter, 'utf8');

    $playWord = readingFile('play_word.txt');

    foreach ($currentWord as $key => $value) {
        if ($value === $letter) {
            $playWord = \str_replace($key, $value, $playWord);
            writeFile($playWord, 'play_word.txt', 'c');
        }
    }
    writeFile(--$attempts, 'attempts.txt', 'w');
}

if ($_POST['action'] === '0' || $attempts <= 0) {
    $words = \count(getAllWords()) > 99 ? getAllWords() : null;

    $currentWord = $words[\mt_rand(0, 99)] ?? $_SESSION['errors'] = 'Попробуйте снова!';

    if (empty($_SESSION['errors'])) {
        writeFile($currentWord, 'current_word.txt', 'w');

        writeFile('12', 'attempts.txt', 'w');

        writeFile('012345', 'play_word.txt', 'w');

    }
}

$playWord = trim(readingFile('play_word.txt'));
$currentWord = trim(readingFile('current_word.txt'));

$isWin = $playWord === $currentWord;

$showWord = readingFile('play_word.txt');
$showWord = \mb_str_split($showWord);

?>

<?php if(!empty($_SESSION['errors'])): ?>
    <?php echo '<p class="msg"> ' . nl2br($_SESSION['errors']) . ' </p>'; ?>
    <?php unset($_SESSION['errors']); ?>
<?php endif; ?>

<?php

if ($attempts <= 0 || $isWin) {
    ?>
    <?php if ($isWin) : ?>
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
            <label for="letter" class="form-label">Пожалуйста, введите букву:
                <input type="text" name="letter" class="form-control">
            </label>
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
