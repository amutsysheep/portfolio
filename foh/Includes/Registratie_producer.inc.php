<?php
$old = $_SESSION['old_input'] ?? [];
require_once 'PHP/ShowMessage.php';
?>

<div class="register-container">
    <h2>Registreren als producer</h2>

    <?php DisplayMessage(); ?>

    <form method="post" action="PHP/Registratie_producer.php" id="register-producer-form">
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

        <!-- Achternaam -->
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

        <!-- Aanmelden -->
        <button type="submit" class="register-button">
            Aanmelden
        </button>
    </form>
</div>

<script>
    // Wachtwoord tonen/verbergen
    const passwordToggles = document.querySelectorAll(".password-toggle");

    passwordToggles.forEach(button => {
        button.addEventListener("click", function () {

            const target = document.getElementById(this.dataset.target);

            if (target.type === "password") {
                target.type = "text";
                this.textContent = "🙈";
                this.setAttribute(
                    "aria-label",
                    "Wachtwoord verbergen"
                );
            } else {
                target.type = "password";
                this.textContent = "👁";
                this.setAttribute(
                    "aria-label",
                    "Wachtwoord tonen"
                );
            }
        });
    });

    // Wachtwoorden controleren
    document
        .getElementById("register-producer-form")
        .addEventListener("submit", function(event) {
            const wachtwoord =
                document.getElementById("wachtwoord").value;
            const herhaalWachtwoord =
                document.getElementById("herhaal_wachtwoord").value;
            const passwordError =
                document.getElementById("password-error");
            if (wachtwoord !== herhaalWachtwoord) {
                event.preventDefault();
                passwordError.style.display = "block";
                return;
            }
            passwordError.style.display = "none";
        });
</script>