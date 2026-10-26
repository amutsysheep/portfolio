<?php
require_once 'PHP/ShowMessage.php';

$old = $_SESSION['old_input'] ?? [];
$geslacht = $old['geslacht'] ?? 'Man';

// Beschikbare beroepen ophalen
$stmt = $pdo->prepare("
    SELECT Beroep_id, Beroep
    FROM beroepen
    ORDER BY Beroep ASC
");

$stmt->execute();

$beroepen = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="register-container">

    <h2>Registreren als artiest</h2>

    <?php DisplayMessage(); ?>

    <form
            method="post"
            action="PHP/Registratie_artiest.php"
            id="register-artist-form"
            enctype="multipart/form-data"
    >

        <!-- Voornaam + Tussenvoegsel -->
        <div class="input-row">

            <div class="input-group">
                <label for="voornaam">Voornaam</label>

                <input
                        type="text"
                        name="voornaam"
                        id="voornaam"
                        placeholder="Voornaam"
                        value="<?= htmlspecialchars($old['voornaam'] ?? '') ?>"
                        required
                >
            </div>

            <div class="input-group">
                <label for="tussenvoegsel">Tussenvoegsel</label>
                <input
                        type="text"
                        name="tussenvoegsel"
                        id="tussenvoegsel"
                        placeholder="Tussenvoegsel"
                        value="<?= htmlspecialchars($old['tussenvoegsel'] ?? '') ?>"
                >
            </div>
        </div>

        <!-- Achternaam + Geslacht -->
        <div class="input-row">
            <div class="input-group">
                <label for="achternaam">Achternaam</label>
                <input
                        type="text"
                        name="achternaam"
                        id="achternaam"
                        placeholder="Achternaam"
                        value="<?= htmlspecialchars($old['achternaam'] ?? '') ?>"
                        required
                >
            </div>

            <div class="input-group">
                <label>Geslacht</label>

                <div class="gender-buttons">
                    <button
                            type="button"
                            class="gender-button <?= $geslacht === 'Man' ? 'selected' : '' ?>"
                            data-gender="Man"
                    >
                        Man
                    </button>

                    <button
                            type="button"
                            class="gender-button <?= $geslacht === 'Vrouw' ? 'selected' : '' ?>"
                            data-gender="Vrouw"
                    >
                        Vrouw
                    </button>
                </div>

                <input
                        type="hidden"
                        name="geslacht"
                        id="geslacht"
                        value="<?= htmlspecialchars($geslacht) ?>"
                >
            </div>
        </div>

        <!-- E-mailadres -->
        <div class="input-row">
            <div class="input-group">
                <label for="email">E-mailadres</label>

                <input
                        type="email"
                        name="email"
                        id="email"
                        placeholder="E-mailadres"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        required
                >
            </div>
        </div>

        <!-- Woonplaats + Postcode -->
        <div class="input-row">
            <div class="input-group">
                <label for="woonplaats">Woonplaats</label>

                <input
                        type="text"
                        name="woonplaats"
                        id="woonplaats"
                        placeholder="Woonplaats"
                        value="<?= htmlspecialchars($old['woonplaats'] ?? '') ?>"
                        required
                >
            </div>

            <div class="input-group">
                <label for="postcode">Postcode</label>

                <input
                        type="text"
                        name="postcode"
                        id="postcode"
                        placeholder="1234 AB"
                        value="<?= htmlspecialchars($old['postcode'] ?? '') ?>"
                        maxlength="7"
                        required
                >
            </div>
        </div>

        <!-- Straatnaam + Huisnummer -->
        <div class="input-row">
            <div class="input-group">
                <label for="straatnaam">Straatnaam</label>

                <input
                        type="text"
                        name="straatnaam"
                        id="straatnaam"
                        placeholder="Straatnaam"
                        value="<?= htmlspecialchars($old['straatnaam'] ?? '') ?>"
                        required
                >
            </div>

            <div class="input-group">
                <label for="huisnummer">Huisnummer</label>

                <input
                        type="number"
                        name="huisnummer"
                        id="huisnummer"
                        placeholder="Huisnummer"
                        value="<?= htmlspecialchars($old['huisnummer'] ?? '') ?>"
                        min="1"
                        required
                >
            </div>
        </div>

        <!-- Huisnummertoevoeging + Beroep -->
        <div class="input-row">
            <div class="input-group">
                <label for="huisnummertoevoeging">Huisnummertoevoeging</label>

                <input
                        type="text"
                        name="huisnummertoevoeging"
                        id="huisnummertoevoeging"
                        placeholder="Bijv. A"
                        value="<?= htmlspecialchars($old['huisnummertoevoeging'] ?? '') ?>"
                        maxlength="255"
                >
            </div>

            <div class="input-group">
                <label for="beroep_id">Beroep</label>

                <select
                        name="beroep_id"
                        id="beroep_id"
                        required
                >
                    <option value="">Kies een beroep</option>
                    <?php foreach ($beroepen as $beroep): ?>
                        <option
                                value="<?= htmlspecialchars($beroep['Beroep_id']) ?>"
                            <?= ($old['beroep_id'] ?? '') == $beroep['Beroep_id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($beroep['Beroep']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- CV upload -->
        <div class="input-row">
            <div class="input-group">
                <label for="cv">CV upload</label>

                <input
                        type="file"
                        name="cv"
                        id="cv"
                        accept=".pdf,.doc,.docx"
                        required
                >
                <small>
                    PDF, DOC of DOCX - maximaal 5 MB
                </small>
            </div>
        </div>

        <!-- Wachtwoord + Herhaal wachtwoord -->
        <div class="input-row">
            <div class="input-group">
                <label for="wachtwoord">Wachtwoord</label>

                <div class="password-wrapper">
                    <input
                            type="password"
                            name="wachtwoord"
                            id="wachtwoord"
                            placeholder="Wachtwoord"
                            required
                    >
                    <button
                            type="button"
                            class="password-toggle"
                            data-target="wachtwoord"
                            aria-label="Wachtwoord tonen"
                    > 👁
                    </button>
                </div>
            </div>

            <div class="input-group">
                <label for="herhaal_wachtwoord">Herhaal wachtwoord</label>

                <div class="password-wrapper">
                    <input
                            type="password"
                            name="herhaal_wachtwoord"
                            id="herhaal_wachtwoord"
                            placeholder="Herhaal wachtwoord"
                            required
                    >
                    <button
                            type="button"
                            class="password-toggle"
                            data-target="herhaal_wachtwoord"
                            aria-label="Wachtwoord tonen"
                    > 👁
                    </button>
                </div>
            </div>
        </div>

        <!-- Foutmelding wachtwoord -->
        <p id="password-error" class="register-password-error">
            Wachtwoorden komen niet overeen.
        </p>

        <!-- Geslacht foutmelding -->
        <p id="gender-error" class="register-password-error">
            Selecteer een geslacht.
        </p>

        <!-- Registreren -->
        <button type="submit" class="register-button">
            Registreren
        </button>
    </form>
</div>

<script>

    const genderButtons =
        document.querySelectorAll(".gender-button");
    const genderInput =
        document.getElementById("geslacht");
    const genderError =
        document.getElementById("gender-error");

    /* Geslacht selecteren */
    genderButtons.forEach(button => {
        button.addEventListener("click", function () {
            genderButtons.forEach(btn => {
                btn.classList.remove("selected");
            });

            this.classList.add("selected");
            genderInput.value = this.dataset.gender;
            genderError.style.display = "none";
        });
    });

    /* Wachtwoord tonen/verbergen */
    const passwordToggles =
        document.querySelectorAll(".password-toggle");

    passwordToggles.forEach(button => {
        button.addEventListener("click", function () {
            const target =
                document.getElementById(this.dataset.target);

            if (target.type === "password") {
                target.type = "text";
                this.textContent = "🙈";
                this.setAttribute(
                    "aria-label",
                    "Wachtwoord verbergen"
                );
            } else
            {
                target.type = "password";
                this.textContent = "👁";
                this.setAttribute(
                    "aria-label",
                    "Wachtwoord tonen"
                );
            }
        });
    });

    /* Formulier controleren voordat het wordt verzonden */
    document
        .getElementById("register-artist-form")
        .addEventListener("submit", function(event) {
            const wachtwoord =
                document.getElementById("wachtwoord").value;
            const herhaalWachtwoord =
                document.getElementById("herhaal_wachtwoord").value;
            const passwordError =
                document.getElementById("password-error");
            // Wachtwoorden controleren
            if (wachtwoord !== herhaalWachtwoord) {
                event.preventDefault();
                passwordError.style.display = "block";
                return;
            }
            // Geslacht controleren
            if (genderInput.value === "") {
                event.preventDefault();
                genderError.style.display = "block";
                return;
            }
            passwordError.style.display = "none";
            genderError.style.display = "none";
        });
</script>
