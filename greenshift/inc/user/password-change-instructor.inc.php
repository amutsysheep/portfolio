<?php
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Token ophalen uit de URL bij eerste keer laden, of uit de sessie als die al is opgeslagen
if (isset($_GET['token'])) {
    $_SESSION['reset_token'] = $_GET['token'];
}
$token = $_SESSION['reset_token'] ?? '';

// Optioneel: melding tonen als token ontbreekt
if (!$token) {
    $_SESSION['message'] = 'Ongeldige of verlopen token.';
}
?>
<!-- Pagina achtergrond -->
<div class="min-h-screen bg-gradient-to-br flex items-center justify-center p-4">

    <!-- Auth kaart -->
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8">

            <!-- Logo -->
            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>
            <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Stel uw wachtwoord in
            </h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                Kies een veilig wachtwoord om uw account te activeren.
            </p>

            <?php if ($message): ?>
                <div class="text-black p-2 rounded mt-4 text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form action="php/user/confirm_activation_instructor.php" method="POST" class="mt-8 space-y-5">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <!-- Wachtwoord -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Wachtwoord</label>
                    <div class="mt-1 relative">
                        <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Uw wachtwoord"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 pr-11 text-gray-900 placeholder-gray-400 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200"
                                required
                        />

                        <!-- Toon/Verberg toggle -->
                        <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                aria-label="Wachtwoord tonen/verbergen">
                            <!-- Oog open -->
                            <svg id="eyeOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                                <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"></circle>
                            </svg>
                            <!-- Oog dicht -->
                            <svg id="eyeClosed" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-7 0-11-7-11-7a19.76 19.76 0 014.09-4.69M9.88 4.24A9.94 9.94 0 0112 4c7 0 11 7 11 7a19.62 19.62 0 01-3.17 3.86M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Bevestig wachtwoord -->
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700">Bevestig wachtwoord</label>
                    <div class="mt-1 relative">
                        <input
                                id="password_confirm"
                                name="password_confirm"
                                type="password"
                                placeholder="Herhaal uw wachtwoord"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200"
                                required
                        />
                    </div>
                </div>

                <button
                        type="submit"
                        class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                >
                    Activeer account
                </button>
            </form>
        </div>

        <!-- Kleine juridische tekst -->
        <p class="mt-4 text-center text-xs text-gray-500">
            Door uw account te activeren gaat u akkoord met onze <a href="#" class="underline hover:text-gray-700">Voorwaarden</a> &amp; <a href="#" class="underline hover:text-gray-700">Privacybeleid</a>.
        </p>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePassword');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');

    toggleBtn.addEventListener('click', () => {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        eyeOpen.classList.toggle('hidden', !isHidden);
        eyeClosed.classList.toggle('hidden', isHidden);
    });
</script>
