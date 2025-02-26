<?php
$arr=range(2,10);
print_r($arr);
array_push($arr,12);
echo "<br>";
print_r($arr);
echo "<br>";
array_pop($arr);
print_r($arr);
echo "<br>";
array_unshift($arr,12);
print_r($arr);
foreach ($arr as $value) {
    echo "<br>";
    echo $value;
    echo " ";
}
?>