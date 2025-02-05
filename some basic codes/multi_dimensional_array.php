<?php
echo "welcome to new learining day of php<br>";
$multiarray=array(
array(1,2,3),array(4,5,6),array(7,8,9)
);
for($i=0;$i<count($multiarray);$i++){
    echo "<br>";
    for($j=0;$j<count($multiarray);$j++){
        echo $multiarray[$i][$j]." ";
    }
}
?>