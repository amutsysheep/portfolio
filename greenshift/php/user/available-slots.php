<?php
include "../../connection/connection.php";

$stmt = $pdo->prepare("
    SELECT date, hour_start, hour_end
    FROM time_slots
    WHERE available = 1
    ORDER BY date, hour_start
");
$stmt->execute();
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped = [];

// Dagnaam vertaling naar NL
$days_nl = [
    'Monday' => 'Maandag',
    'Tuesday' => 'Dinsdag',
    'Wednesday' => 'Woensdag',
    'Thursday' => 'Donderdag',
    'Friday' => 'Vrijdag',
    'Saturday' => 'Zaterdag',
    'Sunday' => 'Zondag'
];

foreach ($slots as $slot) {
    // Datum + dagnaam combineren
    $timestamp = strtotime($slot['date']);
    $day_en = date('l', $timestamp); // Engelse dagnaam
    $day_nl = $days_nl[$day_en] ?? $day_en;
    $day_label = substr($day_nl, 0, 2) . ' ' . date('d-m-Y', $timestamp); // bijv. Di 21-10-2025

    if (!isset($grouped[$day_label])) {
        $grouped[$day_label] = [];
    }

    $grouped[$day_label][] = [
        'hour_start' => $slot['hour_start'],
        'hour_end' => $slot['hour_end']
    ];
}

header('Content-Type: application/json');
echo json_encode($grouped);
