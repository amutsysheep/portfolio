<?php
// Zorg dat de sessie gestart is
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Controleer welke rol de gebruiker heeft
if(isset($_SESSION['rol_id'])) {
    switch($_SESSION['rol_id']) {
        case 1:
            include 'Navitems/Navbar_beheerder.inc.php';
            break;
        case 2:
            include 'Navitems/Navbar_producer.inc.php';
            break;
        case 3:
            include 'Navitems/Navbar_artiest.inc.php';
            break;
        default:
            include 'Navitems/Navbar_gast.inc.php';
    }
} else {
    include 'Navitems/Navbar_gast.inc.php';
}
?>
