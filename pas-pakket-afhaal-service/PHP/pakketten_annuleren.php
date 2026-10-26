<?php
session_start();
require '../connection/connection.php';

// Controleer of gebruiker is ingelogd en er een pakket-ID is
if (!isset($_SESSION['user_id'], $_GET['id'])) {
    header('Location: ../index.php?page=pakketten');
    exit;
}

$user_id = $_SESSION['user_id'];
$pakket_id = (int)$_GET['id'];

// Haal pakket en status op van de gebruiker
$stmt = $pdo->prepare("
    SELECT s.status
    FROM pakketten p
    JOIN pakket_status s ON p.status_id = s.status_id
    WHERE p.pakket_id = :id
    AND p.user_id = :user
");
$stmt->execute([
    'id' => $pakket_id,
    'user' => $user_id
]);

$pakket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pakket) {
    $_SESSION['error'] = 'Pakket niet gevonden';
    header('Location: ../index.php?page=pakketten_overzicht');
    exit;
}

// Alleen annuleren als status Aangemeld
if ($pakket['status'] !== 'aangemeld') {
    $_SESSION['error'] = 'Dit pakket kan niet meer geannuleerd worden';
    header('Location: ../index.php?page=pakketten_overzicht');
    exit;
}

// Zet status op Geannuleerd
$update = $pdo->prepare("
    UPDATE pakketten
    SET status_id = (
        SELECT status_id FROM pakket_status WHERE status = 'pakketten_geannuleerd'
    )
    WHERE pakket_id = :id
");
$update->execute(['id' => $pakket_id]);

$_SESSION['succes'] = 'Pakket succesvol geannuleerd';
header('Location: ../index.php?page=pakketten_overzicht');
exit;
