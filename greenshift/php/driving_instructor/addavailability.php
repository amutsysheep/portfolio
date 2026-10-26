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

// Haal de instructor_id op
$user_id = $_SESSION['userid'];
$stmt = $pdo->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmt->execute([$user_id]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$instructor) {
    header("Location: ../../index.php?home&panel=Driving_Instructor&error=Geen+instructeur+gevonden+voor+deze+gebruiker!");
    exit;
}

$instructor_id = $instructor['instructor_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $time_slots = $_POST['time_slots'] ?? [];

    if (!$date || empty($time_slots)) {
        header("Location: ../../index.php?home&panel=Driving_Instructor&error=Selecteer een datum en minstens één tijdsblok!");
        exit;
    }

    $added = 0;
    foreach ($time_slots as $slot) {
        [$hour_start, $hour_end] = explode('-', $slot);

        // Controleer of deze slot al bestaat
        $stmtCheck = $pdo->prepare("SELECT id FROM time_slots WHERE instructor_id = ? AND date = ? AND hour_start = ? AND hour_end = ?");
        $stmtCheck->execute([$instructor_id, $date, $hour_start, $hour_end]);

        if ($stmtCheck->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO time_slots (instructor_id, date, hour_start, hour_end, available, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
            $stmt->execute([$instructor_id, $date, $hour_start, $hour_end]);
            $added++;
        }
    }

    if ($added > 0) {
        header("Location: ../../index.php?home&panel=Driving_Instructor&success={$added}+tijdslot(s)+toegevoegd!");
    } else {
        header("Location: ../../index.php?home&panel=Driving_Instructor&error=Geen nieuwe tijdslots toegevoegd (mogelijk al aanwezig)!");
    }
    exit;
}
?>
