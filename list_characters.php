<?php
session_start();
require 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch characters from the database
$sql = "SELECT * FROM Characters";
$stmt = $pdo->query($sql);
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the user is an admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Characters</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
            margin: 0;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        ul {
            list-style-type: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        li {
            width: 300px;
            margin: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .character-info {
            font-size: 18px;
            color: #333;
        }
        .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        .buttons a, .buttons button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .buttons a:hover, .buttons button:hover {
            background-color: #0056b3;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #f44336;
            padding: 10px 20px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
        }
        .logout-btn:hover {
            background-color: #d32f2f;
        }
        .add-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #28a745;
            padding: 10px 20px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
        }
        .add-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <!-- Logout button positioned top right -->
    <a href="?logout=true" class="logout-btn">Logout</a>
    
    <!-- Add New Character button positioned top left (visible only to admin) -->
    <?php if ($is_admin): ?>
        <a href="create_character.php" class="add-btn">Add New Character</a>
    <?php endif; ?>

    <h2>Marvel Characters</h2>
    
    <!-- Display character list in card format -->
    <ul>
    <?php foreach ($characters as $character): ?>
        
        <li>
            <div class="character-info">
                <strong><?php echo htmlspecialchars($character['name']); ?></strong><br>
                Alias: <?php echo htmlspecialchars(!empty($character['alias']) ? $character['alias'] : 'N/A'); ?>
            </div>
            <div class="buttons">
                <!-- View button -->
                <a href="view_character.php?id=<?php echo $character['character_id']; ?>">View</a>

                <!-- Edit and Delete buttons visible only to admin -->
                <?php if ($is_admin): ?>
                    <a href="edit_character.php?id=<?php echo $character['character_id']; ?>">Edit</a>
                    <a href="delete_character.php?id=<?php echo $character['character_id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this character?')">Delete</a>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>


</body>
</html>
