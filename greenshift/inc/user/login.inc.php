<?php
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!-- Page background -->
<div class="min-h-screen bg-gradient-to-br flex items-center justify-center p-4">

    <!-- Auth card -->
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8">

            <!-- Logo (optional) -->
            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>
            <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Sign in
            </h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                Welcome back! Please enter your details.
            </p>

            <?php if ($message): ?>
                <div class="text-black p-2 rounded mt-4 text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form action="php/user/login.php" method="POST" class="mt-8 space-y-5">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <div class="mt-1 relative">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            placeholder="you@company.com"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200"
                            required
                        />
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-300">
              <!-- mail icon -->
              <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </span>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="index.php?page=forgot-password" class="text-sm font-medium text-green-600 hover:text-green-700">Forgot password?</a>
                    </div>

                    <div class="mt-1 relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Your password"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 pr-11 text-gray-900 placeholder-gray-400 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200"
                            required
                        />

                        <!-- Show/Hide toggle -->
                        <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                aria-label="Toggle password visibility">
                            <!-- Eye open -->
                            <svg id="eyeOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                                <circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"></circle>
                            </svg>
                            <!-- Eye closed -->
                            <svg id="eyeClosed" class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-7 0-11-7-11-7a19.76 19.76 0 014.09-4.69M9.88 4.24A9.94 9.94 0 0112 4c7 0 11 7 11 7a19.62 19.62 0 01-3.17 3.86M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember + submit -->
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        Remember me
                    </label>
                    <a href="#" class="text-sm text-gray-500 hover:text-gray-700">Need help?</a>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                >
                    Sign in
                </button>

                <p class="text-center text-sm text-gray-600">
                    Don’t have an account?
                    <a href="<?php echo generateUrl('register'); ?>" class="font-semibold text-green-700 hover:text-green-800">Sign up</a>
                </p>
            </form>
        </div>

        <!-- tiny legal -->
        <p class="mt-4 text-center text-xs text-gray-500">
            By signing in you agree to our <a href="#" class="underline hover:text-gray-700">Terms</a> &amp; <a href="#" class="underline hover:text-gray-700">Privacy Policy</a>.
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
