<?php
include "./connection/connection.php";

$user_id = $_SESSION['userid'];

$stmtInstructor = $pdo->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmtInstructor->execute([$user_id]);
$instructor = $stmtInstructor->fetch(PDO::FETCH_ASSOC);

if (!$instructor) {
    die("Geen instructeur gevonden voor deze gebruiker.");
}

$instructor_id = $instructor['instructor_id'];

// Haal beschikbaarheid op van de ingelogde instructeur
$stmt = $pdo->prepare("SELECT id, date, hour_start, hour_end FROM time_slots WHERE instructor_id = ? ORDER BY date, hour_start");
$stmt->execute([$instructor_id]);
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Feedback van form
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="flex-1 overflow-auto bg-white min-h-screen p-6 space-y-6">

    
    

    <!-- Stats Cards -->
  <div class="flex flex-wrap justify-center gap-6">
    <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-6 flex flex-col items-center justify-center w-40 h-40">
        <div class="p-3 rounded-full bg-green-100 text-green-600 mb-2">
            <i class="fas fa-book-open text-2xl"></i>
        </div>
        <p class="text-sm text-gray-500 text-center">Geplande lessen</p>
        <h3 class="text-2xl font-bold text-gray-800">12</h3>
    </div>

    <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-6 flex flex-col items-center justify-center w-40 h-40">
        <div class="p-3 rounded-full bg-green-100 text-green-600 mb-2">
            <i class="fas fa-users text-2xl"></i>
        </div>
        <p class="text-sm text-gray-500 text-center">Actieve leerlingen</p>
        <h3 class="text-2xl font-bold text-gray-800">8</h3>
    </div>

    <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-6 flex flex-col items-center justify-center w-40 h-40">
        <div class="p-3 rounded-full bg-green-100 text-green-600 mb-2">
            <i class="far fa-calendar-alt text-2xl"></i>
        </div>
        <p class="text-sm text-gray-500 text-center">Beschikbare uren</p>
        <h3 class="text-2xl font-bold text-gray-800">24</h3>
    </div>
</div>


    <!-- Grid voor Beschikbaarheid & Geplande Lessen -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Beschikbaarheid Toevoegen -->
        <div class="bg-white rounded-xl shadow-md p-6 max-w-3xl mx-auto">
            <h3 class="text-lg font-semibold mb-4">Beschikbaarheid instellen</h3>

            <?php if($success): ?>
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="php/driving_instructor/addavailability.php" method="POST">
                <!-- Datum -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Datum</label>
                    <input type="date" name="date" class="w-full p-2 border rounded-lg" required>
                </div>

                <!-- Tijdslots -->
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Tijdsblok</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm text-gray-700">
                        <?php
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
                        foreach ($default_slots as $slot_time): ?>
                            <label class="flex items-center border border-gray-300 rounded-lg p-2 cursor-pointer hover:bg-green-50">
                                <input type="checkbox" name="time_slots[]" value="<?= $slot_time ?>" class="mr-2"> <?= $slot_time ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-medium">
                    Beschikbaarheid opslaan
                </button>
            </form>
        </div>

        <!-- Beschikbaarheid Overzicht -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition">
    <div class="px-4 py-3 border-b">
        <h3 class="font-semibold text-lg text-gray-800">Mijn Beschikbaarheid</h3>
    </div>
    <div class="p-4 overflow-x-auto">
        <?php
        if ($slots):
            // Beschikbaarheid groeperen per datum
            $grouped_slots = [];
            foreach ($slots as $slot) {
                $grouped_slots[$slot['date']][] = $slot;
            }
        ?>

        <table class="w-full text-sm text-gray-700 border-collapse">
            <thead class="bg-green-50">
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2 px-3">Datum</th>
                    <th class="py-2 px-3">Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grouped_slots as $date => $day_slots): ?>
                    <?php 
                        $day_name = date('l', strtotime($date)); // Dagnaam
                    ?>
                    <tr class="border-b hover:bg-green-50 transition cursor-pointer" onclick="toggleDay('day-<?= $date ?>')">
                        <td class="py-3 px-3 font-medium text-gray-800">
                            <?= htmlspecialchars($day_name . ' ' . date('d-m-Y', strtotime($date))) ?>
                        </td>
                        <td class="py-3 px-3 text-gray-500 text-sm">Klik om te bekijken</td>
                    </tr>

                    <!-- Verborgen rijen per dag -->
                    <tr id="day-<?= $date ?>" class="hidden bg-gray-50">
                        <td colspan="2" class="p-0">
                            <table class="w-full text-sm border-t">
                                <thead class="bg-green-100 text-gray-600">
                                    <tr>
                                        <th class="py-2 px-3">Tijdsblok</th>
                                        <th class="py-2 px-3">Status</th>
                                        <th class="py-2 px-3">Acties</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($day_slots as $slot): ?>
                                        <?php 
                                            $time_block = $slot['hour_start'] . " - " . $slot['hour_end'];
                                            $status = 'Actief';
                                            $status_color = 'bg-green-100 text-green-800';
                                        ?>
                                        <tr class="border-b hover:bg-green-50 transition">
                                            <td class="py-2 px-3"><?= htmlspecialchars($time_block) ?></td>
                                            <td class="py-2 px-3">
                                                <span class="text-xs px-2 py-1 rounded <?= $status_color ?>"><?= $status ?></span>
                                            </td>
                                            <td class="py-2 px-3 flex space-x-3">
                                                <a href="index.php?page=editslot&panel=Driving_Instructor&slot_id=<?= $slot['id'] ?>" 
                                                   class="text-blue-600 hover:text-blue-800 transition" title="Bewerken">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form action="php/driving_instructor/deleteavailability.php" method="POST" onsubmit="return confirm('Weet je zeker dat je dit wilt verwijderen?');">
                                                    <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-800 transition" title="Verwijderen">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php else: ?>
            <p class="text-center text-gray-500 py-3">Geen beschikbaarheid toegevoegd</p>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDay(id) {
    const row = document.getElementById(id);
    row.classList.toggle('hidden');
}
</script>

    </div>
</div>
