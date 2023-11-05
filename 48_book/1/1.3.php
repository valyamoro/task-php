<?php

//$a = 1;
//
//if ($a === 1) {
//    echo 'monday';
//} elseif ($a === 2) {
//    echo 'Tuesday';
//} elseif ($a === 3) {
//    echo 'Wednesday';
//} elseif ($a === 4) {
//    echo 'Thursday';
//} elseif ($a === 5) {
//    echo 'Friday';
//} elseif ($a === 6) {
//    echo 'Saturday';
//} elseif ($a === 7) {
//    echo 'Sunday';
//} else {
//    echo 'Неверное число';
//}
//
//// -------------------------
//
//$a = 64;
//
//if ($a > 80) {
//    echo 'Здравствуйте уважаемый';
//} else {
//    echo 'успехов';
//}
// ---------------
// страны
// --------------
?>
<form>
    <input name="country" value="egypet" type="checkbox">
    <label for="html">Египет</label>
    <input name="country" value="italia" type="checkbox">
    <label for="html">Италия</label>
    <input name="country" value="turcia" type="checkbox">
    <label for="html">Греция</label>
    <button type="submit" class="btn btn-primary">Выбрать страну</button>
</form>
    <form>
        <input name="restDays" value="w" type="text">
        <label for="html">Египет</label>
        <button type="submit" class="btn btn-primary">Выбрать страну</button>
    </form>
<?php

print_r($_REQUEST);