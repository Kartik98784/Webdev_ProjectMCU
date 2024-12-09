<?php
// Start session and include database connection
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch form data
    $name = trim($_POST['name']);
    $alias = trim($_POST['alias']);
    $powers = trim($_POST['powers']);
    $affiliations = trim($_POST['affiliations']);
    $backstory = trim($_POST['backstory']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    // Handle file upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $uploadDir = 'uploads/';
        
        // Ensure the upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $_FILES['image']['name'];
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid('character_', true) . '.' . $fileExtension;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destination)) {
                $imagePath = $destination;
            } else {
                echo "Error moving the uploaded file.";
                exit;
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            exit;
        }
    }

    // Validate required fields
    if (empty($name) || empty($powers)) {
        echo "Name and Powers are required fields.";
        exit;
    }

    try {
        // Insert data into the database
        $sql = "INSERT INTO characters (name, alias, powers, affiliations, backstory, description, image_path, created_at, updated_at) 
                VALUES (:name, :alias, :powers, :affiliations, :backstory, :description, :image_path, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'alias' => $alias,
            'powers' => $powers,
            'affiliations' => $affiliations,
            'backstory' => $backstory,
            'description' => $description,
            'image_path' => $imagePath
        ]);

        // Redirect to character list after successful insertion
        header('Location: list_characters.php');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Marvel Character</title>
    <style>
/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f8fc;
    color: #333;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

h1 {
    color: #0366d6;
    text-align: center;
    margin-bottom: 30px;
}

/* Form Container */
form {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px 30px;
    max-width: 500px;
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #0366d6;
    align-self: flex-start;
}

input, textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccd7e0;
    border-radius: 4px;
    font-size: 14px;
    color: #333;
    box-sizing: border-box;
}

input:focus, textarea:focus {
    border-color: #0366d6;
    outline: none;
    box-shadow: 0 0 4px rgba(3, 102, 214, 0.5);
}

button {
    background-color: #0366d6;
    color: #ffffff;
    border: none;
    padding: 10px 15px;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
}

button:hover {
    background-color: #024ea3;
}
    </style>
</head>
<body>
    <form action="add_character.php" method="post" enctype="multipart/form-data">
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

        <label for="image">Character Image:</label>
        <input type="file" id="image" name="image" accept="image/*"><br>

        <button type="submit">Add Character</button>
    </form>
</body>
</html>
