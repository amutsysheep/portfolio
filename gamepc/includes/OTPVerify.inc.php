<div class="otp-container">
    <h1>Verificatie</h1>
    <p>Voer de verificatiecode in die naar uw e-mailadres is gestuurd.</p>

    <form action="php/OTPVerify.php" method="POST">
        <label for="otp">Verificatiecode:</label><br>
        <input type="text" id="otp" name="otp" required><br>

        <input type="submit" value="Verifiëren" class="button-default">
    </form>

    <?php
    if (isset($_SESSION['Alert'])) {
        echo "<div class='notification'>" . htmlspecialchars($_SESSION['Alert']) . "</div>";
        unset($_SESSION['Alert']);
    }
    ?>
</div>
