<?php

// verify the serveer request method

if($_SERVER["REQUEST_METHOD"] == "POST");
            // get the value fo num1 and num2;
$num1=$_POST["num1"];
$num2=$_POST["num2"];
if(isset($_POST["add"])){
    $result = $num1 + $num2;
    echo "The result of addition is $result";
}

if(isset($_POST["sub"])){
    $result = $num1 - $num2;
    echo "The result of subtraction is $result";
}

if(isset($_POST["mul"])){
    $result = $num1 * $num2;
    echo "The result of multiplication is $result";
}
if(isset($_POST["percent"])){
    $result = $num1 % $num2;
    echo "The result of multiplication is $result";
}
if(isset($_POST["power"])){
    $result = $num1 ** $num2;
    echo "The result of multiplication is $result";
}

if(isset($_POST["div"])){
    if($num2 != 0){
        $result = $num1 / $num2;
        echo "The result of division is $result";
    } else {
        echo "Division by zero is not allowed";
    }
}




























// echo $_SERVER['PHP_SELF'];

// echo "<br>";
// echo $_SERVER['PHP_SELF'];
// echo "<br>";echo "<br>";
// echo $_SERVER['SERVER_NAME'];
// echo "<br>";echo "<br>";
// echo $_SERVER['HTTP_HOST'];
// echo "<br>";echo "<br>";
// echo $_SERVER['HTTP_REFERER'];
// echo "<br>";echo "<br>";
// echo $_SERVER['HTTP_USER_AGENT'];
// echo "<br>";echo "<br>";
// echo $_SERVER['SCRIPT_NAME'];
?>