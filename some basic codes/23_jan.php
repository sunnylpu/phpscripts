<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sub1 = $_POST["sub1"];
    $sub2 = $_POST["sub2"];
    $sub3 = $_POST["sub3"];
    $sub4 = $_POST["sub4"];
    $sub5 = $_POST["sub5"];

    // Calculate percentage
    $result = (($sub1 + $sub2 + $sub3 + $sub4 + $sub5) / 500) * 100;

    // Determine grade
    if ($result >= 90 && $result <= 100) {
        $grade = 'O';
    } elseif ($result >= 80 && $result < 90) {
        $grade = 'A+';
    } elseif ($result >= 70 && $result < 80) {
        $grade = 'A';
    } elseif ($result >= 60 && $result < 70) {
        $grade = 'B';
    } elseif ($result >= 50 && $result < 60) {
        $grade = 'C';
    } elseif ($result >= 40 && $result < 50) {
        $grade = 'D';
    } else {
        $grade = '<span style="color:red;">F</span>';
    }

    // Format result to 2 decimal places
    $actual = number_format($result, 2);

    // Display percentage and grade
    echo "Percentage: $actual% <br>";
    echo "Grade: $grade <br>";
}
?>