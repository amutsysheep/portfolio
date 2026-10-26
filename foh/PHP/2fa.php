<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=login');
    exit();
}

$code = trim($_POST['code'] ?? '');

if (empty($code)) {
    $_SESSION['error'] = 'Vul de 2FA-code in.';
    header('Location: ../index.php?page=2fa');
    exit();
}

if (!isset(
    $_SESSION['2fa_code'],
    $_SESSION['2fa_expire'],
    $_SESSION['2fa_login_id'],
    $_SESSION['2fa_email'],
    $_SESSION['2fa_rol_id']
)) {
    $_SESSION['error'] = 'Je 2FA-sessie is verlopen. Log opnieuw in.';
    header('Location: ../index.php?page=login');
    exit();
}

if (time() > $_SESSION['2fa_expire']) {
    unset(
        $_SESSION['2fa_code'],
        $_SESSION['2fa_expire'],
        $_SESSION['2fa_login_id'],
        $_SESSION['2fa_email'],
        $_SESSION['2fa_rol_id'],
        $_SESSION['2fa_test_code']
    );

    $_SESSION['error'] = 'De 2FA-code is verlopen. Log opnieuw in.';
    header('Location: ../index.php?page=login');
    exit();
}

if (!hash_equals((string) $_SESSION['2fa_code'], (string) $code)) {
    $_SESSION['error'] = 'Ongeldige 2FA-code.';
    header('Location: ../index.php?page=2fa');
    exit();
}

session_regenerate_id(true);

$_SESSION['login_id'] = $_SESSION['2fa_login_id'];
$_SESSION['email'] = $_SESSION['2fa_email'];
$_SESSION['rol_id'] = $_SESSION['2fa_rol_id'];

/*
 * Tijdelijke 2FA-gegevens verwijderen.
 */
unset(
    $_SESSION['2fa_code'],
    $_SESSION['2fa_expire'],
    $_SESSION['2fa_login_id'],
    $_SESSION['2fa_email'],
    $_SESSION['2fa_rol_id'],
    $_SESSION['2fa_test_code']
);

switch ((int) $_SESSION['rol_id']) {
    case 1:
        header('Location: ../index.php?page=Dashboard_beheerder');
        break;

    case 2:
        header('Location: ../index.php?page=Opera_overzicht_producer');
        break;

    case 3:
        header('Location: ../index.php?page=Opera_overzicht_artiest');
        break;

    default:
        $_SESSION['error'] = 'Onbekende gebruikersrol.';
        header('Location: ../index.php?page=login');
        break;
}

exit();