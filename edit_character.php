<?php
// Start session and include database connection
require 'db_connect.php';

// Check if the user is logged in and is an admin
require 'auth_admin.php';

// Fetch character id from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid character ID.";
    exit;
}

$characterId = (int)$_GET['id'];

// Fetch the existing character data
try {
    $sql = "SELECT * FROM characters WHERE character_id = :id"; // Use the correct column name
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $characterId]);
    $character = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$character) {
        echo "Character not found.";
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch form data
    $name = trim($_POST['name']);
    $alias = trim($_POST['alias']);
    $powers = trim($_POST['powers']);
    $affiliations = trim($_POST['affiliations']);
    $backstory = trim($_POST['backstory']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    // Handle file upload
    $imagePath = $character['image_path']; // Use existing image if no new file uploaded
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
        // Update character in the database
        $sql = "UPDATE characters SET name = :name, alias = :alias, powers = :powers, affiliations = :affiliations, 
                backstory = :backstory, description = :description, image_path = :image_path, updated_at = NOW() 
                WHERE character_id = :id"; // Use the correct column name
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'alias' => $alias,
            'powers' => $powers,
            'affiliations' => $affiliations,
            'backstory' => $backstory,
            'description' => $description,
            'image_path' => $imagePath,
            'id' => $characterId
        ]);

        // Redirect to character list after successful update
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
    <title>Edit Marvel Character</title>
    <style>
        /* Base Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-top: 30px;
        }

        form {
            width: 60%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        input[type="file"] {
            padding: 5px;
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .image-preview {
            margin: 20px 0;
            text-align: center;
        }

        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
        }

        .message {
            color: #f44336;
            font-size: 16px;
            text-align: center;
        }

    </style>
</head>
<body>

<h1>Edit Marvel Character</h1>

<?php if (isset($error)) : ?>
    <div class="message"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form action="edit_character.php?id=<?php echo $characterId; ?>" method="post" enctype="multipart/form-data">
    <label for="name">Character Name:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($character['name']); ?>" required><br>

    <label for="alias">Alias:</label>
    <input type="text" id="alias" name="alias" value="<?php echo htmlspecialchars($character['alias']); ?>"><br>

    <label for="powers">Powers:</label>
    <textarea id="powers" name="powers" required><?php echo htmlspecialchars($character['powers']); ?></textarea><br>

    <label for="affiliations">Affiliations:</label>
    <input type="text" id="affiliations" name="affiliations" value="<?php echo htmlspecialchars($character['affiliations']); ?>"><br>

    <label for="backstory">Backstory:</label>
    <textarea id="backstory" name="backstory"><?php echo htmlspecialchars($character['backstory']); ?></textarea><br>

    <label for="image">Character Image:</label>
    <input type="file" id="image" name="image" accept="image/*"><br>

    <div class="image-preview">
        <?php if ($character['image_path']): ?>
            <img src="<?php echo htmlspecialchars($character['image_path']); ?>" alt="Current Character Image">
        <?php else: ?>
            <p>No image available</p>
        <?php endif; ?>
    </div>

    <button type="submit">Update Character</button>
</form>

</body>
</html>
