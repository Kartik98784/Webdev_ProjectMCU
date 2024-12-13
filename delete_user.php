<?php
session_start();
require 'db_connect.php';
require 'auth_admin.php'; // Ensure only admins can access

// Check if user ID is provided
if (!isset($_GET['id'])) {
    echo "User ID not provided.";
    exit;
}

$user_id = $_GET['id'];

// Delete the user from the database
$sql = "DELETE FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);

header("Location: view_users.php"); // Redirect after successful deletion
exit;
?>
