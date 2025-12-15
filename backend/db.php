<?php
// backend/db.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sport_inventory"; // Line 6

$conn = new mysqli($host,$user,$pass,$db);
if ($conn->connect_error) {
    die("DB connection failed: ".$conn->connect_error);
}

?>