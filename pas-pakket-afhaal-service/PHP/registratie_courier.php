<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();

include "../../private/connection.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../');
    exit();
}

require_once '../vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../env/phpmailer');
$dotenv->load();



// Helper function om bevestigingsmail te sturen
function sendWelcomeEmail($email, $naam) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = ($_ENV['SMTP_SECURE'] === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int) $_ENV['SMTP_PORT'];

        $mail->setFrom('info@pas.nl', 'PAS');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Account succesvol aangemaakt';
        $mail->Body    = "<h2>Welkom $naam!</h2>
                          <p>Je account is succesvol aangemaakt, wacht op goedkeuring van de admin.</p>
                          <br>
                          <p>Met vriendelijke groet,<br><strong>PAS Team</strong></p>";
        $mail->AltBody = "Welkom $naam!\n\nJe account is aangemaakt, wacht op goedkeuring van de admin.\n\n- Pas Team";

        $mail->send();
    } catch (Exception $e) {
        echo "Bericht kon niet worden verzonden. Mailer fout: {$mail->ErrorInfo}";
    }
}

// POST data ophalen
$naam = $_POST['naam'] ?? '';
$tussenvoegsel = $_POST['tussenvoegsel'] ?? '';
$achternaam = $_POST['achternaam'] ?? '';
$email = $_POST['email'] ?? '';
$wachtwoord = $_POST['wachtwoord'] ?? '';
$herhaal_wachtwoord = $_POST['herhaal_wachtwoord'] ?? '';
$bedrijfsnaam = $_POST['bedrijfsnaam'] ?? '';


// Verplicht veld-check
$requiredFields = [
    'naam' => $naam,
    'achternaam' => $achternaam,
    'email' => $email,
    'wachtwoord' => $wachtwoord,
    'herhaal_wachtwoord' => $herhaal_wachtwoord,
];

foreach ($requiredFields as $fieldName => $fieldValue) {
    if (empty(trim($fieldValue))) {
        $_SESSION['message'] = ucfirst(str_replace('-', ' ', $fieldName)) . ' is verplicht.';
        $_SESSION['register-history'] = [
            'naam' => $naam,
            'achternaam' => $achternaam,
            'email' => $email,
            'wachtwoord' => $wachtwoord,
            'herhaal_wachtwoord' => $herhaal_wachtwoord,

        ];
        header('Location: ../index.php?page=registratie_courier');
        exit();
    }
}

// Formdata bewaren
$formData = [
    'naam' => $naam,
    'tussenvoegsel' => $tussenvoegsel,
    'achternaam' => $achternaam,
    'email' => $email,
    'bedrijfsnaam' => $bedrijfsnaam,
];

// Bestaat e-mail al?
$Stmt = $pdo->prepare("SELECT email FROM gebruikers WHERE email = :email");
$Stmt->bindParam(':email', $email);
$Stmt->execute();

if ($Stmt->rowCount() > 0) {
    $_SESSION['message'] = 'Dit e-mailadres bestaat al. Kies een ander adres.';
    $_SESSION['register-history'] = $formData;
    header('Location: ../index.php?page=registratie_courier');
    exit();
}

// Wachtwoorden gelijk?
if ($wachtwoord !== $herhaal_wachtwoord) {
    $_SESSION['message'] = "Wachtwoorden komen niet overeen.";
    $_SESSION['register-history'] = $formData;
    header('Location: ../index.php?page=registratie_courier');
    exit();
}

// Wachtwoord sterkte
if (!(strlen($wachtwoord) >= 8) || !preg_match('/[A-Z]/', $wachtwoord) || !preg_match('/[a-z]/', $wachtwoord) || !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $wachtwoord)) {
    $_SESSION['message'] = 'Het wachtwoord moet minimaal 15+ tekens bevatten, inclusief een hoofdletter, een kleine letter en een speciaal teken.';
    $_SESSION['register-history'] = $formData;
    header('Location: ../index.php?page=registratie_courier');
    exit();
}

// Alles oké, gegevens opslaan
$hashedwachtwoord = password_hash($wachtwoord, PASSWORD_BCRYPT);


$pdo->beginTransaction();
try {
    $sql1 = 'INSERT INTO gebruikers (naam, tussenvoegsel, achternaam, email, wachtwoord, rol_id) 
             VALUES(:naam, :tussenvoegsel, :achternaam, :email, :wachtwoord, 2)';
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->bindParam(':naam', $naam);
    $stmt1->bindParam(':tussenvoegsel', $tussenvoegsel);
    $stmt1->bindParam(':achternaam', $achternaam);
    $stmt1->bindParam(':email', $email);
    $stmt1->bindParam(':wachtwoord', $hashedwachtwoord);
    $stmt1->execute();

    $user_id = $pdo->lastInsertId();

    $sql2 = 'INSERT INTO couriers (user_id, bedrijfsnaam) VALUES(:user_id, :bedrijfsnaam)';
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindParam(':user_id', $user_id);
    $stmt2->bindParam(':bedrijfsnaam', $bedrijfsnaam);
    $stmt2->execute();

    $pdo->commit();
    unset($_SESSION['register-history']);
    $_SESSION['message'] = "Gebruiker succesvol geregistreerd.";
    sendWelcomeEmail($email, $naam);
    header('Location: ../index.php?page=login');
} catch (PDOException $error) {
    $pdo->rollBack();
    $_SESSION['message'] = "Registratie mislukt: " . $error->getMessage();
    $_SESSION['register-history'] = $formData;
    header('Location: ../index.php?page=registratie_courier');
}

exit();
