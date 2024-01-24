<?php

session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Logout logic
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Check if the logged-in user is an admin
$isAdmin = ($_SESSION["user_id"] == 1);

// SQLite database connection
$db = new SQLite3('index.db'); // Replace 'index.db' with your SQLite database file name

// Function to get one selected article from the selected_articles table
function getSelectedArticleFromDatabase() {
    global $db;

    // Fetch a selected article from the selected_articles table
    $query = "SELECT * FROM selected_articles ORDER BY RANDOM() LIMIT 1";
    $result = $db->query($query);

    if ($result) {
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    return false;
}

// Function to get three random articles from the entire range of years' top 50 reports
function getRandomArticlesFromTop50Reports() {
    // Initialize an array to store articles
    $articles = [];

    // Fetch random articles from each year's top 50 report
    for ($year = 2010; $year <= 2023; $year++) {
        $reportURL = "https://en.wikipedia.org/wiki/Wikipedia:" . $year . "_Top_50_Report";
        $articles = array_merge($articles, getArticleTitlesFromReport($reportURL, $year));
    }

    // Shuffle the array and return three random articles
    shuffle($articles);
    return array_slice($articles, 0, 3);
}

// Function to get article titles from a specific year's top 50 report
function getArticleTitlesFromReport($reportURL, $year) {
    // Get the HTML content of the report page
    $html = file_get_contents($reportURL);

    // Check if content is not empty
    if ($html === false) {
        return [];
    }

    // Use DOMDocument to parse the HTML content
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    // XPath to extract the "Article" column and "Rank" column from the page
    $xpath = new DOMXPath($dom);
    $articleNodes = $xpath->query('//table[@class="wikitable"]//tr[position() > 1]/td[2]');
    $rankNodes = $xpath->query('//table[@class="wikitable"]//tr[position() > 1]/td[1]');

    // Extract article titles and ranks
    $articleTitles = [];
    foreach ($articleNodes as $index => $node) {
        $rank = $rankNodes->item($index)->nodeValue;
        $articleTitles[] = [trim($node->nodeValue), $year, $rank];
    }

    return $articleTitles;
}

// Function to get the first 7 sentences of an article
function getArticlePreview($article) {
    $apiURL = "https://en.wikipedia.org/w/api.php?action=query&format=json&prop=extracts&titles=" . urlencode($article) . "&exintro&explaintext&exsentences=7";
    $json = file_get_contents($apiURL);
    $data = json_decode($json, true);

    if (isset($data['query']['pages'])) {
        $page = current($data['query']['pages']);
        return $page['extract'];
    }

    return false;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome to the Home Page</h1>
    <p>
        <?php
        if ($isAdmin) {
            echo "Hello, admin!";
        } else {
            echo "Hello, user!";
        }
        ?>
        <a href="?logout=true" class="logout">Logout</a>
    </p>

    <?php if ($isAdmin): ?>
        <a href="view_articles.php" class="admin-button">View All Selected Articles</a>
    <?php endif; ?>

    <div class="selected-article">
        <h2>Read Our Selected Article:</h2>
        <?php
        // Display the selected article
        $selectedArticle = getSelectedArticleFromDatabase();
        
        if ($selectedArticle) {
            ?>
            <ul>
                <li>
                    <p><?php echo $selectedArticle['quote']; ?></p>
                    <a href="<?php echo $selectedArticle['article_link']; ?>" target="_blank"><?php echo $selectedArticle['article_name']; ?></a>
                    <div class="article-preview">
                        <strong>Article Preview:</strong><br>
                        <?php
                            // Display the preview_words from the database
                            echo $selectedArticle['preview_words'];
                        ?>
                    </div>
                </li>
            </ul>
            <?php
        } else { ?>
            <p>No selected articles found.</p>
        <?php }
        ?>
    </div>

    <h3>Random Articles from Top 50 Reports (2010-2023)</h3>
    <?php
    // Fetch and display three random articles from the entire range of years
    $randomArticles = getRandomArticlesFromTop50Reports();

    if ($randomArticles) {
        ?>
        <ul>
            <?php
            foreach ($randomArticles as $info) {
                list($article, $year, $rank) = $info;
                $wikipediaURL = "https://en.wikipedia.org/wiki/" . urlencode(str_replace(" ", "_", $article));
                $preview = getArticlePreview($article);
                ?>
                <li>
                    <p>This was the <?php echo $rank; ?> most commonly read article on Wikipedia at year <?php echo $year; ?></p>
                    <a href="<?php echo $wikipediaURL; ?>" target="_blank"><?php echo $article; ?></a>
                    <?php if ($preview !== false): ?>
                        <div class="article-preview">
                            <strong>Article Preview:</strong><br>
                            <?php echo $preview; ?>
                        </div>
                    <?php else: ?>
                        <div class="article-preview">
                            <strong>Article Preview:</strong><br>
                            Unable to retrieve preview.
                        </div>
                    <?php endif; ?>
                </li>
                <?php
            }
            ?>
        </ul>
        <?php
    } else { ?>
        <p>No valid Wikipedia articles found.</p>
    <?php }
    ?>
</body>
</html>
