<?php
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Get user's address info
$sql_address = "
SELECT country 
FROM address ";
$stmt_address = $pdo->prepare($sql_address);
$stmt_address->execute();
$address = $stmt_address->fetch(PDO::FETCH_ASSOC);

// Get ENUM values for country
$stmt_enum = $pdo->query("SHOW COLUMNS FROM address LIKE 'country'");
$column = $stmt_enum->fetch(PDO::FETCH_ASSOC);
$enum_values = [];

if ($column && preg_match("/^enum\((.*)\)$/", $column['Type'], $matches)) {
    $vals = explode(",", $matches[1]);
    foreach ($vals as $val) {
        $enum_values[] = trim($val, " '");
    }
}
?>

<!-- Pagina achtergrond -->
<div class="min-h-screen bg-gradient-to-br flex items-center justify-center p-4">

    <!-- Registratiekaart -->
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8">

            <!-- Logo -->
            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>

            <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Registreren</h1>
            <p class="mt-2 text-center text-sm text-gray-600">Maak een account aan om te beginnen</p>

            <?php if ($message): ?>
                <div class="text-black p-2 rounded mt-4 text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form action="php/user/register.php" method="POST" class="mt-8 space-y-5">
                <!-- Voornaam -->
                <div>
                    <label for="firstname" class="block text-sm font-medium text-gray-700">Voornaam</label>
                    <input id="firstname" name="firstname" type="text" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Tussenvoegsel -->
                <div>
                    <label for="infix" class="block text-sm font-medium text-gray-700">Tussenvoegsel</label>
                    <input id="infix" name="infix" type="text"
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Achternaam -->
                <div>
                    <label for="lastname" class="block text-sm font-medium text-gray-700">Achternaam</label>
                    <input id="lastname" name="lastname" type="text" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- E-mailadres -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">E-mailadres</label>
                    <input id="email" name="email" type="email" autocomplete="email" placeholder="jij@bedrijf.com" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Wachtwoord -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Wachtwoord</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="new-password"
                               placeholder="Maak een wachtwoord aan" required
                               class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-11 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <button type="button" id="toggle-password"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-green-600"
                                aria-label="Toon/verberg wachtwoord">
                            <!-- oog open -->
                            <svg id="eye-open-password" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                                <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"></circle>
                            </svg>
                            <!-- oog dicht -->
                            <svg id="eye-closed-password" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-7 0-11-7-11-7a19.76 19.76 0 014.09-4.69M9.88 4.24A9.94 9.94 0 0112 4c7 0 11 7 11 7a19.62 19.62 0 01-3.17 3.86M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Herhaal wachtwoord -->
                <div>
                    <label for="passwordConfirm" class="block text-sm font-medium text-gray-700">Herhaal wachtwoord</label>
                    <div class="mt-1 relative">
                        <input id="passwordConfirm" name="password_confirm" type="password" autocomplete="new-password"
                               placeholder="Herhaal wachtwoord" required
                               class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 pr-11 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                        <button type="button" id="toggle-passwordConfirm"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-green-600"
                                aria-label="Toon/verberg wachtwoord">
                            <!-- oog open -->
                            <svg id="eye-open-passwordConfirm" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                                <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"></circle>
                            </svg>
                            <!-- oog dicht -->
                            <svg id="eye-closed-passwordConfirm" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-7 0-11-7-11-7a19.76 19.76 0 014.09-4.69M9.88 4.24A9.94 9.94 0 0112 4c7 0 11 7 11 7a19.62 19.62 0 01-3.17 3.86M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Adres -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Adres</label>
                    <input id="address" name="address" type="text" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Huisnummer -->
                <div>
                    <label for="housenumber" class="block text-sm font-medium text-gray-700">Huisnummer</label>
                    <input id="housenumber" name="housenumber" type="text" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Postcode -->
                <div>
                    <label for="zipcode" class="block text-sm font-medium text-gray-700">Postcode</label>
                    <input id="zipcode" name="zipcode" type="text" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Woonplaats -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">Woonplaats</label>
                    <input id="city" name="city" type="text" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>
                <div>
                <label for="city" class="block text-sm/6 font-medium text-gray-900">Country</label>
                <select name="country" required class="bg-white w-full border p-2 rounded">
                    <?php foreach ($enum_values as $country): ?>
                        <option value="<?= $country ?>" <?= ($address['country'] ?? '') === $country ? 'selected' : '' ?>>
                            <?= $country ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </div>

                <!-- Verstuur -->
                <button type="submit"
                        class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                    Registreren
                </button>

                <p class="text-center text-sm text-gray-600">
                    Heb je al een account?
                    <a href="#" class="font-semibold text-green-700 hover:text-green-800">Inloggen</a>
                </p>
            </form>
        </div>

        <p class="mt-4 text-center text-xs text-gray-500">
            Door je te registreren ga je akkoord met onze <a href="#" class="underline hover:text-gray-700">Voorwaarden</a> &
            <a href="#" class="underline hover:text-gray-700">Privacybeleid</a>.
        </p>
    </div>
</div>

<script>
    // Wachtwoord tonen/verbergen functionaliteit
    function attachPasswordToggle(inputId, btnId, eyeOpenId, eyeClosedId) {
        const input = document.getElementById(inputId)
        const btn = document.getElementById(btnId)
        const eyeOpen = document.getElementById(eyeOpenId)
        const eyeClosed = document.getElementById(eyeClosedId)
        if (!input || !btn || !eyeOpen || !eyeClosed) return;

        btn.addEventListener('click',() => {
            const hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', !hidden);
            eyeClosed.classList.toggle('hidden', hidden);
            btn.classList.toggle('text-green-600', !hidden);
            btn.classList.toggle('text-gray-400', hidden);
        });
    }
    // Activeer beide velden
    attachPasswordToggle('password', 'toggle-password', 'eye-open-password', 'eye-closed-password');
    attachPasswordToggle('passwordConfirm', 'toggle-passwordConfirm', 'eye-open-passwordConfirm', 'eye-closed-passwordConfirm');
</script>
