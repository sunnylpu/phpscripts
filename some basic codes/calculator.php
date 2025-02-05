<?php
if($_SERVER["REQUEST_METHOD"]="POST"){
$sub1=$_POST["sub1"];
$sub2=$_POST["sub2"];
$sub3=$_POST["sub3"];
$sub4=$_POST["sub4"];
$sub5=$_POST["sub5"];

$result=(($num1+$num2+$num3+$num4+$num5)/500)*100;
echo " the result is : ".number_format($result,2);  //decimal upto
echo "/n";
if($result>=90 && $result<=100){
    echo "The grade is O";
}
elseif($result>=80 && $result<90){
    echo "The grade is A+";
}
elseif($result>=70 && $result<80){
    echo "The grade is A";
}
elseif($result>=60 && $result<70){
    echo "The grade is B";
}
elseif($result>=50 && $result<60){
    echo "The grade is B+";
}
elseif($result>=40 && $result<50){
    echo "The grade is C";
}
else{
    echo "you are fail";
}
}
echo '<span style="color:red;">You are fail</span>';
?>