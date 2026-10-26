<!-- Pagina achtergrond -->
<div class="min-h-screen bg-gradient-to-br flex items-center justify-center p-4">

    <!-- Instructeur toevoegen kaart -->
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8">

            <!-- Logo -->
            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>

            <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Rijinstructeur Toevoegen</h1>
            <p class="mt-2 text-center text-sm text-gray-600">Vul de gegevens in om een nieuwe instructeur te registreren</p>

            <!-- Formulier -->
            <form action="php/admin/addlessonpackages.php" method="POST" class="mt-8 space-y-5">
                <!-- Voornaam -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Naam</label>
                    <input id="name" name="name" type="text" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                  focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Tussenvoegsel -->
                <div>
                    <label for="number_of_lessons" class="block text-sm font-medium text-gray-700">Lessen</label>
                    <input id="number_of_lessons" name="number_of_lessons" type="text"
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                  focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Achternaam -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Prijs</label>
                    <input id="price" name="price" type="text" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                  focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Adres -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Omschrijving</label>
                    <input id="description" name="description" type="text" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm
                                  focus:border-green-500 focus:ring-2 focus:ring-green-200">
                </div>

                <!-- Verstuur -->
                <button type="submit"
                        class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg
                               hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                   Instructeur Toevoegen
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function attachPasswordToggle(inputId, btnId, eyeOpenId, eyeClosedId) {
        const input = document.getElementById(inputId)
        const btn = document.getElementById(btnId)
        const eyeOpen = document.getElementById(eyeOpenId)
        const eyeClosed = document.getElementById(eyeClosedId)
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
