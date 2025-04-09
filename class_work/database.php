<?php
$servername="localhost";
$username="root";
$password="";
$connection=mysqli_connect($servername,$username,$password);
$query="CREATE DATABASE groceryshop";
$run=mysqli_query($connection,$query);
if($run){
    echo "connection on the server is complete";

}
else{
    echo "not connnected to the server";
}
?>