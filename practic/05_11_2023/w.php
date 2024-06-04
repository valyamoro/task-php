<?php

$file_contents = file_get_contents('play_word.txt');

$letter = 'з';
$word = mb_str_split('званая');


foreach ($word as $key => $value) {
    if ($value == $letter) {
        $playWord[$key] = $value;
        $file_contents = str_replace($key, $value, $file_contents);
        file_put_contents('play_word.txt', $file_contents);
    }
}

echo $file_contents;
