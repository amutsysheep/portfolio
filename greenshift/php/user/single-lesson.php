<?php
session_start();
include "../../connection/connection.php";


$user_id = $_SESSION['userid'] ?? null;
$instructor_id = ($_POST['instructor_id'] ?? null);
$preferred_day = ($_POST['preferred_day'] ?? '');
$preferred_time = ($_POST['preferred_time'] ?? '');
$note = ($_POST['note'] ?? '');


if (empty($user_id) || empty($instructor_id) || empty($preferred_day) || empty($preferred_time)) {
    $_SESSION['message'] = "Er ontbreken gegevens om de reservering te maken.";
    header("Location: ../../index.php?page=single-lesson-info-form");
    exit;
}


$preferred_day = date('Y-m-d', strtotime($preferred_day));

$start_time = explode('-', $preferred_time)[0] ?? $preferred_time;

$stmt = $pdo->prepare("SELECT addressid FROM user WHERE userid = ?");
$stmt->execute([$user_id]);
$addressid = $stmt->fetchColumn();

if (!$addressid) {
    $_SESSION['message'] = "Geen adres gevonden voor deze gebruiker.";
    header("Location: ../../index.php?page=single-lesson-info-form");
    exit;
}

$stmt = $pdo->prepare("
    SELECT id 
    FROM time_slots 
    WHERE date = ? 
      AND hour_start = ? 
      AND instructor_id = ? 
      AND available = 1
    LIMIT 1
");
$stmt->execute([$preferred_day, $start_time, $instructor_id]);
$time_slot_id = $stmt->fetchColumn();

if (!$time_slot_id) {
    $_SESSION['message'] = "Geen beschikbaar tijdslot gevonden voor deze instructeur.";
    header("Location: ../../index.php?page=single-lesson-info-form");
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO lesson_reservations 
        (student_id, instructor_id, addressid, time_slot_id, package_id, created_at)
        VALUES (?, ?, ?, ?, NULL, NOW())
    ");
    $stmt->execute([$user_id, $instructor_id, $addressid, $time_slot_id]);

    $stmt = $pdo->prepare("
        UPDATE time_slots 
        SET available = 0 
        WHERE id = ?
    ");
    $stmt->execute([$time_slot_id]);

    $pdo->commit();

    header("Location: ../../index.php?page=single-lesson-succes&selected_day=" . urlencode($preferred_day) . "&selected_time=" . urlencode($preferred_time) . "&instructor_id=" . urlencode($instructor_id));
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = "Er is een fout opgetreden: " . $e->getMessage();
    header("Location: ../../index.php?page=single-lesson-info-form");
    exit;
}
?>
