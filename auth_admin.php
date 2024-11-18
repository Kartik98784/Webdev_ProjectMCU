<?php
session_start();

// Check if the user is an admin by verifying the session is set and is_admin is either 1 or true
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}
?>
