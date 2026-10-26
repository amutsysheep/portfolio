<?php

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['userid'])) {
    $_SESSION['message'] = "Please login first.";
    header("Location: ../../index.php?page=login");
    exit();
}

$userid = $_SESSION['userid'];

// Haal huidige gegevens van de ingelogde instructor
$stmt = $pdo->prepare("
    SELECT u.*, 
           a.address, a.housenumber, a.city AS residence, a.postalcode AS zipcode, a.phonenumber
    FROM user u
    LEFT JOIN address a ON u.addressid = a.addressid
    WHERE u.userid = :userid
    LIMIT 1
");
$stmt->execute([':userid' => $userid]);
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$instructor) {
    $_SESSION['message'] = "Instructor not found.";
    header("Location: ../../index.php?page=dashboard");
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

            <!-- Logo -->
            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>

            <!-- Heading -->
            <div class="text-center">
                <h1 class="text-3xl font-extrabold text-gray-900">Edit Driving Instructor</h1>
                <p class="mt-1 text-sm text-gray-600">Update the instructor's details below</p>
            </div>

            <!-- Form -->
            <form method="POST" action="php/driving_instructor/editinstructor.php" class="space-y-5">

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
                        <input id="email" name="email" type="email" placeholder="you@company.com" required
                               value="<?= htmlspecialchars($instructor['email'] ?? '') ?>"
                               class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                      focus:border-green-500 focus:ring-2 focus:ring-green-200">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input id="password" name="password" type="password" placeholder="Create a new password"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                          focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        </div>
                        <div>
                            <label for="passwordConfirm" class="block text-sm font-medium text-gray-700">Repeat password</label>
                            <input id="passwordConfirm" name="password_confirm" type="password" placeholder="Repeat new password"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                          focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        </div>
                    </div>
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
                        <label for="phonenumber" class="block text-sm font-medium text-gray-700">Phone number</label>
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

                <!-- Submit -->
                <button type="submit"
                        class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg
                               hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
