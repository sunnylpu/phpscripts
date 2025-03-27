<?php
// Custom error handler function
function customError($errno, $errstr, $errfile, $errline) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    echo "Error on line $errline in file $errfile<br>";
    // Optionally log the error to a file
    error_log("Error: [$errno] $errstr in $errfile on line $errline", 3, "errors.log");
}

// Set custom error handler
set_error_handler("customError");

// Trigger an error (for testing purposes)
echo $undefinedVariable; // Notice: Undefined variable
?>