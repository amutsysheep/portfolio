<?php
include '../PrivateW/connection_GamePc.php'; // Verbinding met de database

if (isset($_SESSION['User_id'])) {
    include 'includes/winkelmandjes/winkelmandjeklant.inc.php';
} else {
    include 'includes/winkelmandjes/winkelmandjegast.inc.php';
}
?>

<?php
// Toont melding als die bestaat
if (isset($_SESSION['Alert'])) {
    echo "<div class='notification'>" . htmlspecialchars($_SESSION['Alert']) . "</div>";
    unset($_SESSION['Alert']);
}
?>
