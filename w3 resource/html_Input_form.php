<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, 
    initial-scale=1.0">
    <title>input form </title>
</head>
<body><form action="" method='POST'>
    <h3>Please input your name:</h3>
    <input  name="name" type="text" 
    placeholder="name here">
    <button name="submit" value="submit here">submit here</button>
    </form>
    <?php
    if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST['name'])){
        $name=$_POST['name'];
        echo "<h3>hello $name </h3>";
    }
    ?>
</body>
</html>