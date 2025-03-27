<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Upload my file</h1>
    <form method="POST" action=" " enctype="multipart/form-data">
        Upload my pic:
        <input ype="file" name="myfile">
        <input type="Submit" name="submit" value="Upload my pic">
    </form>
    <?php
    // <!-- print_r($_FILES["myfile"]); -
    $filename = $_FILES["myfile"]["name"];
    $filesize = $_FILES["myfile"]["size"];
    $filetype = $_FILES["myfile"]["type"];
    $filetemp = $_FILES["myfile"]["tmp_name"];
    $fileerror = $_FILES["myfile"]["error"];
    echo "file name that i have uploaded is: $filename <br>";
    echo "File size : $filesize <br>";
    echo "File type : $filetype <br>";
    echo "File temp : $filetemp <br>";  
    echo "File error : $fileerror <br>";
    ?>
</body>
</html> -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
</head>
<body>
    <h1>Upload my file</h1>
    <form method="POST" action="" enctype="multipart/form-data">
        Upload my pic:
        <input type="file" name="myfile"> 
        <input type="submit" name="submit" value="Upload my pic">
    </form>

    <?php
    if (isset($_POST["submit"])) { // Check if form is submitted
        if (isset($_FILES["myfile"]) && $_FILES["myfile"]["error"] == 0) {
            $filename = $_FILES["myfile"]["name"];
            $filesize = $_FILES["myfile"]["size"];
            $filetype = $_FILES["myfile"]["type"];
            $filetemp = $_FILES["myfile"]["tmp_name"];

            echo "File name: $filename <br>";
            echo "File size: $filesize bytes<br>";
            echo "File type: $filetype <br>";
            echo "Temporary file location: $filetemp <br>";

            // Move uploaded file to a specific directory
            $uploadDir = "uploads/"; // Ensure this directory exists with write permissions
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
            }

            if (move_uploaded_file($filetemp, $uploadDir . $filename)) {
                echo "File uploaded successfully!";
            } else {
                echo "File upload failed!";
            }
        } else {
            echo "Error: No file uploaded or file upload error!";
        }
    }
    ?>
</body>
</html>