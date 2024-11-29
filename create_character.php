<?php
require 'db_connect.php';
require 'header.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form input
    $name = trim($_POST['name']);
    $alias = trim($_POST['alias']);
    $powers = trim($_POST['powers']);
    $affiliations = trim($_POST['affiliations']);
    $backstory = trim($_POST['backstory']);

    // Validate input (example: name should not be empty)
    if (empty($name)) {
        $error = "Character name is required.";
    } else {
        // Prepare SQL to insert data into the database
        $sql = "INSERT INTO characters (name, alias, powers, affiliations, backstory) 
                VALUES (:name, :alias, :powers, :affiliations, :backstory)";
        $stmt = $pdo->prepare($sql);
        
        // Execute the query
        $stmt->execute([
            ':name' => $name,
            ':alias' => $alias,
            ':powers' => $powers,
            ':affiliations' => $affiliations,
            ':backstory' => $backstory
        ]);

        // Redirect to character list page
        header("Location: list_characters.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Character</title>
</head>
<body>

    <h2>Create New Character</h2>

    <?php
    // Display error message if any
    if (isset($error)) {
        echo "<p style='color:red;'>$error</p>";
    }
    ?>

    <form method="POST">
        <label for="name">Character Name:</label><br>
        <input type="text" id="name" name="name" required><br><br>

        <label for="alias">Character Alias:</label><br>
        <input type="text" id="alias" name="alias"><br><br>

        <label for="powers">Character Powers:</label><br>
        <textarea id="powers" name="powers" rows="4" cols="50"></textarea><br><br>

        <label for="affiliations">Affiliations:</label><br>
        <input type="text" id="affiliations" name="affiliations"><br><br>

        <label for="backstory">Character Backstory:</label><br>
        <textarea id="backstory" name="backstory" rows="4" cols="50"></textarea><br><br>

        <button type="submit">Create Character</button>
    </form>

    <br>
    <a href="list_characters.php">Back to Character List</a>

</body>
</html>
