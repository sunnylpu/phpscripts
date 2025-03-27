<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $N = $_POST["num"];
        echo "<h3>Prime numbers between 1 and $N are:</h3>";
        for ($i = 2; $i <= $N; $i++) {
            for ($j = 2; $j * $j <= $i; $j++) {
                if ($i % $j == 0) {
                    continue 2;
                }
            }
            echo $i . " ";
        }
    }
    