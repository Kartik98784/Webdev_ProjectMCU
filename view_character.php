<?php
session_start();
require 'db_connect.php';
require 'header.php';

// Check if character ID is provided
if (!isset($_GET['id'])) {
    echo "Character ID not provided.";
    exit;
}

$character_id = $_GET['id'];

// Fetch character data from the database
$sql = "SELECT * FROM Characters WHERE character_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $character_id]);
$character = $stmt->fetch(PDO::FETCH_ASSOC);

// If character does not exist
if (!$character) {
    echo "Character not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Character</title>
</head>
<body>

    <h2>Character Details</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($character['name']); ?></p>
    <p><strong>Alias:</strong> <?php echo htmlspecialchars(!empty($character['alias']) ? $character['alias'] : 'N/A'); ?></p>
    <p><strong>Powers:</strong> <?php echo htmlspecialchars($character['powers']); ?></p>
    <p><strong>Affiliations:</strong> <?php echo htmlspecialchars($character['affiliations']); ?></p>
    <p><strong>Backstory:</strong> <?php echo htmlspecialchars($character['backstory']); ?></p>

    <a href="list_characters.php">Back to Character List</a>

</body>
</html>
