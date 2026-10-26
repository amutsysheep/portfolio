<?php

session_start();

require_once __DIR__ . "/../PHP/Showmessage.php";
include "../../Private/connection_Foh.php";


// Alleen rol 2 mag voorstellingen aanmaken
if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 2) {
    header("Location: ../index.php?page=login");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=Voorstellingen_aanmaken");
    exit();
}


// Input ophalen
$opera_id   = (int)($_POST['Opera_id'] ?? 0);
$locatie_id = (int)($_POST['Locatie_id'] ?? 0);
$datum      = trim($_POST['Datum'] ?? '');
$starttijd  = trim($_POST['Starttijd'] ?? '');
$eindtijd   = trim($_POST['Eindtijd'] ?? '');


// Validatie: opera
if ($opera_id <= 0) {
    ShowMessage("error", "Kies een geldige opera.");
    header("Location: ../index.php?page=Voorstellingen_aanmaken");
    exit();
}


// Validatie: locatie
if ($locatie_id <= 0) {
    ShowMessage("error", "Kies een geldige locatie.");
    header("Location: ../index.php?page=Voorstellingen_aanmaken");
    exit();
}


// Validatie: datum
$datumObj = DateTime::createFromFormat('Y-m-d', $datum);

if (!$datumObj || $datumObj->format('Y-m-d') !== $datum) {
    ShowMessage("error", "Vul een geldige datum in.");
    header("Location: ../index.php?page=Voorstellingen_aanmaken");
    exit();
}

if ($datum < date('Y-m-d')) {
    ShowMessage("error", "De datum mag niet in het verleden liggen.");
    header("Location: ../index.php?page=Voorstellingen_aanmaken");
    exit();
}


// Validatie: tijden (HH:MM)
$startObj = DateTime::createFromFormat('H:i', $starttijd);
$eindObj  = DateTime::createFromFormat('H:i', $eindtijd);

if (!$startObj || $startObj->format('H:i') !== $starttijd) {
    ShowMessage("error", "Vul een geldige starttijd in.");
    header("Location: ../index.php?page=Voorstellingen_aanmaken");
    exit();
}

if (!$eindObj || $eindObj->format('H:i') !== $eindtijd) {
    ShowMessage("error", "Vul een geldige eindtijd in.");
    header("Location: ../index.php?page=Voorstellingen_aanmaken");
    exit();
}

if ($eindObj <= $startObj) {
    ShowMessage("error", "De eindtijd moet na de starttijd liggen.");
    header("Location: ../index.php?page=Voorstellingen_aanmaken");
    exit();
}


try {

    // Controleren of de opera bestaat en actief is
    $stmt = $pdo->prepare("
        SELECT operas.Opera_id
        FROM operas
        WHERE operas.Opera_id = :Opera_id
          AND operas.Actief = 1
    ");

    $stmt->execute([
        'Opera_id' => $opera_id
    ]);

    if (!$stmt->fetch()) {
        ShowMessage("error", "Deze opera bestaat niet of is niet actief.");
        header("Location: ../index.php?page=Voorstellingen_aanmaken");
        exit();
    }


    // Controleren of de locatie bestaat en actief is
    $stmt = $pdo->prepare("
        SELECT locaties.Locatie_id
        FROM locaties
        WHERE locaties.Locatie_id = :Locatie_id
          AND locaties.Actief = 1
    ");

    $stmt->execute([
        'Locatie_id' => $locatie_id
    ]);

    if (!$stmt->fetch()) {
        ShowMessage("error", "Deze locatie bestaat niet of is niet actief.");
        header("Location: ../index.php?page=Voorstellingen_aanmaken");
        exit();
    }


    // Controleren of er al een voorstelling op deze locatie in dit tijdsblok staat
    $startDatumTijd = $datum . ' ' . $starttijd . ':00';
    $eindDatumTijd  = $datum . ' ' . $eindtijd . ':00';

    $stmt = $pdo->prepare("
        SELECT voorstellingen.Voorstelling_id
        FROM voorstellingen
        WHERE voorstellingen.Locatie_id = :Locatie_id
          AND voorstellingen.Actief = 1
          AND voorstellingen.Starttijd < :Eindtijd
          AND voorstellingen.Eindtijd > :Starttijd
    ");

    $stmt->execute([
        'Locatie_id' => $locatie_id,
        'Starttijd'  => $startDatumTijd,
        'Eindtijd'   => $eindDatumTijd
    ]);

    if ($stmt->fetch()) {
        ShowMessage("error", "Er staat al een voorstelling op deze locatie in dit tijdsblok.");
        header("Location: ../index.php?page=Voorstellingen_aanmaken");
        exit();
    }


    // Voorstelling opslaan
    $stmt = $pdo->prepare("
        INSERT INTO voorstellingen (Opera_id, Locatie_id, Starttijd, Eindtijd, Actief)
        VALUES (:Opera_id, :Locatie_id, :Starttijd, :Eindtijd, 1)
    ");

    $stmt->execute([
        'Opera_id'   => $opera_id,
        'Locatie_id' => $locatie_id,
        'Starttijd'  => $startDatumTijd,
        'Eindtijd'   => $eindDatumTijd
    ]);


    ShowMessage("success", "Voorstelling succesvol aangemaakt.");

    header("Location: ../index.php?page=Voorstellingen_beheren");
    exit();


} catch (PDOException $e) {

    error_log($e->getMessage());

    ShowMessage("error", "Voorstelling aanmaken mislukt.");

    header("Location: ../index.php?page=Voorstellingen_aanmaken");
    exit();
}