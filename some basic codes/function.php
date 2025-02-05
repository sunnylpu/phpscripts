<?php
$arr = array(12, 34, 556, 5);
function total($arr){
    $sum = 0;
    for($i=0; $i < count($arr); $i++){
        $sum+=$arr[$i];
    }
    return $sum;
}
$ans=total($arr);
echo $ans;
?>