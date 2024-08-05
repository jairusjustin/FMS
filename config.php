<?php

// Attempt to connect to the database
$mysqli = new mysqli('localhost', 'id22113914_root', 'Test@123', 'id22113914_fms');

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


?>
