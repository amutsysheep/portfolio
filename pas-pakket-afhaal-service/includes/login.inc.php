<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php elseif (isset($_SESSION['succes'])): ?>
    <div class="alert alert-succes">
        <?php echo $_SESSION['succes']; unset($_SESSION['succes']); ?>
    </div>
<?php endif; ?>

<?php $message_registratie = $_SESSION['message'] ?? '';
unset($_SESSION['message']);?>

<?php if ($message_registratie): ?>
    <div class="message"><?= htmlspecialchars($message_registratie) ?></div>
<?php endif; ?>

<div class="login-pagina">
    <div class="login-container">
        <form action="PHP/login.php" method="post" class="login-box">
            <h1 class="login-title">Inloggen</h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <input type="text" name="email" placeholder="E-mailadres" required autocomplete="off">
            <input type="password" name="wachtwoord" placeholder="Wachtwoord" required>

            <button type="submit" class="login-button">Inloggen</button>

            <p class="login-register-text">
                Nog geen account? <a href="index.php?page=Registreren">Registreer hier</a>
            </p>
        </form>
    </div>
</div>
