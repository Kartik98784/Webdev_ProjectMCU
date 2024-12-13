<?php
session_start();
require 'db_connect.php';

// Check if character ID is provided
if (!isset($_GET['id'])) {
    echo "Character ID not provided.";
    exit;
}

$character_id = $_GET['id'];

// Fetch character data from the database
$sql = "SELECT * FROM characters WHERE character_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $character_id]);
$character = $stmt->fetch(PDO::FETCH_ASSOC);

// If character does not exist
if (!$character) {
    echo "Character not found.";
    exit;
}

// Handle comment submission
$error_message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $user_id = $_SESSION['user_id']; // Assuming you store user_id in session
    $comment = trim($_POST['comment']);
    $user_captcha = $_POST['captcha'];

    // Validate CAPTCHA
    if ($_SESSION['captcha'] !== $user_captcha) {
        $error_message = "Invalid CAPTCHA. Please try again.";
    } else {
        if (!empty($comment)) {
            // Insert comment into the database
            $sql = "INSERT INTO comments (character_id, user_id, comment) VALUES (:character_id, :user_id, :comment)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['character_id' => $character_id, 'user_id' => $user_id, 'comment' => $comment]);

            // Clear comment input on success
            $_POST['comment'] = "";
        }
    }
}

// Fetch comments for the character
$sql = "SELECT comments.id, comments.comment, users.username 
        FROM comments 
        JOIN users ON comments.user_id = users.id
        WHERE comments.character_id = :id AND comments.status = 'visible'";  // Filter by 'visible' comments
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $character_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);


$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Handle comment deletion
if (isset($_GET['delete_comment_id']) && $is_admin) {
    $delete_comment_id = $_GET['delete_comment_id'];
    
    // Prepare the DELETE SQL statement
    $sql = "DELETE FROM comments WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $delete_comment_id]);

    // Optionally, you can redirect after deletion
    header('Location: view_character.php?id=' . $character_id); // Redirect to prevent resubmission
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Character</title>
    <style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 80%;
    max-width: 600px;
    display: flex;
    flex-direction: column;
    height: 100%;
}

h2 {
    text-align: center;
    color: #333;
}

.character-details p {
    font-size: 16px;
    margin-bottom: 10px;
}

.character-details strong {
    color: #007bff;
}

a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    text-align: center;
    border-radius: 4px;
}

a:hover {
    background-color: #0056b3;
}

.comments-section {
    margin-top: 30px;
    flex-grow: 1; /* Allows the comments section to grow and take available space */
    overflow-y: auto; /* Makes the comments section scrollable if it exceeds the available space */
    max-height: 300px; /* Sets a maximum height for the comments section */
}

.comment {
    padding: 10px;
    background-color: #f1f1f1;
    margin-bottom: 15px;
    border-radius: 4px;
}

.comment strong {
    color: #007bff;
}

.comment-form textarea {
    width: 80%;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
    margin-bottom: 10px;
    font-size: 16px;
}

.comment-form button {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
}

.comment-form button:hover {
    background-color: #218838;
}

.captcha {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.captcha img {
    margin-left: 10px;
    border: 1px solid #ccc;
}

.character-image {
    width: 100%;
    max-width: 300px;
    margin: 20px 0;
    border-radius: 8px;
    display: block;
    margin-left: auto;
    margin-right: auto;
}

.error {
    color: red;
    font-size: 14px;
}
</style>
</head>
<body>

    <div class="container">
        <h2>Character Details</h2>
        <div class="character-details">
            <?php if (!empty($character['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($character['image_path']); ?>" alt="Character Image" class="character-image">
            <?php endif; ?>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($character['name']); ?></p>
            <p><strong>Alias:</strong> <?php echo htmlspecialchars(!empty($character['alias']) ? $character['alias'] : 'N/A'); ?></p>
            <p><strong>Powers:</strong> <?php echo htmlspecialchars($character['powers']); ?></p>
            <p><strong>Affiliations:</strong> <?php echo htmlspecialchars($character['affiliations']); ?></p>
            <p><strong>Backstory:</strong> <?php echo nl2br(htmlspecialchars($character['backstory'])); ?></p>
        </div>
        <a href="list_characters.php">Back to Character List</a>

        <!-- Comments Section -->
        <div class="comments-section">
    <h3>Comments</h3>
    <?php if ($comments): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>

                <?php if ($is_admin): ?>
                    <a href="?id=<?php echo $character_id; ?>&delete_comment_id=<?php echo $comment['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>
</div>


            <form method="post" class="comment-form">
                <?php if ($error_message): ?>
                    <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
                <textarea name="comment" placeholder="Add a comment..." rows="4" column="2" required><?php echo htmlspecialchars($_POST['comment'] ?? ''); ?></textarea>
                <div class="captcha">
                    <input type="text" name="captcha" placeholder="Enter CAPTCHA" required>
                    <img src="generate_captcha.php" alt="CAPTCHA">
                </div>
                <button type="submit">Submit Comment</button>
            </form>
        </div>
    </div>

</body>
</html>
