<?php

if($_SERVER["REQUEST_METHOD"]=="POST"){
    if(isset($_POST["countvow"])){
        $str=$_POST["str"];
        $count = countvow($str);
        echo "The number of vowels in the string is: $count";
    }
}

function countvow($str) : int {
    $count=0;
    $vowel=["a","e","i","o","u"];
    $strtoLower=strtolower($str);
    for($i=0;$i<strlen($strtoLower);$i++){
        if(in_array($strtoLower[$i],$vowel)){
            $count++;
        }
    }
    return $count;
}
?>



