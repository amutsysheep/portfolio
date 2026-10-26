<?php
session_start();
include '../../PrivateW/connection_GamePC.php';

// Retrieve form data
$Naam = $_POST['Naam'];
$Tussenvoegsel = $_POST['Tussenvoegsel'];
$Achternaam = $_POST['Achternaam'];
$Woonplaats = $_POST['Woonplaats'];
$Straatnaam = $_POST['Straatnaam'];
$Huisnummer = $_POST['Huisnummer'];
$Huisnummer_toevoeging = $_POST['Huisnummer_toevoeging'];
$Postcode = $_POST['Postcode'];
$Email = $_POST['Email'];
$Wachtwoord = $_POST['Wachtwoord'];
$Herhaal_Wachtwoord = $_POST['Herhaal_Wachtwoord'];
$Geboortedatum = $_POST['Geboortedatum'];
$Rol = 2; // Default role for new users


if($Wachtwoord == $Herhaal_Wachtwoord) {

// Check if email already exists
    $checkEmailSql = 'SELECT COUNT(*) FROM gebruikers WHERE Email = :Email';
    $checkEmailStmt = $pdo->prepare($checkEmailSql);
    $checkEmailStmt->bindParam(':Email', $Email);
    $checkEmailStmt->execute();
    $emailCount = $checkEmailStmt->fetchColumn();

    if ($emailCount > 0) {
        $_SESSION['Alert'] = 'Email al bekend in systeem';
        header('Location: ../index.php?page=registreren');
        exit;
    }


    $hashed_wachtwoord = password_hash($Wachtwoord, PASSWORD_DEFAULT);

// Insert user data into the database
    $sql = 'INSERT INTO gebruikers (Naam, Tussenvoegsel, Achternaam, Woonplaats, Straatnaam, Huisnummer, Huisnummer_toevoeging, Postcode, Email, Wachtwoord, Geboortedatum, Rol_id) 
        VALUES (:Naam, :Tussenvoegsel, :Achternaam, :Woonplaats, :Straatnaam, :Huisnummer, :Huisnummer_toevoeging, :Postcode, :Email, :Wachtwoord, :Geboortedatum, :Rol)';
    $Statement = $pdo->prepare($sql);
    $Statement->bindParam(":Naam", $Naam);
    $Statement->bindParam(":Tussenvoegsel", $Tussenvoegsel);
    $Statement->bindParam(":Achternaam", $Achternaam);
    $Statement->bindParam(":Woonplaats", $Woonplaats);
    $Statement->bindParam(":Straatnaam", $Straatnaam);
    $Statement->bindParam(":Huisnummer", $Huisnummer);
    $Statement->bindParam(":Huisnummer_toevoeging", $Huisnummer_toevoeging);
    $Statement->bindParam(":Postcode", $Postcode);
    $Statement->bindParam(":Email", $Email);
    $Statement->bindParam(":Wachtwoord", $hashed_wachtwoord); // Use hashed password
    $Statement->bindParam(":Geboortedatum", $Geboortedatum);
    $Statement->bindParam(":Rol", $Rol);


    $Statement->execute();

    $_SESSION['Alert'] = 'Registratie geslaagd! U kunt nu inloggen.';

// Redirect to login page after successful registration
    header('Location: ../index.php?page=inlog');
    exit;

} else {
    $_SESSION['Alert'] = 'Wachtwoorden komen niet overeen';
    header('Location:../index.php?page=registreren');
    exit;
}
?>
