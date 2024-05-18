<?php
    require_once(__DIR__ . '/../settings.php');
    // Create a connection to the MySQL server
    $conn = new mysqli($dbhost, $dbuser, $dbpassword, $dbname, $dbport);
    // Check if the connection was successful
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
	}
?>
