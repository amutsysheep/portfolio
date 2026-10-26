<?php
// Toon alle errors (handig tijdens ontwikkeling)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

try {
    // Query uitvoeren (zonder aantal lessen)
    $stmt = $pdo->prepare("
        SELECT 
            u.userid,
            u.firstname,
            u.infix,
            u.lastname,
            u.email,
            u.active,
            a.housenumber,
            a.city,
            a.phonenumber
        FROM user u
        LEFT JOIN address a ON u.addressid = a.addressid
        WHERE u.roleid = 2
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div style='background:#fee2e2;color:#b91c1c;padding:10px;border-radius:8px;margin:10px;'>
            <strong>Databasefout:</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
    exit;
}
?>

<body class="bg-gray-50">
<div class="max-w-5xl mx-auto p-10">

    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl text-green-800 font-bold mb-2">Leerlingen Beheer</h1>
        <p class="text-gray-600">Bekijk, voeg toe, bewerk of verwijder leerlingen.</p>
    </div>

    <?php if ($message): ?>
        <div class="text-black p-2 rounded mt-4 text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Action button -->
    <div class="text-right mb-6">
        <button 
            onclick="window.location.href='index.php?page=addstudent&panel=Admin'"
            class="bg-green-800 hover:bg-green-900 text-white px-5 py-2 rounded-xl text-sm font-medium transition">
            + Nieuwe leerling toevoegen
        </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white rounded-xl shadow-md overflow-hidden">
            <thead>
                <tr class="bg-green-800 text-white text-left">
                    <th class="py-3 px-4">Naam</th>
                    <th class="py-3 px-4">Telefoon</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Adres</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                    <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition">
                        <td class="py-3 px-4">
                            <a href="index.php?page=editstudent&panel=Admin&userid=<?= $student['userid'] ?>" 
                               class="text-green-800 font-medium hover:text-green-900 hover:underline">
                               <?= htmlspecialchars(trim($student['firstname'] . ' ' . $student['infix'] . ' ' . $student['lastname'])) ?>
                            </a>
                        </td>
                        <td class="py-3 px-4"><?= htmlspecialchars($student['phonenumber'] ?? '-') ?></td>
                        <td class="py-3 px-4"><?= htmlspecialchars($student['email'] ?? '-') ?></td>
                        <td class="py-3 px-4">
                            <?= htmlspecialchars(
                                ($student['street'] ?? '-') . ' ' . 
                                ($student['housenumber'] ?? '') . ', ' . 
                                ($student['city'] ?? '-')
                            ) ?>
                        </td>
                        <td class="py-3 px-4">
                            <?php if ($student['active'] == 1): ?>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-lg text-xs font-medium">Actief</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-lg text-xs font-medium">Inactief</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex gap-2">
                                <?php if ($student['active'] == 1): ?>
                                    <a href="php/admin/deletestudent.php?userid=<?= $student['userid'] ?>" 
                                       onclick="return confirm('Weet je zeker dat je deze leerling wilt deactiveren?');"
                                       class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs transition">
                                        Deactiveer
                                    </a>
                                <?php else: ?>
                                    <a href="php/admin/activatestudent.php?userid=<?= $student['userid'] ?>"
                                       onclick="return confirm('Weet je zeker dat je deze leerling wilt activeren?');"
                                       class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-xs transition">
                                        Activeer
                                    </a>
                                <?php endif; ?>
                                
                            </div>
                            <td class="py-3 px-4">
                                <div class="flex gap-2">
                                </div>
                                <td>
                                  <div>
                                    <a href="index.php?page=editstudent&panel=Admin&userid=<?= $student['userid'] ?>" 
                                    class="bg-amber-400 hover:bg-amber-500 text-white px-3 py-1 rounded-lg text-xs transition">
                                        Aanpassen
                                    </a>
                                </div>

                                </td>
                            </td>

                           
                            
                        </td>
                        
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500">Geen leerlingen gevonden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
