<?php
session_start();

require_once '../../Private/connection_Foh.php';
require_once 'ShowMessage.php';

// Alleen POST toestaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=registratie_artiest');
    exit;
}

$voornaam = trim($_POST['voornaam'] ?? '');
$tussenvoegsel = trim($_POST['tussenvoegsel'] ?? '');
$achternaam = trim($_POST['achternaam'] ?? '');
$email = trim($_POST['email'] ?? '');
$geslacht = trim($_POST['geslacht'] ?? '');

$woonplaats = trim($_POST['woonplaats'] ?? '');
$straatnaam = trim($_POST['straatnaam'] ?? '');
$huisnummer = trim($_POST['huisnummer'] ?? '');
$huisnummertoevoeging = trim($_POST['huisnummertoevoeging'] ?? '');
$postcode = trim($_POST['postcode'] ?? '');

$beroepId = $_POST['beroep_id'] ?? '';

$wachtwoord = $_POST['wachtwoord'] ?? '';
$herhaalWachtwoord = $_POST['herhaal_wachtwoord'] ?? '';

// Gegevens bewaren bij een fout
$_SESSION['old_input'] = [
    'voornaam' => $voornaam,
    'tussenvoegsel' => $tussenvoegsel,
    'achternaam' => $achternaam,
    'email' => $email,
    'geslacht' => $geslacht,
    'woonplaats' => $woonplaats,
    'straatnaam' => $straatnaam,
    'huisnummer' => $huisnummer,
    'huisnummertoevoeging' => $huisnummertoevoeging,
    'postcode' => $postcode,
    'beroep_id' => $beroepId
];

// Verplichte velden controleren
if (
    $voornaam === '' ||
    $achternaam === '' ||
    $email === '' ||
    $geslacht === '' ||
    $woonplaats === '' ||
    $straatnaam === '' ||
    $huisnummer === '' ||
    $postcode === '' ||
    $beroepId === '' ||
    $wachtwoord === '' ||
    $herhaalWachtwoord === ''
) {
    ShowMessage('error', 'Vul alle verplichte velden in.');

    header('Location: ../index.php?page=registratie_artiest');
    exit;
}

// Geslacht controleren
if (!in_array($geslacht, ['Man', 'Vrouw'], true)) {
    ShowMessage('error', 'Selecteer een geldig geslacht.');

    header('Location: ../index.php?page=registratie_artiest');
    exit;
}

// E-mailadres controleren
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ShowMessage('error', 'Vul een geldig e-mailadres in.');

    header('Location: ../index.php?page=registratie_artiest');
    exit;
}

// Wachtwoorden controleren
if ($wachtwoord !== $herhaalWachtwoord) {
    ShowMessage('error', 'De wachtwoorden komen niet overeen.');

    header('Location: ../index.php?page=registratie_artiest');
    exit;
}

// Wachtwoord controleren
if (!preg_match('/^(?=.*[A-Z])(?=.*[!@#$%^&*]).{6,}$/', $wachtwoord)) {
    ShowMessage(
        'error',
        'Het wachtwoord moet minimaal 6 tekens bevatten, ' .
        'waarvan minimaal 1 hoofdletter en 1 speciaal teken (!@#$%^&*).'
    );

    header('Location: ../index.php?page=registratie_artiest');
    exit;
}

// Postcode controleren
if (!preg_match('/^[0-9]{4}\s?[A-Za-z]{2}$/', $postcode)) {
    ShowMessage('error', 'Vul een geldige Nederlandse postcode in.');

    header('Location: ../index.php?page=registratie_artiest');
    exit;
}

// Huisnummer controleren
if (!ctype_digit($huisnummer) || (int)$huisnummer <= 0) {
    ShowMessage('error', 'Vul een geldig huisnummer in.');

    header('Location: ../index.php?page=registratie_artiest');
    exit;
}

