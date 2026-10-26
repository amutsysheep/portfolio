<?php
require_once '../../Private/connection_Foh.php';
require_once 'ShowMessage.php';

session_start();

// Alleen POST toestaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=registratie_producer');
    exit;
}

// Gegevens uit formulier ophalen
$voornaam = trim($_POST['voornaam'] ?? '');
$tussenvoegsel = trim($_POST['tussenvoegsel'] ?? '');
$achternaam = trim($_POST['achternaam'] ?? '');
$email = trim($_POST['email'] ?? '');
$wachtwoord = $_POST['wachtwoord'] ?? '';
$herhaalWachtwoord = $_POST['herhaal_wachtwoord'] ?? '';

// Bewaar ingevulde gegevens bij een fout
$_SESSION['old_input'] = [
    'voornaam' => $voornaam,
    'tussenvoegsel' => $tussenvoegsel,
    'achternaam' => $achternaam,
    'email' => $email
];

// Verplichte velden controleren
if (
    $voornaam === '' ||
    $achternaam === '' ||
    $email === '' ||
    $wachtwoord === ''
) {
    ShowMessage('error', 'Vul alle verplichte velden in.');
    header('Location: ../index.php?page=registratie_producer');
    exit;
}

// E-mailadres controleren
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ShowMessage('error', 'Vul een geldig e-mailadres in.');
    header('Location: ../index.php?page=registratie_producer');
    exit;
}

// Wachtwoorden controleren
if ($wachtwoord !== $herhaalWachtwoord) {
    ShowMessage('error', 'De wachtwoorden komen niet overeen.');
    header('Location: ../index.php?page=registratie_producer');
    exit;
}

// Wachtwoord controleren
if (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*]).{6,}$/', $wachtwoord)) {
    ShowMessage(
        'error',
        'Het wachtwoord moet minimaal 6 tekens bevatten, ' .
        'waarvan minimaal 1 hoofdletter en 1 speciaal teken (!@#$%^&*).'
    );

    header('Location: ../index.php?page=registratie_producer');
    exit;
}

try {

    // Database-transactie starten
    $pdo->beginTransaction();

    // Controleren of e-mailadres al bestaat
    $stmt = $pdo->prepare("
        SELECT Login_id
        FROM login
        WHERE Email = :email
        LIMIT 1
    ");

    $stmt->execute([
        ':email' => $email
    ]);

    if ($stmt->fetch()) {
        $pdo->rollBack();
        ShowMessage('error', 'Dit e-mailadres is al in gebruik.');
        header('Location: ../index.php?page=registratie_producer');
        exit;
    }

    // Wachtwoord hashen
    $hashedPassword = password_hash($wachtwoord, PASSWORD_DEFAULT);

    // Login aanmaken
    // Rol_id 2 = Producer
    $stmt = $pdo->prepare("
        INSERT INTO login (
            Email,
            Wachtwoord,
            Rol_id
        )
        VALUES (
            :email,
            :wachtwoord,
            :rol_id
        )
    ");

    $stmt->execute([
        ':email' => $email,
        ':wachtwoord' => $hashedPassword,
        ':rol_id' => 2
    ]);

    // ID van de nieuwe login ophalen
    $loginId = $pdo->lastInsertId();

    // Producer aanmaken
    $stmt = $pdo->prepare("
        INSERT INTO producers (
            Login_id,
            Voornaam,
            Tussenvoegsel,
            Achternaam,
            Producer_status_id,
            Actief
        )
        VALUES (
            :login_id,
            :voornaam,
            :tussenvoegsel,
            :achternaam,
            :producer_status_id,
            :actief
        )
    ");

    $stmt->execute([
        ':login_id' => $loginId,
        ':voornaam' => $voornaam,
        ':tussenvoegsel' => $tussenvoegsel !== '' ? $tussenvoegsel : null,
        ':achternaam' => $achternaam,
        ':producer_status_id' => 1,
        ':actief' => 1
    ]);

    // Alles definitief opslaan
    $pdo->commit();

    // Oude invoer verwijderen
    unset($_SESSION['old_input']);

    // Succesmelding opslaan
    ShowMessage('success', 'Je produceraccount is succesvol aangemaakt!');
    header('Location: ../index.php?page=login');
    exit;

} catch (PDOException $e) {
    // Bij een fout alles terugdraaien
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    ShowMessage('error', 'Databasefout: ' . $e->getMessage());
    header('Location: ../index.php?page=registratie_producer');
    exit;
}