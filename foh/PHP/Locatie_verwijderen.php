<?php
session_start();

require_once '../../Private/connection_Foh.php';
require_once 'ShowMessage.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    ShowMessage('error', 'Ongeldige locatie.');
    header('Location: ../index.php?page=Locatie_overzicht');
    exit;
}

$locatie_id = (int) $_GET['id'];

try {

    $sql = "
        UPDATE locaties
        SET Actief = 0
        WHERE Locatie_id = :locatie_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':locatie_id' => $locatie_id
    ]);

    if ($stmt->rowCount() > 0) {
        ShowMessage('success', 'De locatie is succesvol verwijderd.');
    } else {
        ShowMessage('error', 'De locatie kon niet worden verwijderd.');
    }

} catch (PDOException $e) {

    ShowMessage('error', 'Er is een fout opgetreden bij het verwijderen van de locatie.');
}

header('Location: ../index.php?page=Locatie_overzicht');
exit;