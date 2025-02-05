<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $age = htmlspecialchars($_POST['age']);
    $gender = isset($_POST['male']) ? 'male' : (isset($_POST['female']) ? 'female' : 'other');

    if ($age >= 18) {
        echo "<h1>Hello $name, you are eligible to vote.</h1>";
    } else {
        echo "Hello $name, you are not eligible to vote.";
    }
}
?>