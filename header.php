<?php

?>
<header>
    <div class="navbar">
        <a href="list_characters.php" class="logo">Marvel Vault</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <form action="list_characters.php" method="get" class="search-form">
                <input type="text" name="search" placeholder="Search characters..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
            </form>
            <a href="logout.php" class="logout-btn">Logout</a>
        <?php else: ?>
            <a href="login.php" class="login-btn">Login</a>
        <?php endif; ?>
    </div>
</header>
<style>
    header {
        background-color: #007bff;
        padding: 10px 20px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }
    .navbar .logo {
        font-size: 24px;
        font-weight: bold;
        text-decoration: none;
        color: white;
    }
    .navbar .search-form {
        display: flex;
        align-items: center;
    }
    .navbar .search-form input {
        padding: 8px;
        border: none;
        border-radius: 4px;
        margin-right: 5px;
    }
    .navbar .search-form button {
        padding: 8px 12px;
        background-color: #0056b3;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .navbar .search-form button:hover {
        background-color: #00408b;
    }
    .logout-btn, .login-btn {
        padding: 8px 12px;
        background-color: #f44336;
        color: white;
        text-decoration: none;
        border-radius: 4px;
    }
    .logout-btn:hover, .login-btn:hover {
        background-color: #d32f2f;
    }
</style>
