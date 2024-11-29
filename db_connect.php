<?php
/*******w******** 

    Name: Kartik Agnihotri
    Date: September 18, 2024
    Description: This script establishes a connection to the 
    database using PDO (PHP Data Objects). It defines the 
    database credentials and attempts to connect to the MySQL 
    database. If the connection fails, an error message is displayed.

****************/

define('DB_DSN', 'mysql:host=localhost;dbname=marvel_cms;charset=utf8');
define('DB_USER', 'root');
define('DB_PASS', '');

// PDO (PHP Data Objects) is used here for secure database interactions.
try {
    // Attempt to create a new PDO connection to MySQL.
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
} catch (PDOException $e) {
    print "Error: " . $e->getMessage();
    die(); // Stop execution on errors.
    // In production, handle this situation more gracefully.
}
?>
