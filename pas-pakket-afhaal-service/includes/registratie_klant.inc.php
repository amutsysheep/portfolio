<?php
$message_registratie = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<div class="registratie-pagina">

    <div class="registratie-kaart">

        <h1 class="registratie-title">Registreren</h1>
        <p class="registratie-subtitle">Maak een account aan om te beginnen</p>

        <?php if ($message_registratie): ?>
            <div class="message-box"><?= htmlspecialchars($message_registratie) ?></div>
        <?php endif; ?>

        <form action="PHP/registratie_klant.php" method="POST" class="registratie-form">

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

            <label>Telefoonnummer</label>
            <input type="text" name="telefoonnummer" required>

            <label>Straat</label>
            <input type="text" name="straat" required>

            <label>Huisnummer</label>
            <input type="text" name="huisnummer" required>

            <label>Huisnummer Toevoeging</label>
            <input type="text" name="huisnummer_toevoeging">

            <label>Postcode</label>
            <input type="text" name="postcode" required>

            <label>Woonplaats</label>
            <input type="text" name="plaats" required>

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
