<?php
require 'vendor/autoload.php';

session_start(); // Ensure session_start is called

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Fetch the username of the logged-in user
$user_id = $_SESSION["user_id"];
$db = new SQLite3('index.db');
$userQuery = $db->prepare("SELECT username FROM users WHERE id = :user_id");
$userQuery->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $userQuery->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);
$username = $user['username'];

// Check if the user is an admin (assuming admin user has id=1)
if ($user_id !== 1) {
    header("Location: articles.php");
}

// Function to fetch a random Wikipedia article
function getRandomWikipediaArticle() {
    $url = "https://en.wikipedia.org/w/api.php?action=query&list=random&rnnamespace=0&format=json";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['query']['random'][0]['title'])) {
        return $data['query']['random'][0]['title'];
    }

    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch a random Wikipedia article title
    $articleTitle = getRandomWikipediaArticle();

    if ($articleTitle) {
        // Fetch the content of the random Wikipedia article
        $url = "https://en.wikipedia.org/w/api.php?action=query&titles=$articleTitle&prop=extracts&exintro&format=json";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['query']['pages'])) {
            $articleContent = current($data['query']['pages'])['extract'];

            // Add the random Wikipedia article to the database
            $title = $articleTitle;
            $query = $db->prepare("INSERT INTO articles (title, content, author_id) VALUES (:title, :content, :author_id)");
            $query->bindValue(':title', $title, SQLITE3_TEXT);
            $query->bindValue(':content', $articleContent, SQLITE3_TEXT);
            $query->bindValue(':author_id', $user_id, SQLITE3_INTEGER);
            $query->execute();

            header("Location: articles.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Article</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>Welcome, <?php echo $username; ?>! (Admin)</h2>
    <h3>Add Random Wikipedia Article</h3>
    <form method="post" action="">
        <input type="submit" value="Add Random Wikipedia Article">
    </form>
    <a href="articles.php">Back to Articles</a>
    <a href="logout.php">Logout</a>
</body>
</html>
