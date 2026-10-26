<?php

$instructorId = $_GET['userid'] ?? null;

if (!$instructorId) {
    echo "Instructor ID not provided.";
    exit();
}

$stmt = $pdo->prepare("
    SELECT u.*, 
           a.address, a.housenumber, a.city AS residence, a.postalcode AS zipcode, a.phonenumber
    FROM user u
    LEFT JOIN address a ON u.addressid = a.addressid
    WHERE u.userid = :instructorId
    LIMIT 1
");
$stmt->execute([':instructorId' => $instructorId]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$instructor) {
    echo "Instructor not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Driving Instructor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="min-h-screen bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8 space-y-6">

             <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>

            <!-- Heading -->
            <div class="text-center">
                <h1 class="text-3xl font-extrabold text-gray-900">Edit Driving Instructor</h1>
                <p class="mt-1 text-sm text-gray-600">Update the instructor's details below</p>
            </div>

            <!-- Form -->
<form method="POST" action="php/admin/editinstructor.php?userid=<?= $instructorId ?>" class="space-y-5">

                <!-- Personal info -->
                <div class="space-y-4">
                    <div>
                        <label for="firstname" class="block text-sm font-medium text-gray-700">First name</label>
                        <input id="firstname" name="firstname" type="text" required
                               value="<?= htmlspecialchars($instructor['firstname'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div>
                        <label for="infix" class="block text-sm font-medium text-gray-700">Infix</label>
                        <input id="infix" name="infix" type="text"
                               value="<?= htmlspecialchars($instructor['infix'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div>
                        <label for="lastname" class="block text-sm font-medium text-gray-700">Last name</label>
                        <input id="lastname" name="lastname" type="text" required
                               value="<?= htmlspecialchars($instructor['lastname'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                </div>

                <!-- Login info -->
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input id="email" name="email" type="email" required
                               value="<?= htmlspecialchars($instructor['email'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>

                <!-- Address -->
                <div class="space-y-4">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <input id="address" name="address" type="text" required
                               value="<?= htmlspecialchars($instructor['address'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div>
                        <label for="phonenumber" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input id="phonenumber" name="phonenumber" type="text"
                               value="<?= htmlspecialchars($instructor['phonenumber'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="zipcode" class="block text-sm font-medium text-gray-700">Zip code</label>
                            <input id="zipcode" name="zipcode" type="text" required
                                   value="<?= htmlspecialchars($instructor['zipcode'] ?? '') ?>"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                          focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        </div>
                        <div>
                            <label for="residence" class="block text-sm font-medium text-gray-700">City</label>
                            <input id="residence" name="residence" type="text" required
                                   value="<?= htmlspecialchars($instructor['residence'] ?? '') ?>"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                          focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        </div>
                    </div>
                    <div>
                        <label for="housenumber" class="block text-sm font-medium text-gray-700">House number</label>
                        <input id="housenumber" name="housenumber" type="text" required
                               value="<?= htmlspecialchars($instructor['housenumber'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg
                               hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function attachPasswordToggle(inputId, btnId, eyeOpenId, eyeClosedId) {
    const input = document.getElementById(inputId);
    const btn = document.getElementById(btnId);
    const eyeOpen = document.getElementById(eyeOpenId);
    const eyeClosed = document.getElementById(eyeClosedId);
    if (!input || !btn || !eyeOpen || !eyeClosed) return;

    btn.addEventListener('click', () => {
        const hidden = input.type === 'password';
        input.type = hidden ? 'text' : 'password';
        eyeOpen.classList.toggle('hidden', !hidden);
        eyeClosed.classList.toggle('hidden', hidden);
        btn.classList.toggle('text-green-600', !hidden);
        btn.classList.toggle('text-gray-400', hidden);
    });
}

attachPasswordToggle('password', 'toggle-password', 'eye-open-password', 'eye-closed-password');
attachPasswordToggle('passwordConfirm', 'toggle-passwordConfirm', 'eye-open-passwordConfirm', 'eye-closed-passwordConfirm');
</script>
</body>
</html>
