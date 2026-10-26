<?php

if(isset($_SESSION['Rol'])) {
    if ($_SESSION['Rol'] == '1') {
        include 'navitems/navbar_admin.inc.php';
    } else if($_SESSION['Rol'] == '2') {
        include 'navitems/navbar_klant.inc.php';
    } else {
        include 'navitems/navbar_gast.inc.php';
    }
}else {
    include 'navitems/navbar_gast.inc.php';
}
?>


