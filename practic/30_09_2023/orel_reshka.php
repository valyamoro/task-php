<?php

$probability = rand(0, 1000);

$sideCoin = match(true) {
    $probability > 500 => 'Орел',
    $probability < 500 => 'Решка',
    default => 'Монета встала ребром',
};

$choiceCoinSide = 'Орел';

if ($choiceCoinSide != 'Орел' && $choiceCoinSide != 'Решка') {
    echo 'Пожалуйста, выберите правильную сторону монеты.';
} elseif ($choiceCoinSide == $sideCoin) {
    echo 'Вы выиграли.';
} else {
    echo 'Вы проиграли.';
}
