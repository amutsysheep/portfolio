<?php
if (!isset($_SESSION)) session_start();
include "connection/connection.php";

$user_id = $_SESSION['userid'] ?? null;

if (!$user_id) {
    echo "<div class='p-6 text-center text-red-600'>Je moet ingelogd zijn om lessen in te plannen.</div>";
    exit;
}

// Haal ALLE pakketten op
$stmt = $pdo->prepare("
    SELECT sp.id AS student_package_id, lp.name, lp.number_of_lessons, lp.price, 
           sp.remaining_lessons, sp.start_date
    FROM student_packages sp
    JOIN lesson_packages lp ON sp.package_id = lp.id
    WHERE sp.student_id = ?
    ORDER BY sp.start_date ASC
");
$stmt->execute([$user_id]);
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Zoek het eerstvolgende actieve pakket
$active_package = null;
foreach ($packages as $pkg) {
    if ($pkg['remaining_lessons'] > 0) {
        $active_package = $pkg;
        break;
    }
}
?>

<div class="container mx-auto max-w-2xl mt-10 mb-20 p-6 bg-white rounded-2xl shadow-md">
    <h2 class="text-2xl font-semibold text-center mb-6">Pakketlessen Inplannen</h2>

    <?php if (empty($packages)): ?>
        <p class="text-center text-gray-600">
            Je hebt momenteel geen lespakketten.<br>
            <a href="#" class="text-green-600 font-semibold underline">Bekijk onze lespakketten</a>
        </p>
    <?php else: ?>
        <div class="space-y-4 mb-8">
            <?php foreach ($packages as $pkg): ?>
                <div class="border rounded-xl p-4 <?= $pkg['remaining_lessons'] > 0 ? 'bg-gray-50' : 'bg-gray-100 opacity-60' ?>">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-lg font-semibold"><?= htmlspecialchars($pkg['name']) ?></h3>
                            <p>💰 €<?= number_format($pkg['price'], 2) ?></p>
                            <p>📦 <?= $pkg['number_of_lessons'] ?> lessen</p>
                        </div>
                        <div class="text-right">
                            <p>📅 Gestart: <?= date('d-m-Y', strtotime($pkg['start_date'])) ?></p>
                            <p>🕐 Resterend:
                                <span class="<?= $pkg['remaining_lessons'] > 0 ? 'text-green-600' : 'text-gray-500' ?> font-semibold">
                                    <?= $pkg['remaining_lessons'] ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$active_package): ?>
            <p class="text-center text-gray-600">
                Alle pakketten zijn volledig ingepland. 🎉
            </p>
        <?php else: ?>
            <div class="border-t border-gray-200 my-6"></div>

            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6">
                <h3 class="text-lg font-semibold mb-2">
                    Actief pakket: <?= htmlspecialchars($active_package['name']) ?>
                </h3>
                <p>🕐 Nog te plannen:
                    <span class="font-semibold text-green-600">
                        <?= $active_package['remaining_lessons'] ?>
                    </span>
                </p>
            </div>

            <form action="php/user/pakket-inplannen.php" method="POST" class="space-y-5">
                <input type="hidden" name="student_package_id" value="<?= $active_package['student_package_id'] ?>">

                <div>
                    <label for="preferred_day" class="block font-medium mb-1">Datum</label>
                    <input type="date" id="preferred_day" name="preferred_day"
                           class="w-full border rounded-lg p-2"
                           min="<?= date('Y-m-d') ?>" required>
                </div>

                <div>
                    <label for="preferred_time" class="block font-medium mb-1">Tijdslot</label>
                    <select id="preferred_time" name="preferred_time"
                            class="w-full border rounded-lg p-2" disabled required>
                        <option value="">Selecteer eerst een datum</option>
                    </select>
                </div>

                <div>
                    <label for="instructor_id" class="block font-medium mb-1">Instructeur</label>
                    <select name="instructor_id" id="instructor_id"
                            class="w-full border rounded-lg p-2" disabled required>
                        <option value="">Selecteer eerst een tijdslot</option>
                    </select>
                </div>

                <div>
                    <label for="note" class="block font-medium mb-1">Opmerking (optioneel)</label>
                    <textarea name="note" id="note" rows="3"
                              class="w-full border rounded-lg p-2"
                              placeholder="Bijv. voorkeur leslocatie, speciale wensen..."></textarea>
                </div>

                <button type="submit"
                        class="w-full bg-green-600 text-white font-semibold py-2 rounded-lg hover:bg-green-700 transition">
                    Les inplannen
                </button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>


<script>
    document.addEventListener("DOMContentLoaded", () => {
        const dateInput = document.getElementById("preferred_day");
        const timeSelect = document.getElementById("preferred_time");
        const instructorSelect = document.getElementById("instructor_id");

        async function loadTimeSlots() {
            const date = dateInput.value;
            if (!date) return;

            timeSelect.disabled = true;
            instructorSelect.disabled = true;
            timeSelect.innerHTML = "<option>Laden...</option>";

            const response = await fetch("php/user/available-slots.php");
            const data = await response.json();

            const dayKey = Object.keys(data).find(k => k.includes(date.split('-').reverse().join('-'))); // match dd-mm-yyyy

            timeSelect.innerHTML = "";

            if (dayKey && data[dayKey].length > 0) {
                data[dayKey].forEach(slot => {
                    const opt = document.createElement("option");
                    opt.value = slot.hour_start; // HH:MM:SS
                    opt.textContent = slot.hour_start.slice(0,5) + " - " + slot.hour_end.slice(0,5);
                    timeSelect.appendChild(opt);
                });
                timeSelect.disabled = false;


                if (data[dayKey].length === 1) {
                    timeSelect.value = data[dayKey][0].hour_start;
                    await loadInstructors();
                }

            } else {
                timeSelect.innerHTML = "<option>Geen tijden beschikbaar</option>";
            }
        }

        async function loadInstructors() {
            const date = dateInput.value;
            const time = timeSelect.value;
            if (!date || !time) return;

            instructorSelect.disabled = true;
            instructorSelect.innerHTML = "<option>Laden...</option>";

            const response = await fetch(`php/user/available-instructors.php?date=${date}&time=${time}`);
            const instructors = await response.json();

            instructorSelect.innerHTML = "";
            if (instructors.length === 0) {
                instructorSelect.innerHTML = "<option>Geen instructeurs beschikbaar</option>";
            } else {
                instructors.forEach(i => {
                    const opt = document.createElement("option");
                    opt.value = i.instructor_id;
                    opt.textContent = i.name;
                    instructorSelect.appendChild(opt);
                });
                instructorSelect.disabled = false;
            }
        }

        dateInput.addEventListener("change", loadTimeSlots);
        timeSelect.addEventListener("change", loadInstructors);

    });

        document.querySelector("form").addEventListener("submit", () => {
            document.getElementById("instructor_id").disabled = false;
            document.getElementById("preferred_time").disabled = false;
        });


</script>
