<?php
session_start();

// Check if OTP and session values are set
if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry'])) {
    $_SESSION['Alert'] = 'Verificatie verlopen of ongeldig.';
    header('Location: index.php?page=inlog');
    exit();
}

// Check if OTP has expired
if (time() > $_SESSION['otp_expiry']) {
    unset($_SESSION['otp'], $_SESSION['otp_expiry']);
    $_SESSION['Alert'] = 'De verificatiecode is verlopen. Probeer opnieuw in te loggen.';
    header('Location: index.php?page=inlog');
    exit();
}

// Process OTP submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userOtp = $_POST['otp'] ?? '';

    if ($userOtp == $_SESSION['otp']) {
        // OTP correct
        $_SESSION['User_id'] = $_SESSION['User_id_temp'];
        $_SESSION['Rol'] = $_SESSION['Rol_temp'];

        // Remove temporary session data
        unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['User_id_temp'], $_SESSION['Rol_temp']);

        // Redirect to the home page or dashboard
        header('Location: ../index.php?page=home');
        exit();
    } else {
        $_SESSION['Alert'] = 'Ongeldige verificatiecode.';
    }
}

// Include the HTML interface
include 'Includes/OTPVerify.inc.php';
