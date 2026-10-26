<?php
require_once '../Private/connection_Foh.php';
session_start();

$page = isset($_GET['page']) ? $_GET['page'] : 'Login';
$Page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page);
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="/foh/Images/Logo.png">

    <title><?= htmlspecialchars($Page) ?></title>

    <link rel="stylesheet" href="/foh/CSS/Styling.css">
    <link rel="stylesheet" href="/foh/CSS/Styling_beheerder_dashboard.css">
    <link rel="stylesheet" href="/foh/CSS/Styling_producers_goedkeuren.css">
    <link rel="stylesheet" href="/foh/CSS/Styling_opera_overzicht_gast.css">
    <link rel="stylesheet" href="/foh/CSS/Styling_locatie_overzicht.css">
    <link rel="stylesheet" href="/foh/CSS/Styling_voorstelling.css">
    <link rel="stylesheet" href="/foh/CSS/Styling_opera_aanmaken.css">
</head>



<body>

<header>
    <?php include 'Includes/Navbar.inc.php'; ?>
</header>

<main>
    <?php include 'Includes/' . $Page . '.inc.php'; ?>
</main>

</body>
</html>
