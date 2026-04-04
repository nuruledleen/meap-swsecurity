<?php
$conn = new mysqli("localhost", "root", "", "parking_system");

if ($conn->connect_error) {
    die("Database Connection failed");
}

?>