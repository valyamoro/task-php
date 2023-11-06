<?php
\session_start();
// Получение 100 слов с сайта - 24 минуты. 30 + 30 +
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

$needWord = file('current_word.txt');

$attempts = mb_strlen($needWord[0], 'utf8') * 2;
if (empty(file('attempts.txt'))) {
    $handler = fopen('attempts.txt', 'a');
    fwrite($handler, $attempts);
}


$needWord = mb_str_split($needWord[0]);

$playWord = array_fill(0, 6, null);

$letter = $_POST['letter'];
$letter = mb_strtolower($letter, 'utf8');

$handler = fopen("play_word.txt", "c");
if (!empty(file('play_word.txt'))) {
    $cells = str_repeat('_', count($playWord));
    fwrite($handler, $cells);
}

foreach ($needWord as $key => $value) {
    if ($value == $letter) {
        $playWord[$key] = $value;
        fseek($handler, 1);
        fwrite($handler, $value);
    }
}


print_r($playWord);

$_SESSION['play_word'] = $playWord;

foreach ($playWord as $value) {
    echo $value;
}

$attempts = file('attempts.txt')[0];
$attempts = $attempts - 1;

$handler = fopen('attempts.txt', 'w');
fwrite($handler, $attempts);



//header('Location: form.php');


//print_r($needWord);
//print_r($playWord);
//print_r($_SESSION['fail']);


$array = ['id' => 1, 'name' => 'ВАНЯЯ'];

$data = json_encode($array, JSON_UNESCAPED_UNICODE);

print_r($data);
$data = json_decode($data);
echo $data->name;