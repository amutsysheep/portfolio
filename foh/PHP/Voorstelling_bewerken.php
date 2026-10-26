<?php

session_start();

require_once __DIR__ . "/Showmessage.php";
include "../../Private/connection_Foh.php";


// Alleen rol 2 mag voorstellingen bewerken
if (!isset($_SESSION['rol_id']) || (int)$_SESSION['rol_id'] !== 2) {
    header("Location: ../index.php?page=login");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=Voorstellingen_beheren");
    exit();
}


// Gegevens uit formulier ophalen

$voorstelling_id = (int)($_POST['Voorstelling_id'] ?? 0);
$opera_id = (int)($_POST['Opera_id'] ?? 0);
$locatie_id = (int)($_POST['Locatie_id'] ?? 0);
$datum = trim($_POST['Datum'] ?? '');
$starttijd = trim($_POST['Starttijd'] ?? '');
$eindtijd = trim($_POST['Eindtijd'] ?? '');


// Controleren of alle gegevens zijn ingevuld

if (
    $voorstelling_id <= 0 ||
    $opera_id <= 0 ||
    $locatie_id <= 0 ||
    $datum === '' ||
    $starttijd === '' ||
    $eindtijd === ''
) {

    ShowMessage(
        "error",
        "Voorstelling aanpassen mislukt. Vul alle velden in."
    );

    header("Location: ../index.php?page=Voorstellingen_beheren");
    exit();
}


// Controleren of datum en tijden geldig zijn

$datumObj = DateTime::createFromFormat('Y-m-d', $datum);
$startObj = DateTime::createFromFormat('H:i', $starttijd);
$eindObj  = DateTime::createFromFormat('H:i', $eindtijd);

if (
    !$datumObj || $datumObj->format('Y-m-d') !== $datum ||
    !$startObj || $startObj->format('H:i') !== $starttijd ||
    !$eindObj  || $eindObj->format('H:i') !== $eindtijd
) {

    ShowMessage(
        "error",
        "Voorstelling aanpassen mislukt. Ongeldige datum of tijd."
    );

    header("Location: ../index.php?page=Voorstelling_bewerken&Voorstelling_id=" . $voorstelling_id);
    exit();
}

if ($datum < date('Y-m-d')) {

    ShowMessage(
        "error",
        "De datum mag niet in het verleden liggen."
    );

    header("Location: ../index.php?page=Voorstelling_bewerken&Voorstelling_id=" . $voorstelling_id);
    exit();
}

if ($eindObj <= $startObj) {

    ShowMessage(
        "error",
        "De eindtijd moet na de starttijd liggen."
    );

    header("Location: ../index.php?page=Voorstelling_bewerken&Voorstelling_id=" . $voorstelling_id);
    exit();
}


try {

    // Controleren of er al een andere voorstelling op deze locatie in dit tijdsblok staat

    $startDatumTijd = $datum . ' ' . $starttijd . ':00';
    $eindDatumTijd  = $datum . ' ' . $eindtijd . ':00';

    $stmt = $pdo->prepare("
        SELECT voorstellingen.Voorstelling_id
        FROM voorstellingen
        WHERE voorstellingen.Locatie_id = :Locatie_id
          AND voorstellingen.Actief = 1
          AND voorstellingen.Voorstelling_id != :Voorstelling_id
          AND voorstellingen.Starttijd < :Eindtijd
          AND voorstellingen.Eindtijd > :Starttijd
    ");

    $stmt->execute([
        'Locatie_id' => $locatie_id,
        'Voorstelling_id' => $voorstelling_id,
        'Starttijd' => $startDatumTijd,
        'Eindtijd' => $eindDatumTijd
    ]);

    if ($stmt->fetch()) {

        ShowMessage(
            "error",
            "Er staat al een voorstelling op deze locatie in dit tijdsblok."
        );

        header("Location: ../index.php?page=Voorstelling_bewerken&Voorstelling_id=" . $voorstelling_id);
        exit();
    }


    // Voorstelling bijwerken

    $stmt = $pdo->prepare("
        UPDATE voorstellingen
        SET
            Opera_id = :Opera_id,
            Locatie_id = :Locatie_id,
            Starttijd = :Starttijd,
            Eindtijd = :Eindtijd
        WHERE Voorstelling_id = :Voorstelling_id
    ");

    $stmt->execute([
        'Opera_id' => $opera_id,
        'Locatie_id' => $locatie_id,
        'Starttijd' => $startDatumTijd,
        'Eindtijd' => $eindDatumTijd,
        'Voorstelling_id' => $voorstelling_id
    ]);


    // Succesmelding

    ShowMessage(
        "success",
        "Voorstelling succesvol aangepast."
    );


    // Terug naar overzicht

    header("Location: ../index.php?page=Voorstellingen_beheren");
    exit();


} catch (PDOException $e) {

    error_log($e->getMessage());

    ShowMessage(
        "error",
        "Er is iets misgegaan bij het aanpassen van de voorstelling."
    );

    header("Location: ../index.php?page=Voorstellingen_beheren");
    exit();
}