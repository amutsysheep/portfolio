<?php
$selected_day = $_GET["selected_day"] ?? '';
$selected_time = $_GET["selected_time"] ?? '';
$start_time = explode('-', $selected_time)[0] ?? null;

// Gebruiker ophalen
$user_id = $_SESSION['userid'] ?? null;

$first_name = '';
$last_name = '';
$email = '';
$postalcode = '';
$housenumber = '';
$address = '';
$city = '';

if ($user_id) {
    // Haal adres + naam + e-mail in één query op via de koppeling met addressid
    $stmt = $pdo->prepare("
        SELECT 
            a.first_name, 
            a.last_name, 
            a.postalcode, 
            a.housenumber, 
            a.address, 
            a.city, 
            u.email
        FROM user u
        JOIN address a ON u.addressid = a.addressid
        WHERE u.userid = :userid
        LIMIT 1
    ");
    $stmt->execute([':userid' => $user_id]);
    $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_row) {
        $first_name = $user_row['first_name'];
        $last_name = $user_row['last_name'];
        $email = $user_row['email'];
        $postalcode = $user_row['postalcode'];
        $housenumber = $user_row['housenumber'];
        $address = $user_row['address'];
        $city = $user_row['city'];
    }
}

// Haal beschikbare instructeurs op
$instructors = [];
if ($selected_day && $start_time) {
    $parts = explode(' ', $selected_day);
    $date_formatted = date('Y-m-d', strtotime($parts[1] ?? $selected_day));

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

            <form action="php/user/single-lesson.php" method="POST" class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Voornaam</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($first_name); ?>" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Achternaam</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($last_name); ?>" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email); ?>" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Adres</label>
                    <input type="text" name="street" value="<?= htmlspecialchars($address); ?>" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Huisnummer</label>
                    <input type="text" name="house_number" value="<?= htmlspecialchars($housenumber); ?>" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Plaats</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($city); ?>" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Postcode</label>
                    <input type="text" name="postcode" value="<?= htmlspecialchars($postalcode); ?>" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Rijinstructeur</label>
                    <select name="instructor_id" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <?php foreach ($instructors as $instr): ?>
                            <option value="<?= htmlspecialchars($instr['instructor_id']); ?>">
                                <?= htmlspecialchars($instr['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Opmerking (optioneel)</label>
                    <textarea name="note" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 focus:border-green-500 focus:ring-2 focus:ring-green-200"></textarea>
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
    document.querySelector('form').addEventListener('submit', function() {
        const selectedDayInput = document.getElementById('preferred_day');
        const dayText = selectedDayInput.value;
        const dateParts = dayText.match(/\d{2}-\d{2}-\d{4}/);
        if (dateParts) {
            const parts = dateParts[0].split('-');
            selectedDayInput.value = `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
    });
</script>
