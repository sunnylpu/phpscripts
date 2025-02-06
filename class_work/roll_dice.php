<?php
echo "<h1>roll a dice</h1>";
echo "<br>";
$score = 0;

while(true){
    $x=rand(1,6); 
echo "the person rolled a die and he got $x";
if($x==6){
    echo "the game is over";
    break;
}
elseif($x%2==0){
    echo "the score is ".$score+=0;
    echo "<br>";
}
elseif($x%2!=0){
    echo "the score is ".$score+=10;
    echo "<br>";
}
}


?>