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
        SET Actief = 1
        WHERE Locatie_id = :locatie_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':locatie_id' => $locatie_id
    ]);

    if ($stmt->rowCount() > 0) {
        ShowMessage('success', 'De locatie is succesvol geactiveerd.');
    } else {
        ShowMessage('error', 'De locatie kon niet worden geactiveerd.');
    }

} catch (PDOException $e) {

    ShowMessage('error', 'Er is een fout opgetreden bij het activeren van de locatie.');
}

header('Location: ../index.php?page=Locatie_overzicht');
exit;