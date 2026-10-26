<div class="inlog-container">
    <div class="inlog-form">
        <h1>Login</h1>

        <div class="login">
            <form action="php/inlog.php" method="post">
                <label for="email">Email</label><br>
                <input type="text" id="Email" name="Email" required><br>

                <label for="wachtwoord">Wachtwoord</label><br>
                <input type="password" id="Wachtwoord" name="Wachtwoord" required><br>

                <input type="submit" value="Inloggen" class="button-default">
            </form>

            <?php
            if (isset($_SESSION['Alert'])) {
                echo "<div class='notification'>" . htmlspecialchars($_SESSION['Alert']) . "</div>";
                unset($_SESSION['Alert']);
            }
            ?>
        </div>
        <div class="register-link">
            <p>Geen account?</p>
            <a href="index.php?page=registreren">Registreren</a>
        </div>
    </div>
</div>
