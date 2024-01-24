<?php

// SQLite database connection
$db = new SQLite3('index.db'); // Replace 'index.db' with your SQLite database file name

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
    $articleName = $_POST["article_name"];
    $articleLink = $_POST["article_link"];
    $quote = $_POST["quote"];
    $previewWords = $_POST["preview_words"];

    // Sanitize input to prevent SQL injection
    $articleName = $db->escapeString($articleName);
    $articleLink = $db->escapeString($articleLink);
    $quote = $db->escapeString($quote);
    $previewWords = $db->escapeString($previewWords);

    // Insert new article into the selected_articles table
    $query = "INSERT INTO selected_articles (article_name, article_link, quote, preview_words) VALUES ('$articleName', '$articleLink', '$quote', '$previewWords')";
    $db->exec($query);

    // Redirect to avoid duplicate submissions
    header("Location: view_articles.php");
    exit();
}

// Handle logout
if (isset($_GET["logout"])) {
    // Destroy the session and redirect to the login page
    session_start();
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            position: relative;
        }

        h2 {
            text-align: center;
        }

        .article {
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 10px;
            position: relative;
        }

        .remove-button {
            position: absolute;
            top: 0;
            right: 0;
        }

        .add-article-form {
            margin-top: 20px;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            position: absolute;
            top: 0;
            right: 0;
        }

        .logout-link,
        .return-link {
            cursor: pointer;
            color: #007BFF;
            text-decoration: underline; /* Add underline to mimic normal text link */
            margin-right: 10px; /* Add margin to separate links */
        }

        .add-article-form label,
        .add-article-form input,
        .add-article-form textarea,
        .add-article-form button {
            display: block;
            margin-bottom: 10px;
        }
    </style>
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
        <a href="view_articles.php" class="logout-link">Logout</a>
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
