<?php
$arr_1=[1,2,3,4,5,6,12];
$arr_2=[3,45,67,88,12];
$merge=array_merge($arr_1,$arr_2);
foreach($merge as $i){
    echo  " $i ";
}
echo "<br>";
$int1=array_intersect($arr_1,$arr_2);
print_r($int1);
?>