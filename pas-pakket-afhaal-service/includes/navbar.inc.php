<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$rol_id = $_SESSION['rol_id'] ?? null;
?>

<nav class="navbar">
    <div class="nav-logo">PakketService</div>

    <?php
    if (!$rol_id) {
        ?>
        <ul class="nav-links">
            <li><a href="index.php?page=pakket_traceren">Track & Trace</a></li>
            <li><a href="index.php?page=registratie_klant">Registreren</a></li>
            <li><a href="index.php?page=registratie_courier">Registreren courier</a></li>
            <li><a href="index.php?page=login" class="login-btn">Inloggen</a></li>
        </ul>

        <?php
    } elseif ($rol_id == 1) {
        ?>
        <ul class="nav-links">
            <li><a href="index.php?page=pakketten_overzicht">Mijn pakketten</a></li>
            <li><a href="index.php?page=pakket_aanmelden_klant" class="logout">Pakket aanmelden</a></li>
            <li><a href="index.php?page=pakket_traceren">Track & Trace</a></li>
            <li><a href="index.php?page=profiel">Profipael</a></li>
            <li><a href="PHP/uitloggen.php" class="logout">Uitloggen</a></li>
        </ul>

        <?php
    } elseif ($rol_id == 2) {
        ?>
        <ul class="nav-links">
            <li><a href="index.php?page=pakketten_overzicht_courier">Paketten</a></li>
            <li><a href="index.php?page=courier_overzicht">Mijn routes</a></li>
            <li><a href="index.php?page=profiel">Profiel</a></li>
            <li><a href="php/uitloggen.php" class="logout">Uitloggen</a></li>
        </ul>

        <?php
    } elseif ($rol_id == 3) {
        ?>
        <ul class="nav-links">
            <li><a href="index.php?page=alle_pakketten_overzicht">Alle pakketten overzicht</a></li>
            <li><a href="index.php?page=courier_beheer">Courier beheer</a></li>
            <li><a href="index.php?page=status_beheer">Statussen</a></li>
            <li><a href="php/uitloggen.php" class="logout">Uitloggen</a></li>
        </ul>
    <?php } ?>
</nav>
