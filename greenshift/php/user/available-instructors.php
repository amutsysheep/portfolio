<?php
if (!isset($_SESSION)) session_start();
include "../../connection/connection.php";

$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;

header('Content-Type: application/json');

if (!$date || !$time) {
    echo json_encode([]);
    exit;
}

$date = date('Y-m-d', strtotime($date));

// zorg dat tijd in HH:MM:SS is
$time_parts = explode(':', $time);
if(count($time_parts) === 2) {
    $time .= ':00';
}

try {
    $stmt = $pdo->prepare("
        SELECT i.instructor_id, i.name
        FROM instructors i
        JOIN time_slots t ON t.instructor_id = i.instructor_id
        WHERE t.date = :date
          AND t.hour_start = :time
          AND t.available = 1
        ORDER BY i.name ASC
    ");
    $stmt->execute([
        ':date' => $date,
        ':time' => $time
    ]);

    $instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($instructors);

} catch (Exception $e) {
    echo json_encode([]);
}
