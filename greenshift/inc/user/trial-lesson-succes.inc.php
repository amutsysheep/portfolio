<?php
$selected_day = $_GET["selected_day"] ?? '';
$selected_time = $_GET["selected_time"] ?? '';
$instructor_id = $_GET["instructor_id"] ?? null;

$instructor_name = 'Word toegewezen';

if ($instructor_id && $selected_day && $selected_time) {
    $start_time = explode('-', $selected_time)[0];

    // Query aanpassen naar date in plaats van day_of_week
    $stmt = $pdo->prepare("
        SELECT i.name 
        FROM instructors i
        JOIN time_slots t ON i.instructor_id = t.instructor_id
        WHERE t.instructor_id = :instructor_id
        AND t.date = :date
        AND t.hour_start = :start_time
        LIMIT 1
    ");
    $stmt->execute([
        ':instructor_id' => $instructor_id,
        ':date' => $selected_day,
        ':start_time' => $start_time
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $instructor_name = $row['name'];
    }

    // Optioneel: formatteer de dag naar bijv. "Di 21-10-2025"
    $formatted_day = date('D d-m-Y', strtotime($selected_day));
} else {
    $formatted_day = $selected_day;
}
?>

<div class="min-h-screen bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center p-4">
    <div class="w-full max-w-3xl">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8 text-center">

            <div class="flex justify-center mb-4">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>

            <div class="text-green-700 text-6xl mb-4 animate-bounce">🎉</div>

            <h1 class="text-3xl font-extrabold text-green-700 mb-2">
                Reservering Gelukt!
            </h1>

            <p class="text-gray-600 mb-6">
                Bedankt voor je reservering. Je proefles is succesvol geboekt.<br>
                Je ontvangt binnenkort een bevestiging per e-mail.
            </p>

            <div class="mb-6 bg-green-50 border border-green-200 p-4 rounded-lg text-green-700 text-sm">
                <p>
                    <strong>Gekozen dag:</strong> <?= htmlspecialchars($formatted_day ?: 'Onbekend'); ?><br>
                    <strong>Gekozen tijd:</strong> <?= htmlspecialchars($selected_time ?: 'Onbekend'); ?><br>
                    <strong>Rijinstructeur:</strong> <?= htmlspecialchars($instructor_name); ?>
                </p>
            </div>

            <a href="index.php?page=home" class="inline-block w-full md:w-auto rounded-lg bg-green-600 py-3 px-6 font-semibold text-white shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300 transition-colors">
                Terug naar Home
            </a>

        </div>
    </div>
</div>
