<?php 
$conn = mysqli_connect("localhost", "root", "", "gabplant1");
if(!$conn){
    echo "Connection error: ". mysqli_connect_error();
    
}
?>