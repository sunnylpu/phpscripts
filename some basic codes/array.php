<?php
echo "welcome to the associative array";
$sername=array('sunny'=>'tyagi',
                'nikhil'=>'mishra',
                'aastha'=>'muskan');

                echo "<br>";
echo "the name and sername are as follow<br>";
foreach($sername as $key => $value){
    
    echo "name $key => $value <br>";
}
echo "<br>";
$sunny=[23,44,67,89];
    for($i=0;$i<count($sunny);$i++){
        echo $sunny[$i]. "<br>";
    }
    echo "<br>";
    echo "<br>";


    $tyagi=[23,45,6,6,787,4];
    foreach ($tyagi as $s) {
        echo $s;
        echo "<br>";
    }
    
?>