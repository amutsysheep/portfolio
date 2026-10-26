<?php
if (!isset($_SESSION)) session_start();
include "connection/connection.php";

$day = $_GET['selected_day'] ?? null;
$time = $_GET['selected_time'] ?? null;
$instructor_id = $_GET['instructor_id'] ?? null;

if (!$day || !$time || !$instructor_id) {
    echo "<div class='text-center p-6 text-red-600'>Er ging iets mis bij het ophalen van de bevestiging.</div>";
    exit;
}

$stmt = $pdo->prepare("SELECT name FROM instructors WHERE instructor_id = ?");
$stmt->execute([$instructor_id]);
$instructor_name = $stmt->fetchColumn() ?: "Onbekende instructeur";
?>

<div class="container mx-auto max-w-lg mt-20 p-6 bg-white rounded-2xl shadow-lg text-center">
    <h2 class="text-2xl font-bold text-green-700 mb-4">✅ Les succesvol ingepland!</h2>

    <div class="text-gray-700 space-y-2">
        <p><strong>📅 Datum:</strong> <?= date('d-m-Y', strtotime($day)) ?></p>
        <p><strong>🕒 Tijd:</strong> <?= substr($time, 0, 5) ?></p>
        <p><strong>👨‍🏫 Instructeur:</strong> <?= htmlspecialchars($instructor_name) ?></p>
    </div>

    <a href="index.php?page=home"
       class="inline-block mt-6 bg-green-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-green-700 transition">
        Naar home
    </a>
</div>
