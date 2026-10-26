<?php
include "./connection/connection.php";

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['userid'])) {
    header("Location: ../../login.php");
    exit;
}

$instructor_id = $_SESSION['userid'];

// Ophalen slot uit database
$slot_id = $_GET['slot_id'] ?? null;

if (!$slot_id) {
    die("Geen slot geselecteerd!");
}

$stmt = $pdo->prepare("SELECT * FROM time_slots WHERE id = ? AND instructor_id = ?");
$stmt->execute([$slot_id, $instructor_id]);
$slot = $stmt->fetch();

if (!$slot) {
    die("Tijdslot niet gevonden of geen toegang!");
}

// Default tijdslots
$default_slots = [
    '08:00-09:00',
    '09:00-10:00',
    '10:00-11:00',
    '11:00-12:00',
    '12:00-13:00',
    '13:00-14:00',
    '14:00-15:00',
    '15:00-16:00',
    '16:00-17:00',
    '17:00-18:00',
    '18:00-19:00',
    '19:00-20:00'
];
?>

<div class="flex justify-center items-start min-h-screen p-6 bg-gray-100">
    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-4xl h-full overflow-auto">
        <h2 class="text-3xl font-bold mb-6 text-gray-800 text-center">Tijdslot bewerken</h2>

        <!-- Geselecteerde tijd en datum tonen -->
        <div class="text-center mb-8">
            <p class="text-lg text-gray-700">
                <strong>Huidige datum:</strong> <?= htmlspecialchars($slot['date']) ?><br>
                <strong>Huidig tijdslot:</strong> <?= htmlspecialchars($slot['hour_start'] . '-' . $slot['hour_end']) ?>
            </p>
        </div>

        <form action="php/driving_instructor/editslot.php" method="POST" class="space-y-6 h-full flex flex-col justify-between">
            <input type="hidden" name="slot_id" value="<?= htmlspecialchars($slot['id']) ?>">

            <div class="space-y-6">
                <!-- Datum -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Datum</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($slot['date']) ?>" class="w-full border border-gray-300 rounded-lg p-3" required>
                </div>

                <!-- Tijdslots -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Tijdsblok</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <?php foreach ($default_slots as $slot_time): 
                            $selected = ($slot['hour_start'].'-'.$slot['hour_end'] == $slot_time) ? 'checked' : '';
                        ?>
                            <label class="flex items-center space-x-2 border border-gray-300 rounded-lg p-3 cursor-pointer hover:bg-green-50">
                                <input type="radio" name="time_slot" value="<?= $slot_time ?>" <?= $selected ?> required>
                                <span class="text-gray-700"><?= $slot_time ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Knoppen -->
            <div class="flex justify-between mt-8">
                <a href="../../index.php?home&panel=Driving_Instructor" 
                   class="px-6 py-3 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition">Annuleren</a>
                <button type="submit" 
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Opslaan</button>
            </div>
        </form>
    </div>
</div>
