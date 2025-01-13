<?php
$host = 'localhost';
$dbname = 'employee_db';
$user = 'root';
$pass = 'root';


$mysqli = new mysqli($host, $user, $pass, $dbname);


if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}


if (!$mysqli->set_charset("utf8mb4")) {
    die("Error loading character set utf8mb4: " . $mysqli->error);
}
?>

