<?php
session_start();

//echo "<pre>", print_r($_POST), "</pre>";

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 'home'; // Home page
}

$page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page);

?>

    <html lang="en">
    <head>
        <title><?= htmlspecialchars($page) ?></title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>


<?php
include 'includes/navbar.inc.php';

include 'Includes/' . $page . '.inc.php';  // Load other pages
?>

    </body>
</html>
