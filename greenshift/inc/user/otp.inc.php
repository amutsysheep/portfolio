<?php
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!-- Pagina achtergrond -->
<div class="min-h-screen bg-gradient-to-br flex items-center justify-center p-4">

    <!-- Verificatiekaart -->
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8">

            <!-- Logo (optioneel) -->
            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>
            <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Verifieer Token
            </h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                Vul de token in die u per e-mail heeft ontvangen.
            </p>

            <?php if ($message): ?>
                <div class="text-black p-2 rounded mt-4 text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form action="php/user/otp.php" method="POST" class="mt-8 space-y-5">
                <!-- Token -->
                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700">Token</label>
                    <div class="mt-1 relative">
                        <input
                                id="otp"
                                name="otp"
                                type="text"
                                placeholder="Voer uw token in"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200"
                                required
                        />
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-300">
                            <!-- sleutel icoon -->
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m0-6V9m-7 6a9 9 0 1114 0H5z"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <button
                        type="submit"
                        class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                >
                    Verifieer Token
                </button>
            </form>
        </div>
    </div>
</div>
