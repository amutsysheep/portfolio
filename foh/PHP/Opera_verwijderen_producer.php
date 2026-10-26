<?php
session_start();

require "../../Private/connection_foh.php";
require "Showmessage.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?page=Opera_overzicht_producer");
    exit;
}

$operaId = (int)($_POST['opera_id'] ?? 0);

if ($operaId <= 0) {
    ShowMessage('error', 'Ongeldige opera.');
    header("Location: ../index.php?page=Opera_overzicht_producer");
    exit;
}

if ($pdo === null) {
    ShowMessage('error', 'Database niet beschikbaar.');
    header("Location: ../index.php?page=Opera_overzicht_producer");
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT Opera_id
        FROM operas
        WHERE Opera_id = :opera_id
    ");
    $stmt->execute([
        ':opera_id' => $operaId
    ]);

    $opera = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$opera) {
        ShowMessage('error', 'Opera niet gevonden.');
        header("Location: ../index.php?page=Opera_overzicht_producer");
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE operas
        SET Actief = 0
        WHERE Opera_id = :opera_id
    ");
    $stmt->execute([
        ':opera_id' => $operaId
    ]);

    ShowMessage('success', 'Opera succesvol verwijderd.');
    header("Location: ../index.php?page=Opera_overzicht_producer");
    exit;
} catch (Exception $e) {
    ShowMessage('error', 'Opera kon niet worden verwijderd.');
    header("Location: ../index.php?page=Opera_overzicht_producer");
    exit;
}