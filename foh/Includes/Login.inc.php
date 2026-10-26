<?php
require_once __DIR__ . '/../PHP/Showmessage.php';
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Log In - FOH</title>

    <link rel="stylesheet" href="CSS/Styling.css">
</head>

<body>

<div class="login-page">
    <div class="login-card">

        <div class="login-logo">
            <img src="./Images/Logo.png" alt="logo">
        </div>

        <h1 class="login-title">Log In</h1>
        <p class="login-subtitle">Welkom bij Flex Opera House</p>

        <?php DisplayMessage(); ?>

        <form method="POST" action="PHP/Login.php" class="login-form">

            <div class="form-group">
                <label for="email">Email</label>
                <input
                        type="email"
                        name="email"
                        id="email"
                        placeholder="Voer je email in"
                        required
                >
            </div>

            <div class="form-group password-wrapper">

                <input
                        type="password"
                        name="password"
                        id="wachtwoord"
                        placeholder="Wachtwoord"
                        required
                >

                <button
                        type="button"
                        class="password-toggle"
                        data-target="wachtwoord"
                        aria-label="Wachtwoord tonen"
                >
                    👁
                </button>

            </div>

            <button type="submit" class="login-button">
                Inloggen
            </button>

        </form>

        <div class="login-footer">
            <div class="status-row">
                <span class="status-dot"></span>
                <span>System online</span>
            </div>
        </div>

    </div>
</div>

<script>
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

</script>


</body>
</html>