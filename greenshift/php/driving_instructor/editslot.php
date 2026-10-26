<?php
session_start();
include "../../connection/connection.php";

if (!isset($_SESSION['userid'])) {
    header("Location: ../../login.php");
    exit;
}

$instructor_id = $_SESSION['userid'];

// Ontvang de POST-data
$slot_id = $_POST['slot_id'] ?? null;
$date = $_POST['date'] ?? null;
$time_slot = $_POST['time_slot'] ?? null;

if (!$slot_id || !$date || !$time_slot) {
    die("Ongeldige invoer!");
}

// Splits het tijdslot in start- en eindtijd
list($hour_start, $hour_end) = explode('-', $time_slot);

// Werk het tijdslot bij in de database
$stmt = $pdo->prepare("UPDATE time_slots SET date = ?, hour_start = ?, hour_end = ? WHERE id = ? AND instructor_id = ?");
$stmt->execute([$date, $hour_start, $hour_end, $slot_id, $instructor_id]);

// Terug naar het dashboard met melding
header("Location: ../../index.php?home&panel=Driving_Instructor&message=Tijdslot bijgewerkt: $date ($hour_start-$hour_end)");
exit;
?>
