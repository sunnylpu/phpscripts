<?php

$value1 = null;
$value2 = null;
$value3 = "Hello, PHP!";
$value4 = "This won't be used";
$value5 = null;


$firstNonNull = $value1 ?? $value2 ?? $value3 ?? $value4 ?? $value5 ?? "No non-null values found";


echo "First non-null value: " . $firstNonNull;
?>