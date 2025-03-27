<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $file = $_FILES['file'];

    if ($file['error'] === 0) {
        $uploadDir = "uploads/";
        $uploadFile = $uploadDir . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            echo "File uploaded successfully!<br>";
            echo "File Name: " . $file['name'] . "<br>";
            echo "File Type: " . $file['type'] . "<br>";
            echo "File Size: " . ($file['size'] / 1024) . " KB";
        } else {
            echo "Error uploading the file.";
        }
    } else {
        echo "Error: " . $file['error'];
    }
}
?>