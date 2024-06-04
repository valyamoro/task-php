<?php
\session_start();
// Получение 100 слов с сайта - 24 минуты. 30 + 30 + 45 + 30 + 30.
//$url = 'https://sanstv.ru/randomWord/lang-ru/strong-2/count-100/word-%3F%3F%3F%3F%3F%3F';
//
//$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//$response = curl_exec($ch);
//curl_close($ch);
//
//if ($response === false) {
//    die('Ошибка при выполнении запроса.');
//}
//
//preg_match_all('/\b[а-яё]{6}\b/u', $response, $matches);
//
//$words = array_unique($matches[0]);
//
//$words = array_slice($words, 0, 100);
//
//$words = implode(' ' . PHP_EOL, $words);

//$handler = fopen('words.txt', 'a');
//fwrite($handler, $words);
//===========================================================================

$words = file('words.txt');
$needWord = trim($words[mt_rand(0, 99)]);

if (empty(file('current_word.txt'))) {
    $handler = fopen('current_word.txt', 'a');
    fwrite($handler, $needWord);
}

$attempts = file('attempts.txt')[0];

if ($_POST['action'] == 1) {
    $needWord = file('current_word.txt');

    $needWord = mb_str_split($needWord[0]);

    $playWord = array_fill(0, 6, null);

    $letter = $_POST['letter'];
    $letter = mb_strtolower($letter, 'utf8');

    $filename = 'play_word.txt';

    $file_contents = file_get_contents('play_word.txt');

    foreach ($needWord as $key => $value) {
        if ($value == $letter) {
            $file_contents = str_replace($key, $value, $file_contents);
            file_put_contents('play_word.txt', $file_contents);
        }
    }
    $attempts = $attempts - 1;

    $handler = fopen('attempts.txt', 'w');
    fwrite($handler, $attempts);
}

if ($_POST['action'] == 0 || $attempts <= 0) {
    file_put_contents('play_word.txt', '012345');
    file_put_contents('attempts.txt', '12');
    $isContinue = true;
//    header('Location: index.php');
}

$word = file('play_word.txt')[0];
$word = mb_str_split($word);
foreach ($word as $value) {
    if (is_numeric($value)) {
        $value = '_';
    }
    $showWord[] = $value;
}

foreach ($showWord as $value) {
    if ($value == '_') {
        $isContinue = true;
    } else {
        $isContinue = false;
    }
}

var_dump($isContinue);

if ($attempts <= 0 || !$isContinue) {
    if ($isContinue) {
        echo 'Вы проиграли!';
    } else {
        echo 'Вы выиграли!';
    }
    ?>
    <form action="index.php" method="POST">
        <button type="submit" name="action" value="0" class="btn btn-primary">Начать заново</button>
    </form>
    <?php
} else {
    ?>
    <style>
        kv {
            padding: 5px;

            color:blue;
        }
    </style>
    <form action="index.php" method="POST">
        <div class="mb-3">
            <label for="letter" class="form-label">Пожалуйста, введите букву:</label>
            <input type="text" name="letter" class="form-control">
        </div>
        <button type="submit" name="action" value="1" class="btn btn-primary">Ввести букву</button>
    </form>
    <kv>
        <?php foreach ($showWord as $value) {
            echo $value;
        }?>
    </kv>
    <?php
}










