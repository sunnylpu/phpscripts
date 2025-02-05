<?php
$subject="hello world!";
$pattern="/world/";
if(preg_match($pattern,$subject)){
    echo "patter found ";
}
?>