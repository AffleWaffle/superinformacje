<?php
// SQLite database connection
$db = new SQLite3('index.db');

// Ensure session_start is called at the beginning
session_start();

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Function to get all selected articles from the selected_articles table
function getAllSelectedArticles() {
    global $db;

    // Fetch all selected articles from the selected_articles table
    $query = "SELECT * FROM selected_articles";
    $result = $db->query($query);

    $articles = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $articles[] = $row;
    }

    return $articles;
}

// Function to remove an article from the selected_articles table
function removeArticle($articleId) {
    global $db;

    // Sanitize input to prevent SQL injection
    $articleId = $db->escapeString($articleId);

    // Delete the article from the selected_articles table
    $query = "DELETE FROM selected_articles WHERE id = '$articleId'";
    $db->exec($query);
}

// Handle form submissions for removing an article
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove_article"])) {
    $articleId = $_POST["article_id"];
    removeArticle($articleId);

    // Redirect to avoid resubmitting the form on page refresh
    header("Location: view_articles.php");
    exit();
}

// Handle form submissions for adding a new article
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_article"])) {
    // Redirect to add_article.php
    header("Location: add_article.php");
    exit();
}

// Handle logout
if (isset($_GET["logout"])) {
    // Destroy the session and redirect to the login page
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Selected Articles</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2 class="add-article-form">Add a New Article</h2>
    <form method="post" class="add-article-form">
        <label for="article_name">Article Name:</label>
        <input type="text" name="article_name" required>

        <label for="article_link">Article Link:</label>
        <input type="text" name="article_link" required>

        <label for="quote">Quote:</label>
        <textarea name="quote" rows="2" required></textarea>

        <label for="preview_words">Preview Words:</label>
        <textarea name="preview_words" rows="4" cols="50" required></textarea>

        <button type="submit" name="add_article">Add Article</button>
    </form>

    <div class="button-group">
        <a href="logout.php" class="logout-link">Logout</a>
        <a href="index.php" class="return-link">Return to Index</a>
    </div>

    <h2>All Selected Articles</h2>

    <?php
    // Display all selected articles
    $selectedArticles = getAllSelectedArticles();

    if ($selectedArticles) {
        foreach ($selectedArticles as $article) {
            ?>
            <div class="article">
                <p><?php echo $article['quote']; ?></p>
                <a href="<?php echo $article['article_link']; ?>" target="_blank"><?php echo $article['article_name']; ?></a>
                <div>
                    <strong>Preview Words:</strong><br>
                    <?php echo $article['preview_words']; ?>
                </div>
                <form method="post" class="remove-button">
                    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                    <button type="submit" name="remove_article">Remove Article</button>
                </form>
            </div>
            <?php
        }
    } else {
        ?>
        <p>No selected articles found.</p>
        <?php
    }
    ?>

</body>
</html>
