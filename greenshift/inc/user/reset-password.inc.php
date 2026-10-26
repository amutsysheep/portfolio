<div class="min-h-screen bg-gradient-to-br flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-green-100 p-8">


            <div class="flex justify-center">
                <img src="img/greenshift.png" alt="Logo" class="h-10 w-auto">
            </div>
            <h1 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Reset your password
            </h1>
            <p class="mt-2 text-center text-sm text-gray-600">
                Choose a new password for your account.
            </p>

            <?php if (!empty($_SESSION['message'])): ?>
                <div class="text-black p-2 rounded mt-4 text-center">
                    <?= htmlspecialchars($_SESSION['message']) ?>
                </div>
                <?php unset($_SESSION['message']); endif; ?>

            <form action="php/user/reset-password.php" method="POST" class="mt-8 space-y-5">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">


                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New password</label>
                    <div class="mt-1 relative">
                        <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Your new password"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 pr-11 text-gray-900 placeholder-gray-400 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200"
                                required
                        />
                    </div>
                </div>



                <div>
                    <label for="password_repeat" class="block text-sm font-medium text-gray-700">Confirm password</label>
                    <div class="mt-1">
                        <input
                                id="password_repeat"
                                name="password_repeat"
                                type="password"
                                placeholder="Repeat your password"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 placeholder-gray-400 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-200"
                                required
                        />
                    </div>
                </div>


                <button
                        type="submit"
                        class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white shadow-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                >
                    Reset password
                </button>
            </form>
        </div>

        <p class="mt-4 text-center text-xs text-gray-500">
            <a href="index.php?page=login" class="underline hover:text-gray-700">Back to login</a>
        </p>
    </div>
</div>
