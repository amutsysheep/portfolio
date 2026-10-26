<?php
$message_registratie = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<div class="registratie-pagina">

    <div class="registratie-kaart">

        <h1 class="registratie-title">Registreren courier</h1>
        <p class="registratie-subtitle">Maak een account aan om te beginnen</p>

        <?php if ($message_registratie): ?>
            <div class="message-box"><?= htmlspecialchars($message_registratie) ?></div>
        <?php endif; ?>

        <form action="PHP/registratie_courier.php" method="POST" class="registratie-form">

            <label>Voornaam</label>
            <input type="text" name="naam" required>

            <label>Tussenvoegsel</label>
            <input type="text" name="tussenvoegsel">

            <label>Achternaam</label>
            <input type="text" name="achternaam" required>

            <label>E-mailadres</label>
            <input type="email" name="email" required placeholder="jij@bedrijf.com">

            <label>Wachtwoord</label>
            <input type="password" name="wachtwoord" required id="wachtwoord">

            <label>Herhaal wachtwoord</label>
            <input type="password" name="herhaal_wachtwoord" required id="herhaal_wachtwoord">

            <label>Bedrijfsnaam</label>
            <input type="text" name="bedrijfsnaam">

            <button type="submit">Registreren</button>

            <p class="al_account">
                Heb je al een account?
                <a href="#">Inloggen</a>
            </p>
        </form>

        <p class="terms">
            Door je te registreren ga je akkoord met onze <a href="#">Voorwaarden</a> &
            <a href="#">Privacybeleid</a>.
        </p>
    </div>
</div>
