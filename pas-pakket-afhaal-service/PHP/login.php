<?php
session_start();

include "../../../private/connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=login');
    exit();
}

$email = trim($_POST['email'] ?? '');
$wachtwoord = $_POST['wachtwoord'] ?? '';

if (empty($email) || empty($wachtwoord)) {
    $_SESSION['error'] = 'Vul alle velden in.';
    header('Location: ../index.php?page=login');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Ongeldig e-mailadres.';
    header('Location: ../index.php?page=login');
    exit();
}

try {

    $stmt = $pdo->prepare("
        SELECT 
            g.user_id,
            g.email,
            g.wachtwoord,
            g.rol_id,
            c.is_Goedgekeurd
        FROM gebruikers g
        LEFT JOIN couriers c ON g.user_id = c.user_id
        WHERE g.email = :email
        LIMIT 1
    ");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($wachtwoord, $user['wachtwoord'])) {
        $_SESSION['error'] = 'Ongeldige combinatie van e-mail en wachtwoord.';
        header('Location: ../index.php?page=login');
        exit();
    }


    if ($user['rol_id'] == 2) {
        switch ($user['is_Goedgekeurd']) {

            case null:
            case 0:
                $_SESSION['error'] = 'Je account is nog niet goedgekeurd.';
                header('Location: ../index.php?page=login');
                exit();

            case 2:
                $_SESSION['error'] = 'Je account is afgekeurd. Controleer je e-mail.';
                header('Location: ../index.php?page=login');
                exit();

            case 4:
                $_SESSION['error'] = 'Je account is verwijderd.';
                header('Location: ../index.php?page=login');
                exit();

            case 5:

                $_SESSION['beperkt'] = true;
                break;

            case 1:
                // Alles OK
                break;

            default:
                $_SESSION['error'] = 'Onbekende accountstatus.';
                header('Location: ../index.php?page=login');
                exit();
        }
    }

    // Login succesvol
    $_SESSION['user_id']    = $user['user_id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['rol_id']     = $user['rol_id'];

    switch ($user['rol_id']) {
        case 1: // Klant
            header('Location: ../index.php?page=mijn_pakketten');
            break;
        case 2: // Courier
            header('Location: ../index.php?page=courier_overzicht');
            break;
        case 3: // Admin
            header('Location: ../index.php?page=courier_beheer');
            break;
        default:
            $_SESSION['error'] = 'Onbekende gebruikersrol.';
            header('Location: ../index.php?page=login');
            break;
    }
    exit();

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['error'] = 'Er is een technische fout opgetreden.';
    header('Location: ../index.php?page=login');
    exit();
}
