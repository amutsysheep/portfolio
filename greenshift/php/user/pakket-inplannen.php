<?php
session_start();
include "../../connection/connection.php";

$user_id = $_SESSION['userid'] ?? null;
$student_package_id = $_POST['student_package_id'] ?? null;
$instructor_id = $_POST['instructor_id'] ?? null;
$preferred_day = $_POST['preferred_day'] ?? null;
$preferred_time = $_POST['preferred_time'] ?? null;
$note = $_POST['note'] ?? '';

if (!$user_id || !$student_package_id || !$instructor_id || !$preferred_day || !$preferred_time) {
    $_SESSION['message'] = "Ontbrekende gegevens.";
    header("Location: ../../index.php?page=pakket-inplannen&error=1");
    exit;
}

$preferred_day = date('Y-m-d', strtotime($preferred_day));
if (strlen($preferred_time) === 5) $preferred_time .= ':00'; // bv. "14:00" → "14:00:00"

try {
    $pdo->beginTransaction();


    $stmt = $pdo->prepare("SELECT id, student_id, package_id, remaining_lessons FROM student_packages WHERE id = ? LIMIT 1");
    $stmt->execute([$student_package_id]);
    $sp = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sp) throw new Exception("Pakket niet gevonden.");
    if ((int)$sp['student_id'] !== (int)$user_id) throw new Exception("Dit pakket hoort niet bij jouw account.");
    if ((int)$sp['remaining_lessons'] <= 0) throw new Exception("Geen resterende lessen meer in dit pakket.");


    $stmt = $pdo->prepare("
        SELECT id FROM time_slots 
        WHERE date = ? AND hour_start = ? AND instructor_id = ? AND available = 1 
        LIMIT 1
    ");
    $stmt->execute([$preferred_day, $preferred_time, $instructor_id]);
    $time_slot_id = $stmt->fetchColumn();

    if (!$time_slot_id) throw new Exception("Geen beschikbaar tijdslot gevonden.");


    $stmt = $pdo->prepare("SELECT addressid FROM user WHERE userid = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $addressid = $stmt->fetchColumn();
    if (!$addressid) throw new Exception("Geen adres gevonden voor deze gebruiker.");


    $stmt = $pdo->prepare("
        INSERT INTO lesson_reservations
        (student_id, instructor_id, addressid, time_slot_id, package_id, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $instructor_id, $addressid, $time_slot_id, $sp['package_id']]);


    $stmt = $pdo->prepare("UPDATE student_packages SET remaining_lessons = remaining_lessons - 1 WHERE id = ?");
    $stmt->execute([$student_package_id]);


    $stmt = $pdo->prepare("UPDATE time_slots SET available = 0 WHERE id = ?");
    $stmt->execute([$time_slot_id]);

    $pdo->commit();

    header("Location: ../../index.php?page=pakket-inplannen-succes&selected_day=" . urlencode($preferred_day) . "&selected_time=" . urlencode($preferred_time) . "&instructor_id=" . urlencode($instructor_id));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    // Debug: toon de foutmelding direct
    echo "<h2>Fout opgetreden:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>";
    echo "POST-data: " . print_r($_POST, true) . "\n";
    echo "SESSION-data: " . print_r($_SESSION, true);
    echo "</pre>";
    exit;
}
