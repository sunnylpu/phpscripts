 <?php

function sunny()  {
    echo "this is testing for the function";
}
sunny();
?> 

 <?php
function greet($name="guest")  {
    echo " hello $name";
}

greet("sunny");
echo "<br>";
greet();
?> 

<?php
function counter() {
    static $count=0;
    $count++;
    echo "$count";
}
counter();
counter();
counter();
?>

<?php
$fruits = array("Apple", "Banana", "Cherry");
echo $fruits[0]; // Outputs: Apple
$fruits[]="orange";
print_r($fruits);
?>


<?php
$text = <<<EOD
This is a
multiline
string.
EOD;

echo $text;

// 2. Advanced Associative Arrays
$ages = ['John' => 25, 'Mary' => 30, 'Peter' => 22];
// Sorting associative arrays by keys and values
ksort($ages);
print_r($ages);

arsort($ages);
print_r($ages);
?>
