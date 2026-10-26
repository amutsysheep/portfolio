<?php

$selected_day = $_GET["selected_day"] ?? '';
$selected_time = $_GET["selected_time"] ?? '';

$start_time = explode('-', $selected_time)[0];

$instructors = [];
if ($selected_day && $start_time) {
    $parts = explode(' ', $selected_day);
    $date_formatted = date('Y-m-d', strtotime($parts[1]));

    $stmt = $pdo->prepare("
        SELECT i.instructor_id, i.name
        FROM time_slots t
        JOIN instructors i ON t.instructor_id = i.instructor_id
        WHERE t.date = :date
        AND t.hour_start = :start_time
        AND t.available = 1
    ");
    $stmt->execute([
        ':date' => $date_formatted,
        ':start_time' => $start_time
    ]);
    $instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_SESSION['message'])) {
    echo "<p style='color:red'>" . $_SESSION['message'] . "</p>";
    unset($_SESSION['message']);
}
?>

<div class="min-h-screen bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center p-4">
    <div class="w-full max-w-3xl">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8">

            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>

            <h1 class="mt-6 text-center text-3xl font-extrabold text-green-700">
                Proefles boeken
            </h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                Vul je gegevens in voor een proefles
            </p>

            <div class="mt-6 mb-6 bg-green-50 border border-green-200 p-4 rounded-lg text-green-700 text-sm">
                <p>
                    <strong>Gekozen dag:</strong> <?= htmlspecialchars($selected_day ?: 'Onbekend'); ?><br>
                    <strong>Gekozen tijd:</strong> <?= htmlspecialchars($selected_time ?: 'Onbekend'); ?>
                </p>
            </div>

            <form action="php/user/trial-lesson.php" method="POST" class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Voornaam</label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Achternaam</label>
                    <input type="text" name="last_name" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" name="email" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefoonnummer</label>
                    <input type="tel" name="phone" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Postcode</label>
                    <input type="text" name="postcode" value="<?= htmlspecialchars($_GET['postcode'] ?? ''); ?>" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Huisnummer</label>
                    <input type="text" name="house_number" value="<?= htmlspecialchars($_GET['huisnummer'] ?? ''); ?>" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Straat</label>
                    <input type="text" name="street" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Plaats</label>
                    <input type="text" name="city" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Rijinstructeur</label>
                    <select name="instructor_id" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <?php foreach ($instructors as $instr): ?>
                            <option value="<?= htmlspecialchars($instr['instructor_id']); ?>">
                                <?= htmlspecialchars($instr['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Opmerking (optioneel)</label>
                    <textarea name="note" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 shadow-sm placeholder-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-200"></textarea>
                </div>

                <input type="hidden" name="preferred_day" id="preferred_day" value="<?= htmlspecialchars($selected_day); ?>">
                <input type="hidden" name="preferred_time" value="<?= htmlspecialchars($selected_time); ?>">

                <div class="md:col-span-2 mt-4">
                    <button type="submit" class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                        Verstuur aanvraag
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        var selectedDayInput = document.getElementById('preferred_day');
        var dayText = selectedDayInput.value;


        var dateParts = dayText.match(/\d{2}-\d{2}-\d{4}/);
        if (dateParts) {
            var parts = dateParts[0].split('-');
            var yyyy = parts[2], mm = parts[1], dd = parts[0];
            selectedDayInput.value = yyyy + '-' + mm + '-' + dd;
        }
    });
</script>
