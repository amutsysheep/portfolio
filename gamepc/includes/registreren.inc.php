<div class="registratie-container">
    <form action="php/registreren.php" method="post" class="register-form">
        <h2 class="registratie-header">Registreren</h2>
        <div class="input-group">
            <label for="Naam" class="label-text">Voornaam</label>
            <input type="text" id="Naam" name="Naam" required>
        </div>
        <div class="input-group">
            <label for="Tussenvoegsel" class="label-text">Tussenvoegsel</label>
            <input type="text" id="Tussenvoegsel" name="Tussenvoegsel">
        </div>
        <div class="input-group">
            <label for="Achternaam" class="label-text">Achternaam</label>
            <input type="text" id="Achternaam" name="Achternaam" required>
        </div>
        <div class="input-group">
            <label for="Woonplaats" class="label-text">Woonplaats</label>
            <input type="text" id="Woonplaats" name="Woonplaats" required>
        </div>
        <div class="input-group">
            <label for="Straatnaam" class="label-text">Straatnaam</label>
            <input type="text" id="Straatnaam" name="Straatnaam" required>
        </div>
        <div class="input-group">
            <label for="Huisnummer" class="label-text">Huisnummer</label>
            <input type="text" id="Huisnummer" name="Huisnummer" required>
        </div>
        <div class="input-group">
            <label for="Huisnummer_toevoeging" class="label-text">Huisnummer_toevoeging</label>
            <input type="text" id="Huisnummer_toevoeging" name="Huisnummer_toevoeging">
        </div>
        <div class="input-group">
            <label for="Postcode" class="label-text">Postcode</label>
            <input type="text" id="Postcode" name="Postcode" required>
        </div>
        <div class="input-group">
            <label for="Gebruiker" class="label-text">Email</label>
            <input type="email" id="Gebruiker" name="Email" required>
        </div>
        <div class="input-group">
            <label for="Wachtwoord" class="label-text">Wachtwoord</label>
            <input type="password" id="Wachtwoord" name="Wachtwoord" required>
        </div>
        <div class="input-group">
            <label for="Herhaal_Wachtwoord" class="label-text">Herhaal Wachtwoord</label>
            <input type="password" id="Herhaal_Wachtwoord" name="Herhaal_Wachtwoord" required>
        </div>
        <div class="input-group">
            <label for="Geboortedatum" class="label-text">Geboortedatum</label>
            <input type="date" id="Geboortedatum" max="<?= date("Y-m-d"); ?>" name="Geboortedatum" required>
        </div>
        <button type="submit" class="registreer-button">Registreren</button>
    </form>

    <?php if (isset($_SESSION['Alert'])): ?>
        <div class="notification-message"><?php echo $_SESSION['Alert']; unset($_SESSION['Alert']); ?></div>
    <?php endif; ?>

    <div class="login-link">
        Heb je al een account? <a href="index.php?page=inlog">Log in</a>
    </div>
</div>
