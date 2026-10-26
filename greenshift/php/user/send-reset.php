<?php
session_start();

require '../../vendor/autoload.php';
include "../../connection/connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv\Dotenv::createImmutable('../../env/phpmailer');
$dotenv->load();

function sendResetEmail($email, $resetLink) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = ($_ENV['SMTP_SECURE'] === 'tls')
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int) $_ENV['SMTP_PORT'];

        $mail->setFrom('info@GreenShift.nl', 'GreenShift');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Reset je wachtwoord';
        $mail->Body    = "Klik op de volgende link om je wachtwoord te resetten: 
                          <a href='$resetLink'>$resetLink</a>";
        $mail->AltBody = "Gebruik deze link om je wachtwoord te resetten: $resetLink";

        $mail->send();
    } catch (Exception $e) {
        $_SESSION['message'] = "E-mail kon niet verzonden worden. Error: {$mail->ErrorInfo}";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';


    $stmt = $pdo->prepare("SELECT userid FROM user WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['message'] = "Geen gebruiker gevonden met dit e-mailadres.";
        header("Location: ../../index.php?page=forgot-password");
        exit();
    }


    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expires = (new DateTime('+1 hour', new DateTimeZone('Europe/Amsterdam')))
        ->format('Y-m-d H:i:s');


    $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['userid']]);


    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['userid'], $tokenHash, $expires]);


    $resetLink = "http://localhost/greenshift/index.php?page=reset-password&token=$token&email=" . urlencode($email);


    sendResetEmail($email, $resetLink);

    $_SESSION['message'] = "Als dit e-mailadres bestaat, is er een resetlink verstuurd.";
    header("Location: ../../index.php?page=login");
    exit();
}
