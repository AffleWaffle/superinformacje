<?php

require 'vendor/autoload.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate login credentials (you may add more secure validation)
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Validate against database (assume you've connected to the database)
    $db = new SQLite3('index.db');
    $query = $db->prepare("SELECT * FROM users WHERE username=:username AND password=:password");
    $query->bindValue(':username', $username, SQLITE3_TEXT);
    $query->bindValue(':password', $password, SQLITE3_TEXT);
    $result = $query->execute();

    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $_SESSION["user_id"] = $row["id"];
        header("Location: index.php");  // Update this line to redirect to index.php
        exit();  // Ensure that no further code is executed after the redirection
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h2>Login</h2>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <input type="submit" value="Login">
    </form>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
</body>
</html>
