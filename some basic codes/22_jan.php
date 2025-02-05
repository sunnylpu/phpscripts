<?php
$num1=readline("enter the first number");
$num2=readline("enter the second number");
$op=readline("enter the operation + - * /");
switch ($op) {
    case "+":
        
        echo $num1+$num2;
        break;
    

    case "-":
        echo $num1-$num2;
        break;
         
        
    case "*":
        echo $num1 * $num2;
        break;
            
    case "/":
        echo $num1/$num2;
        break; 
        
    case "%":
        echo $num1%$num2;
        break;    
    default:
        echo "select the valid operator ";
        break;
}
?>