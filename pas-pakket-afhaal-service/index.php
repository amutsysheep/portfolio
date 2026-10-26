<?php
session_start();

require_once __DIR__ . '/../../private/connection.php';


if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 'registratie_klant';
}

$page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page);

?>

<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="css/style.css">
<body>

<?php
include "includes/navbar.inc.php";
?>


<main class="main-content">
    <?php
    include 'includes/' . $page . '.inc.php';
    ?>
</main>
</body>
</html>

