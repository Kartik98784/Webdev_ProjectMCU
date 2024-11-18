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

// Handle form submission to update character
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize the input
    $name = trim($_POST['name']);
    $alias = trim($_POST['alias']);

    // Ensure name is not empty
    if (empty($name)) {
        echo "Character name is required.";
        exit;
    }

    // Update character in the database
    $update_sql = "UPDATE Characters SET name = :name, alias = :alias WHERE character_id = :id";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute(['name' => $name, 'alias' => $alias, 'id' => $character_id]);

    // Redirect to character list
    header('Location: list_characters.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Character</title>
</head>
<body>

    <h2>Edit Character</h2>
    <form method="POST">
        <label for="name">Character Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($character['name']); ?>" required><br><br>

        <label for="alias">Character Alias:</label><br>
        <input type="text" id="alias" name="alias" value="<?php echo htmlspecialchars($character['alias']); ?>"><br><br>

        <button type="submit">Save Changes</button>
    </form>

    <br>
    <a href="list_characters.php">Back to Character List</a>

</body>
</html>
