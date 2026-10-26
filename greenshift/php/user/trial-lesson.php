<?php
session_start();
include "../../connection/connection.php";

$name = ($_POST['name'] ?? '');
$last_name = ($_POST['last_name'] ?? '');
$email = ($_POST['email'] ?? '');
$phone = ($_POST['phone'] ?? '');
$postcode = ($_POST['postcode'] ?? '');
$house_number = ($_POST['house_number'] ?? '');
$street = ($_POST['street'] ?? '');
$city = ($_POST['city'] ?? '');
$preferred_day = ($_POST['preferred_day'] ?? '');
$preferred_time = ($_POST['preferred_time'] ?? '');
$note = ($_POST['note'] ?? '');
$instructor_id = ($_POST['instructor_id'] ?? null);

$errors = [];

// Zorg dat $preferred_day in YYYY-MM-DD formaat staat
//if (!empty($preferred_day)) {
//    $preferred_day = date('Y-m-d', strtotime($preferred_day));
//}

// Zorg dat $preferred_time een volledig tijdformaat heeft
if (!empty($preferred_time)) {
    if (strlen($preferred_time) <= 5) {
        $preferred_time .= ':00';
    }
} else {
    $preferred_time = null;
}

// Starttijd uit preferred_time halen
$start_time = explode('-', $preferred_time)[0] ?? null;

// Check dubbele reservering
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM trial_requests 
    WHERE postcode = ? 
      AND house_number = ? 
      AND preferred_day = ? 
      AND preferred_time = ?
");
$stmt->execute([$postcode, $house_number, $preferred_day, $preferred_time]);
if ($stmt->fetchColumn() > 0) {
    $errors[] = "Er is al een reservering voor dit adres op dit tijdslot.";
}

if (!empty($errors)) {
    $_SESSION['message'] = implode("<br>", $errors);
    header("Location: ../index.php?page=trial-lesson-info-form&postcode=$postcode&huisnummer=$house_number&selected_day=$preferred_day&selected_time=$preferred_time");
    exit;
}

try {
    $pdo->beginTransaction();

    // Voeg de proefles toe
    $stmt = $pdo->prepare("
        INSERT INTO trial_requests 
        (name, last_name, email, phone, postcode, house_number, street, city, preferred_day, preferred_time, note, instructor_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$name, $last_name, $email, $phone, $postcode, $house_number, $street, $city, $preferred_day, $preferred_time, $note, $instructor_id]);

    // Zet het tijdslot op niet beschikbaar
    $stmt = $pdo->prepare("
        UPDATE time_slots 
        SET available = 0 
        WHERE instructor_id = ? 
          AND date = ? 
          AND hour_start = ?
    ");
    $stmt->execute([$instructor_id, $preferred_day, $start_time]);

    $pdo->commit();

    header("Location: ../../index.php?page=trial-lesson-succes&selected_day=" . urlencode($preferred_day) . "&selected_time=" . urlencode($preferred_time) . "&instructor_id=" . urlencode($instructor_id));
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['message'] = "Er is een fout opgetreden: " . $e->getMessage();
    header("Location: ../../index.php?page=trial-lesson-info-form&postcode=$postcode&huisnummer=$house_number&selected_day=$preferred_day&selected_time=$preferred_time");
    exit;
}
?>
