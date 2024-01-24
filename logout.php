<?php

require 'vendor/autoload.php';

session_start();
session_destroy();
header("Location: login.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <p>You have been logged out.</p>
    <a href="login.php">Login Again</a>
</body>
</html>
