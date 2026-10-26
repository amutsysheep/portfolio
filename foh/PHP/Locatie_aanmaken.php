<?php
session_start();

require_once '../../Private/connection_Foh.php';
require_once 'ShowMessage.php';

// Alleen POST toestaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Index.php?page=Locatie_aanmaken');
    exit;
}

// Gegevens ophalen
$naam = trim($_POST['Naam'] ?? '');
$zaal = trim($_POST['Zaal'] ?? '');
$plaats = trim($_POST['Plaats'] ?? '');
$straatnaam = trim($_POST['Straatnaam'] ?? '');
$huisnummer = trim($_POST['Huisnummer'] ?? '');
$postcode = strtoupper(trim($_POST['Postcode'] ?? ''));

// Validatie
if ($naam === '') {
    ShowMessage('error', 'Naam is verplicht.');
    header('Location: ../Index.php?page=Locatie_aanmaken');
    exit;
}

if ($plaats === '') {
    ShowMessage('error', 'Plaats is verplicht.');
    header('Location: ../Index.php?page=Locatie_aanmaken');
    exit;
}

if (strlen($postcode) > 6) {
    ShowMessage('error', 'Postcode mag maximaal 6 tekens bevatten.');
    header('Location: ../Index.php?page=Locatie_aanmaken');
    exit;
}

try {

    $sql = "
        INSERT INTO locaties (
            Naam,
            Zaal,
            Plaats,
            Straatnaam,
            Huisnummer,
            Postcode,
            Actief
        ) VALUES (
            :naam,
            :zaal,
            :plaats,
            :straatnaam,
            :huisnummer,
            :postcode,
            1
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':naam' => $naam,
        ':zaal' => $zaal !== '' ? $zaal : null,
        ':plaats' => $plaats,
        ':straatnaam' => $straatnaam !== '' ? $straatnaam : null,
        ':huisnummer' => $huisnummer !== '' ? $huisnummer : null,
        ':postcode' => $postcode !== '' ? $postcode : null
    ]);

    ShowMessage('success', 'De locatie is succesvol aangemaakt.');

} catch (PDOException $e) {

    ShowMessage('error', 'Er is een fout opgetreden bij het aanmaken van de locatie.');
}

header('Location: ../index.php?page=Locatie_overzicht');
exit;