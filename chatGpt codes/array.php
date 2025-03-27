<?php
// array create 
$arr=range(2,10);
print_r($arr);
//array add using push
array_push($arr,12);
echo "<br>";
print_r($arr);
echo "<br>";
array_pop($arr); // remove the element
print_r($arr);
echo "<br>";
array_unshift($arr,12); //add the element at first position
print_r($arr);
// for printing the entire array for key and value both and seperate
foreach ($arr as $value) {
    echo "<br>";
    echo $value;
    echo " ";
}
// array searching 
echo "<br>";
if(in_array(12,$arr)){
    echo "found";
}
echo "<br>";
//sorting
sort($arr);
foreach ($arr as  $value) {
    echo "$value";
    echo "<br>";
}
echo "<br>";
rsort($arr);
echo "<br>";

// array filter and map
$even_numbers=array_filter($arr)
?>