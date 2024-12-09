<?php
session_start();
require 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit;
}

// Check if comment_id is provided
if (!isset($_POST['comment_id'])) {
    echo "Comment ID not provided.";
    exit;
}

$comment_id = $_POST['comment_id'];

// Delete comment from the database
$sql = "DELETE FROM comments WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $comment_id]);

// Redirect back to the character view page
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
