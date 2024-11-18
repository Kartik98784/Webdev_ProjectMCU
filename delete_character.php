<?php
session_start();
require 'db_connect.php';

// Redirect to login if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Check if character ID is provided
if (!isset($_GET['id'])) {
    echo "Character ID not provided.";
    exit;
}

$character_id = $_GET['id'];

// Delete character from the database
$delete_sql = "DELETE FROM Characters WHERE character_id = :id";
$delete_stmt = $pdo->prepare($delete_sql);
$delete_stmt->execute(['id' => $character_id]);

// Redirect to character list after deletion
header('Location: list_characters.php');
exit;
?>