try {
    $pdo->beginTransaction();

    /* Controleren of het e-mailadres al bestaat */
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

        header('Location: ../index.php?page=registratie_artiest');
        exit;
    }

    /* Controleren of het beroep bestaat */
    $stmt = $pdo->prepare("
        SELECT Beroep_id
        FROM beroepen
        WHERE Beroep_id = :beroep_id
        LIMIT 1
    ");

    $stmt->execute([
        ':beroep_id' => $beroepId
    ]);

    if (!$stmt->fetch()) {
        $pdo->rollBack();

        ShowMessage('error', 'Het geselecteerde beroep bestaat niet.');

        header('Location: ../index.php?page=registratie_artiest');
        exit;
    }

    $cv = null;

    if (
        isset($_FILES['cv']) &&
        $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
            $pdo->rollBack();

            ShowMessage(
                'error',
                'Er is iets misgegaan met het uploaden van het CV.'
            );

            header('Location: ../index.php?page=registratie_artiest');
            exit;
        }

        // Maximale bestandsgrootte: 5 MB
        if ($_FILES['cv']['size'] > 5 * 1024 * 1024) {
            $pdo->rollBack();

            ShowMessage(
                'error',
                'Het CV mag maximaal 5 MB groot zijn.'
            );

            header('Location: ../index.php?page=registratie_artiest');
            exit;
        }

        // Toegestane bestandstypen
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $fileType = mime_content_type(
            $_FILES['cv']['tmp_name']
        );

        if (!in_array($fileType, $allowedTypes, true)) {
            $pdo->rollBack();

            ShowMessage(
                'error',
                'Het CV moet een PDF-, DOC- of DOCX-bestand zijn.'
            );

            header('Location: ../index.php?page=registratie_artiest');
            exit;
        }

        // Bestand uitlezen als BLOB
        $cv = file_get_contents(
            $_FILES['cv']['tmp_name']
        );

        if ($cv === false) {
            $pdo->rollBack();

            ShowMessage(
                'error',
                'Het CV kon niet worden ingelezen.'
            );

            header('Location: ../index.php?page=registratie_artiest');
            exit;
        }
    }

    /* Wachtwoord hashen */
    $hashedPassword = password_hash(
        $wachtwoord,
        PASSWORD_DEFAULT
    );

    /* Login aanmaken */
    $rolId = 3;

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
        ':rol_id' => $rolId
    ]);

    // Login ID ophalen
    $loginId = $pdo->lastInsertId();
    $artiestStatusId = 1;

    /* Artiest aanmaken */
    $stmt = $pdo->prepare("
        INSERT INTO artiesten (
            Login_id,
            Beroep_id,
            Artiest_status_id,
            Voornaam,
            Tussenvoegsel,
            Achternaam,
            Woonplaats,
            Straatnaam,
            Huisnummer,
            Huisnummertoevoeging,
            Postcode,
            CV,
            Geslacht,
            Actief
        )
        VALUES (
            :login_id,
            :beroep_id,
            :artiest_status_id,
            :voornaam,
            :tussenvoegsel,
            :achternaam,
            :woonplaats,
            :straatnaam,
            :huisnummer,
            :huisnummertoevoeging,
            :postcode,
            :cv,
            :geslacht,
            :actief
        )
    ");

    $stmt->execute([
        ':login_id' => $loginId,
        ':beroep_id' => $beroepId,
        ':artiest_status_id' => $artiestStatusId,
        ':voornaam' => $voornaam,
        ':tussenvoegsel' => $tussenvoegsel !== '' ? $tussenvoegsel : null,
        ':achternaam' => $achternaam,
        ':woonplaats' => $woonplaats,
        ':straatnaam' => $straatnaam,
        ':huisnummer' => (int)$huisnummer,
        ':huisnummertoevoeging' => $huisnummertoevoeging !== ''
            ? $huisnummertoevoeging
            : null,
        ':postcode' => strtoupper(str_replace(' ', '', $postcode)),
        ':cv' => $cv,
        ':geslacht' => $geslacht,
        ':actief' => 1
    ]);

    /* Alles succesvol opgeslagen */
    $pdo->commit();

    // Oude formuliergegevens verwijderen
    unset($_SESSION['old_input']);

    // Succesmelding opslaan
    ShowMessage(
        'success',
        'Je artiestaccount is succesvol aangemaakt!'
    );

    header('Location: ../index.php?page=login');
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    ShowMessage(
        'error',
        'Er is een fout opgetreden tijdens het registreren.'
    );

    header('Location: ../index.php?page=registratie_artiest');
    exit;
}