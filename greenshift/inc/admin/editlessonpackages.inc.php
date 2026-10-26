<?php

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$instructorId = $_GET['id'] ?? null;

if (!$instructorId) {
    echo "Lesson Packages ID not provided.";
    exit();
}

$stmt = $pdo->prepare("
    SELECT id, name, number_of_lessons, price, description
    FROM lesson_packages
    where id = :id
    LIMIT 1
");
$stmt->execute([':id' => $instructorId]);
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
    <title>Edit Lesson packages Instructor</title>
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
                <h1 class="text-3xl font-extrabold text-gray-900">Edit Lesson packages Instructor</h1>
                <p class="mt-1 text-sm text-gray-600">Update the Lesson packages details below</p>
            </div>

            <?php if ($message): ?>
                <div class="text-black p-2 rounded mt-4 text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="php/admin/editlessonpackage.php?id=<?=$instructorId?>" class="space-y-5">

                <!-- Personal info -->
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Naam</label>
                        <input id="name" name="name" type="text" required
                               value="<?= htmlspecialchars($instructor['name'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div>
                        <label for="number_of_lessons" class="block text-sm font-medium text-gray-700">number_of_lessons</label>
                        <input id="number_of_lessons" name="number_of_lessons" type="text"
                               value="<?= htmlspecialchars($instructor['number_of_lessons'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Prijs</label>
                        <input id="price" name="price" type="text" required
                               value="<?= htmlspecialchars($instructor['price'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Omschrijving</label>
                        <input id="description" name="description" type="text" required
                               value="<?= htmlspecialchars($instructor['description'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
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
