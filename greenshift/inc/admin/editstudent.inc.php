<?php
session_start();
include "../../connection/connection.php";

$studentId = $_GET['userid'] ?? null;

if (!$studentId) {
    echo "Student ID not provided.";
    exit();
}

$stmt = $pdo->prepare("
    SELECT u.*, 
           a.address, a.housenumber, a.city AS residence, a.postalcode AS zipcode, a.phonenumber
    FROM user u
    LEFT JOIN address a ON u.addressid = a.addressid
    WHERE u.userid = :studentId
    LIMIT 1
");
$stmt->execute([':studentId' => $studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found.";
    exit();
}
?>


<body>
<div class="min-h-screen bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8 space-y-6">

            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>

            <!-- Heading -->
            <div class="text-center">
                <h1 class="text-3xl font-extrabold text-gray-900">Leerling Bewerken</h1>
                <p class="mt-1 text-sm text-gray-600">Pas de gegevens van de leerling hieronder aan</p>
            </div>

            <!-- Form -->
            <form method="POST" action="php/admin/editstudent.php?userid=<?= $studentId ?>" class="space-y-5">

                <!-- Personal info -->
                <div class="space-y-4">
                    <div>
                        <label for="firstname" class="block text-sm font-medium text-gray-700">Voornaam</label>
                        <input id="firstname" name="firstname" type="text" required
                               value="<?= htmlspecialchars($student['firstname'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div>
                        <label for="infix" class="block text-sm font-medium text-gray-700">Tussenvoegsel</label>
                        <input id="infix" name="infix" type="text"
                               value="<?= htmlspecialchars($student['infix'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div>
                        <label for="lastname" class="block text-sm font-medium text-gray-700">Achternaam</label>
                        <input id="lastname" name="lastname" type="text" required
                               value="<?= htmlspecialchars($student['lastname'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                </div>

                <!-- Login info -->
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-mailadres</label>
                        <input id="email" name="email" type="email" required
                               value="<?= htmlspecialchars($student['email'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                </div>

                <!-- Address -->
                <div class="space-y-4">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Adres</label>
                        <input id="address" name="address" type="text" required
                               value="<?= htmlspecialchars($student['address'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div>
                        <label for="phonenumber" class="block text-sm font-medium text-gray-700">Telefoonnummer</label>
                        <input id="phonenumber" name="phonenumber" type="text"
                               value="<?= htmlspecialchars($student['phonenumber'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="zipcode" class="block text-sm font-medium text-gray-700">Postcode</label>
                            <input id="zipcode" name="zipcode" type="text" required
                                   value="<?= htmlspecialchars($student['zipcode'] ?? '') ?>"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                          focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        </div>
                        <div>
                            <label for="residence" class="block text-sm font-medium text-gray-700">Woonplaats</label>
                            <input id="residence" name="residence" type="text" required
                                   value="<?= htmlspecialchars($student['residence'] ?? '') ?>"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                          focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        </div>
                    </div>
                    <div>
                        <label for="housenumber" class="block text-sm font-medium text-gray-700">Huisnummer</label>
                        <input id="housenumber" name="housenumber" type="text" required
                               value="<?= htmlspecialchars($student['housenumber'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg
                               hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                    Wijzigingen opslaan
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
