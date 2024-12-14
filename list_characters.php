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
    font-family: 'Roboto', sans-serif;
    background-color: #263238; /* Dark gray-blue background */
    margin: 0;
    padding: 0;
    color: #ecf0f1; /* Light text color */
}

/* Top Bar */
.top-bar {
    background: linear-gradient(90deg, #2C3E50, #5F4B8B); /* Dark Navy to SKU Blue */
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    height: 60px;
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.top-bar .logo {
    font-size: 1.5em;
    font-weight: bold;
}

.top-bar .logout-btn {
    background-color: #e74c3c;
    color: white;
    padding: 8px 15px;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.top-bar .logout-btn:hover {
    background-color: #c0392b;
}

/* Buttons */
.add-character-btn {
    position: absolute;
    top: 10px;
    left: 390px; 
    background-color: #27ae60; 
    padding: 10px 20px;
    text-decoration: none;
    color: white;
    border-radius: 5px;
    font-size: 16px;
}

.add-character-btn:hover {
    background-color: #2ecc71;
}

.view-users-btn {
    position: absolute;
    top: 10px;
    left: 240px; 
    background-color: #27ae60; /* Updated for a consistent color scheme */
    padding: 10px 20px;
    text-decoration: none;
    color: white;
    border-radius: 5px;
    font-size: 16px;
}

.view-users-btn:hover {
    background-color: #2980b9;
}

/* Search and Sort Bar */
.search-sort-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #16a085; /* Updated bar color to teal green */
    padding: 15px 20px;
    border-bottom: 1px solid #2c3e50;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.search-bar input {
    width: 300px;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #7f8c8d;
    border-radius: 5px;
    transition: border-color 0.3s;
    background-color: #34495e; /* Matching background color */
    color: #ecf0f1;
}

.search-bar input:focus {
    border-color: #1abc9c; /* Light green focus */
    outline: none;
}

.search-bar button {
    padding: 10px 20px;
    font-size: 16px;
    background-color:rgb(102, 187, 170);
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.search-bar button:hover {
    background-color: #16a085;
}

.sort-bar select {
    padding: 8px;
    font-size: 16px;
    border: 1px solid #7f8c8d;
    border-radius: 5px;
    background-color: #34495e;
    color: #ecf0f1;
}

.sort-bar select:focus {
    border-color: #1abc9c;
    outline: none;
}

/* Character List */
.character-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    padding: 20px;
    list-style: none;
}

.character-item {
    width: 280px;
    padding: 20px;
    background: #34495e; /* Dark background for each item */
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    text-align: center;
    transition: transform 0.3s, box-shadow 0.3s;
}

.character-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.3);
}

.character-item h2 {
    font-size: 1.5em;
    margin-bottom: 10px;
    color: #ecf0f1; /* Light text for character names */
}

.character-item p {
    font-size: 1em;
    color: #bdc3c7; /* Lighter text for details */
    margin-bottom: 15px;
}

.character-item .buttons a {
    padding: 8px 15px;
    font-size: 14px;
    color: white;
    background-color: #1abc9c; /* Green button color */
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.character-item .buttons a:hover {
    background-color: #16a085;
}

/* Comments Section */
.comments {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #34495e;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.comment-item {
    background: #2c3e50;
    padding: 10px;
    border: 1px solid #7f8c8d;
    border-radius: 5px;
    margin-bottom: 10px;
}

.comment-item h4 {
    margin: 0 0 5px;
    font-size: 1.1em;
    color: #ecf0f1;
}

.comment-item p {
    margin: 0;
    font-size: 0.95em;
    color: #bdc3c7;
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
        <a href="add_character.php" class="add-character-btn">Add Character</a>
<?php endif; ?>

    <!-- Search and Sort Bar -->
    <div class="search-sort-bar">
    <?php if ($is_admin): ?>
    <a href="view_users.php" class="view-users-btn">View Users</a>
<?php endif; ?>


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
