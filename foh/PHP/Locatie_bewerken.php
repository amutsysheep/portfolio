<?php
session_start();

require_once '../../Private/connection_Foh.php';
require_once 'ShowMessage.php';

// Controleren of er een geldig ID is meegegeven
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    ShowMessage('error', 'Ongeldige locatie.');
    header('Location: ../Index.php?page=locaties');
    exit;
}

$locatie_id = (int) $_GET['id'];

// Alleen POST toestaan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Index.php?page=locaties');
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
    header('Location: ../Index.php?page=locaties_bewerken&id=' . $locatie_id);
    exit;
}

if ($plaats === '') {
    ShowMessage('error', 'Plaats is verplicht.');
    header('Location: ../Index.php?page=locaties_bewerken&id=' . $locatie_id);
    exit;
}

if ($postcode !== '' && strlen($postcode) > 6) {
    ShowMessage('error', 'Postcode mag maximaal 6 tekens bevatten.');
    header('Location: ../Index.php?page=locaties_bewerken&id=' . $locatie_id);
    exit;
}

try {

    $sql = "
        UPDATE locaties
        SET
            Naam = :naam,
            Zaal = :zaal,
            Plaats = :plaats,
            Straatnaam = :straatnaam,
            Huisnummer = :huisnummer,
            Postcode = :postcode
        WHERE Locatie_id = :locatie_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':naam' => $naam,
        ':zaal' => $zaal !== '' ? $zaal : null,
        ':plaats' => $plaats,
        ':straatnaam' => $straatnaam !== '' ? $straatnaam : null,
        ':huisnummer' => $huisnummer !== '' ? $huisnummer : null,
        ':postcode' => $postcode !== '' ? $postcode : null,
        ':locatie_id' => $locatie_id
    ]);

    ShowMessage('success', 'De locatie is succesvol aangepast.');

} catch (PDOException $e) {

    ShowMessage('error', 'Er is een fout opgetreden bij het aanpassen van de locatie.');
}

header('Location: ../index.php?page=Locatie_overzicht');
exit;