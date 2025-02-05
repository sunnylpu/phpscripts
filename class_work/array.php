<?php
$fruits = array("Apple", "Banana", "Cherry", "Date", "Elderberry");
print_r($fruits);
foreach ($fruits as $fruit) {
    echo $fruit . "\n";
    echo "<br>";
}
$reversed_fruits = array_reverse($fruits);
print_r($reversed_fruits);
echo "<br>";
echo "<br>";
echo "the min is".min($fruits);
echo "<br>";
echo "the max is ".max($fruits);

echo "<br>";echo "<br>";
if(in_array("Apple", $fruits)){
    echo "the fruit is present";
}
?>