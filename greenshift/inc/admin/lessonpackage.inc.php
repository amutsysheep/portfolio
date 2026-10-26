<?php

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Selecteer alle gebruikers met rolid 3 (rij-instructeurs)
$stmt = $pdo->prepare("SELECT lesson_packages.*
                              FROM lesson_packages");
$stmt->execute();
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="max-w-5xl mx-auto p-10">
    <div class="text-center mb-8">
        <h1 class="text-4xl text-green-800 mb-2 font-bold">Lespakketten Beheer</h1>
        <p class="text-gray-600">Bekijk, voeg toe, bewerk of verwijder lespakketten.</p>
    </div>

    <?php if ($message): ?>
        <div class="text-black p-2 rounded mt-4 text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="text-right mb-6">
        <button
                onclick="window.location.href='http://localhost/greenshift/index.php?page=addlessonpackages&panel=Admin'"
                class="bg-green-800 hover:bg-green-900 text-white px-5 py-2 rounded-xl text-sm font-medium transition">
            + Nieuwe instructeur toevoegen
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white rounded-xl shadow-md overflow-hidden">
            <thead>
            <tr class="bg-green-800 text-white text-left">
                <th class="py-3 px-4">Lespakket Naam</th>
                <th class="py-3 px-4">Aantal Lessen</th>
                <th class="py-3 px-4">Prijs</th>
                <th class="py-3 px-4">omschrijving</th>
                <th class="py-3 px-4">Acties</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($instructors as $instr): ?>
                <tr class="odd:bg-white even:bg-green-50 hover:bg-green-100 transition-colors cursor-pointer">
                    <td class="py-3 px-4">
                        <a href="index.php?page=editlessonpackages&panel=Admin&id=<?= $instr['id'] ?>"
                           class="text-green-800 font-medium hover:text-green-900 hover:underline">
                            <?= htmlspecialchars($instr['name']) ?>
                        </a>
                    </td>
                    <td class="py-3 px-4"><?= htmlspecialchars($instr['number_of_lessons']) ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($instr['price']) ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($instr['description']) ?></td>
                    <td class="py-3 px-4 flex gap-2">

                        <!-- Aanpassen knop -->

                        <button
                                onclick="window.location.href='http://localhost/greenshift/index.php?page=editlessonpackages&panel=Admin&id=<?= $instr['id'] ?>'"
                                class="bg-amber-400 hover:bg-amber-500 text-white px-3 py-1 rounded-lg text-xs transition">
                            Aanpassen
                        </button>


                            <a href="php/admin/deletelessonpackages.php?id=<?= $instr['id'] ?>"
                               onclick="return confirm('Weet je zeker dat je deze instructeur wilt Verwijderen?');"
                               class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-xs transition">
                                Verwijderen
                            </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
