<div class="twofa-page">
    <div class="twofa-card">

        <div class="twofa-logo">
            <img src="Images/logo.png" alt="Flex Opera House">
        </div>

        <div class="twofa-content">

            <h1>2FA Verificatie</h1>

            <p class="twofa-description">
                We hebben een 6-cijferige verificatiecode naar je
                e-mailadres gestuurd.
            </p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="twofa-error">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form class="twofa-form" action="PHP/2fa.php" method="POST">

                <label for="twofa-code">
                    Verificatiecode
                </label>

                <input
                        type="text"
                        id="twofa-code"
                        name="code"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="000000"
                        required
                >

                <p class="twofa-expiration">
                    De code is 5 minuten geldig.
                </p>

                <button type="submit" class="twofa-button">
                    Verifiëren
                </button>

            </form>

        </div>

        <div class="twofa-footer">
            <span>Flex Opera House</span>
            <span>Veilig inloggen</span>
        </div>

    </div>
</div>