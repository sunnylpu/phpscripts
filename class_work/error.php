<?php

function customError($errno, $errstr, $errfile, $errline) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    echo "Error on line $errline in file $errfile<br>";

    error_log("Error: [$errno] $errstr in $errfile on line $errline", 3, "errors.log");
}


set_error_handler("customError");

$file = fopen("example.txt", "w");
if (!$file) {
    trigger_error("Failed to open file in write mode", E_USER_WARNING);
} else {
    fwrite($file, "This is a test content.\n");
    fclose($file);
}

if (isset($file) && is_resource($file)) {
    fclose($file);
}

echo $undefinedVariable;
?>