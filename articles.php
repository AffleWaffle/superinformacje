<?php

require 'vendor/autoload.php';

session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
}

// Fetch and display articles from the database
$db = new SQLite3('articles.db');
$query = $db->query("SELECT * FROM articles");

// Fetch the username of the logged-in user
$user_id = $_SESSION["user_id"];
$userQuery = $db->prepare("SELECT username FROM users WHERE id = :user_id");
$userQuery->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $userQuery->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);
$username = $user['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Articles</title>
</head>
<body>
    <h2>Welcome, <?php echo $username; ?>!</h2>
    <h3>AI-generated Articles</h3>
    <ul>
        <?php while ($row = $query->fetchArray(SQLITE3_ASSOC)): ?>
            <li>
                <h3><?= $row['title']; ?></h3>
                <p><?= $row['content']; ?></p>
            </li>
        <?php endwhile; ?>
    </ul>

    <?php if ($user_id === 1): ?>
        <a href="add_article.php">Add Article</a>
    <?php endif; ?>

    <a href="logout.php">Logout</a>
</body>
</html>
