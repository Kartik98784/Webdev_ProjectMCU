<?php
session_start();
require 'db_connect.php';
require 'auth_admin.php'; // Ensure only admins can access

// Check if user ID is provided
if (!isset($_GET['id'])) {
    echo "User ID not provided.";
    exit;
}

$user_id = $_GET['id'];

// Fetch user data from the database
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If user does not exist
if (!$user) {
    echo "User not found.";
    exit;
}

// Handle form submission for editing user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Update user details in the database
    $sql = "UPDATE users SET username = :username, email = :email WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username, 'email' => $email, 'id' => $user_id]);

    header("Location: view_users.php"); // Redirect after successful update
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>
<body>
    <h2>Edit User</h2>
    <form method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>
        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>
        <button type="submit">Update User</button>
    </form>
    <a href="view_users.php">Back to User List</a>
</body>
</html>
