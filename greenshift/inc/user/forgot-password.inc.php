<div class="min-h-screen bg-gradient-to-br flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8">


            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>
            <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Forgot your password?
            </h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your email address and we’ll send you a link to reset your password.
            </p>

            <?php if (!empty($_SESSION['message'])): ?>
                <div class="text-black p-2 rounded mt-4 text-center">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                </div>
                <?php unset($_SESSION['message']); endif; ?>

            <form action="php/user/send-reset.php" method="POST" class="mt-8 space-y-5">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <div class="mt-1">
                        <input
                                id="email"
                                name="email"
                                type="email"
                                placeholder="you@example.com"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200"
                                required
                        />
                    </div>
                </div>

                <button
                        type="submit"
                        class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                >
                    Send reset link
                </button>
            </form>
        </div>

        <p class="mt-4 text-center text-xs text-gray-500">
            <a href="index.php?page=login" class="underline hover:text-gray-700">Back to login</a>
        </p>
    </div>
</div>
