<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "gabplant1";
if ($conn = mysqli_connect($host, $user, $pass, $db)) {
} else {
    echo "Connection failed";
}
?>