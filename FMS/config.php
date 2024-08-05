<?php

// Attempt to connect to the database
$mysqli = new mysqli('localhost', 'root', '', 'fms');

// Check the connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


?>
