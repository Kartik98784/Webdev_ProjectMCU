<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Marvel Character</title>
</head>
<body>
    <h1>Add New Marvel Character</h1>
    <form action="create_character.php" method="post">
        <label for="name">Character Name:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="alias">Alias:</label>
        <input type="text" id="alias" name="alias"><br>

        <label for="powers">Powers:</label>
        <textarea id="powers" name="powers" required></textarea><br>

        <label for="affiliations">Affiliations:</label>
        <input type="text" id="affiliations" name="affiliations"><br>

        <label for="backstory">Backstory:</label>
        <textarea id="backstory" name="backstory"></textarea><br>

        <button type="submit">Add Character</button>
    </form>
</body>
</html>
