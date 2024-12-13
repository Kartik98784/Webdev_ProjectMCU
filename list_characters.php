<?php
session_start();
require 'db_connect.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch sort and search parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name'; // Default sort by name
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build SQL query dynamically based on sort and search
$sql = "SELECT * FROM Characters";
if (!empty($search)) {
    $sql .= " WHERE name LIKE :search OR alias LIKE :search";
}
switch ($sort) {
    case 'created_at':
        $sql .= " ORDER BY created_at";
        break;
    case 'updated_at':
        $sql .= " ORDER BY updated_at";
        break;
    case 'name':
    default:
        $sql .= " ORDER BY name";
}

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
}
$stmt->execute();
$characters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the user is an admin
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle global comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $name = !empty($_POST['name']) ? $_POST['name'] : 'Anonymous';
    $comment = $_POST['comment'];

    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO comments (character_id, name, comment) VALUES (NULL, :name, :comment)");
        $stmt->execute(['name' => $name, 'comment' => $comment]);
    }
}

// Fetch all comments
$comments_stmt = $pdo->prepare("SELECT * FROM comments ORDER BY created_at DESC");
$comments_stmt->execute();
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
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
            margin: 0;
            padding: 0;
        }
        .top-bar {
            background-color: #007bff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            height: 60px;
            position: relative;
        }
        .top-bar .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-align: center;
        }
        .top-bar .logout-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .top-bar .logout-btn:hover {
            background-color: #d32f2f;
        }
        .sort-bar {
            background-color: #e9ecef;
            padding: 10px;
            display: flex;
            justify-content: flex-end;
        }
        .sort-bar select {
            padding: 5px;
            font-size: 16px;
        }
        .search-bar {
            background-color: #e9ecef;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .search-bar input {
            width: 300px;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .character-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
            list-style-type: none ;
        }
        .character-item {
            width: 300px;
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }
        .character-item .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        .character-item .buttons a,
        .character-item .buttons button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .character-item .buttons a:hover,
        .character-item .buttons button:hover {
            background-color: #0056b3;
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
        .add-user-btn {
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

        .character-item {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #fff;
        }

        form {
            margin-top: 15px;
        }

        form textarea, form input[type="text"] {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        form button:hover {
            background-color: #0056b3;
        }

        .comments {
            margin-top: 20px;
        }

        .comment-item {
            background: #f4f4f9;
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Top Blue Bar -->
    <div class="top-bar">
        <h1 class="logo">Marvel Characters</h1>
        <a href="?logout=true" class="logout-btn">Logout</a>
    </div>

    <?php if ($is_admin): ?>
    <a href="add_character.php" class="add-btn">Add New Character</a>
<?php endif; ?>

    <!-- Search and Sort Bar -->
    <div class="search-sort-bar">
    <a href="view_users.php" class="add-user-btn">View Users</a>

        <form action="list_characters.php" method="get" class="search-bar">
            <input 
                type="text" 
                name="search" 
                placeholder="Search Characters..." 
                value="<?php echo htmlspecialchars($search); ?>"> 
            <button type="submit">Search</button>
        </form>
        <div class="sort-bar">
            <label for="sort">Sort by:</label>
            <select id="sort" name="sort" onchange="location = this.value;">
                <option value="?sort=name&search=<?php echo urlencode($search); ?>" <?php if ($sort == 'name') echo 'selected'; ?>>Name</option>
                <option value="?sort=created_at&search=<?php echo urlencode($search); ?>" <?php if ($sort == 'created_at') echo 'selected'; ?>>Created Date</option>
                <option value="?sort=updated_at&search=<?php echo urlencode($search); ?>" <?php if ($sort == 'updated_at') echo 'selected'; ?>>Updated Date</option>
            </select>
        </div>
    </div>

    <!-- Display Characters -->
    <ul class="character-list">
        <?php foreach ($characters as $character): ?>
            <li class="character-item">
                <h2><?php echo htmlspecialchars($character['name']); ?></h2>
                <p>Alias: <?php echo htmlspecialchars($character['alias'] ?: 'N/A'); ?></p>
                <div class="buttons">
                    <a href="view_character.php?id=<?php echo $character['character_id']; ?>">View</a>
                    <?php if ($is_admin): ?>
                        <a href="edit_character.php?id=<?php echo $character['character_id']; ?>">Edit</a>
                        <a href="delete_character.php?id=<?php echo $character['character_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (empty($characters)): ?>
        <p>No characters found. Try adjusting your search or sorting options.</p>
    <?php endif; ?>
</body>
</html>
