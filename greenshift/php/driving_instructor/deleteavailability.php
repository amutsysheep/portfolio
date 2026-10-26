<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../connection/connection.php";

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['userid'])) {
    header("Location: ../../login.php");
    exit;
}

$instructor_id = $_SESSION['userid'];

// Controleer of slot_id aanwezig is
$slot_id = $_POST['slot_id'] ?? null;

if (!$slot_id) {
    header("Location: ../../index.php?page=dashboard&panel=Driving_Instructor&error=Geen slot geselecteerd!");
    exit;
}

// Controleer of het slot bestaat en bij de ingelogde instructeur hoort
$stmtCheck = $pdo->prepare("SELECT * FROM time_slots WHERE id = ? AND instructor_id = ?");
$stmtCheck->execute([$slot_id, $instructor_id]);
$slot = $stmtCheck->fetch();

if (!$slot) {
    header("Location: ../../index.php?page=dashboard&panel=Driving_Instructor&error=Tijdslot niet gevonden of geen toegang!");
    exit;
}

// Verwijder het slot
$stmtDelete = $pdo->prepare("DELETE FROM time_slots WHERE id = ? AND instructor_id = ?");
$stmtDelete->execute([$slot_id, $instructor_id]);

    header('Location: ../../index.php?home&panel=Driving_Instructor');
exit;
?>
