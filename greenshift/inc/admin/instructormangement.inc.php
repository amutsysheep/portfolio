<?php

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Selecteer alle gebruikers met rolid 3 (rij-instructeurs)
$stmt = $pdo->prepare("SELECT u.*, a.phonenumber 
                       FROM user u
                       LEFT JOIN address a ON u.addressid = a.addressid
                       WHERE u.roleid = 3");
$stmt->execute();
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-5xl mx-auto p-10">
    <div class="text-center mb-8">
        <h1 class="text-4xl text-green-800 mb-2 font-bold">Rij-instructeurs Beheer</h1>
        <p class="text-gray-600">Bekijk, voeg toe, bewerk of verwijder rij-instructeurs.</p>
    </div>

    <?php if ($message): ?>
        <div class="text-black p-2 rounded mt-4 text-center"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>


    <div class="text-right mb-6">
        <button 
            onclick="window.location.href='http://localhost/greenshift/index.php?page=addinstructor&panel=Admin'" 
            class="bg-green-800 hover:bg-green-900 text-white px-5 py-2 rounded-xl text-sm font-medium transition">
            + Nieuwe instructeur toevoegen
        </button>
    </div>

    <div class="overflow-x-auto rounded-xl shadow-md">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-green-800 text-white text-left">
                    <th class="py-3 px-4">Naam</th>
                    <th class="py-3 px-4">Email</th>
                    <th class="py-3 px-4">Telefoon</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($instructors as $instr): ?>
                <tr class="odd:bg-white even:bg-green-50 hover:bg-green-100 transition-colors cursor-pointer">
                    <td class="py-3 px-4">
                        <a href="index.php?page=editinstructor&panel=Admin&userid=<?= $instr['userid'] ?>"
                           class="text-green-800 font-medium hover:text-green-900 hover:underline">
                           <?= htmlspecialchars($instr['firstname'] . ' ' . $instr['infix'] . ' ' . $instr['lastname']) ?>
                        </a>
                    </td>
                    <td class="py-3 px-4"><?= htmlspecialchars($instr['email']) ?></td>
                    <td class="py-3 px-4"><?= htmlspecialchars($instr['phonenumber'] ?? '-') ?></td>
                    <td class="py-3 px-4">
                        <?php if($instr['active'] == 1): ?>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-lg text-xs font-medium">Actief</span>
                        <?php else: ?>
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded-lg text-xs font-medium">Inactief</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-4 flex gap-2">

                        <!-- Aanpassen knop -->

                        <button 
                            onclick="window.location.href='http://localhost/greenshift/index.php?page=editinstructor&panel=Admin&userid=<?= $instr['userid'] ?>'"
                         class="bg-amber-400 hover:bg-amber-500 text-white px-3 py-1 rounded-lg text-xs transition">
                            Aanpassen
                        </button>
                        
                        

                         <!-- Verwijderen knop -->
                             <?php if($instr['active'] == 1): ?>
                            <a href="php/admin/deleteinstructor.php?userid=<?= $instr['userid'] ?>" 
                            onclick="return confirm('Weet je zeker dat je deze instructeur wilt Deactiveren?');"
                            class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-xs transition">
                                Deactiveer
                            </a>
                             <?php else: ?>
                             <a href="php/admin/activeinstructor.php?userid=<?= $instr['userid'] ?> "
                                onclick="return confirm('Weet je zeker dat je deze instructeur wilt Activeren?');"
                                 <button class="bg-green-500 hover:bg-green-400 text-white px-3 py-1 rounded-lg text-xs transition">
                                     Activeer
                                 </button>
                             </a>
                             <?php endif; ?>


                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
