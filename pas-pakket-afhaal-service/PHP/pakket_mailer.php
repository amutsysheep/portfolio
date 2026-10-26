<?php

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../env/phpmailer');
$dotenv->load();

function pakketStatusMailData(PDO $pdo, int $pakket_id): array
{
    $stmt = $pdo->prepare("
        SELECT 
            g.email AS klant_email,
            p.track_trace_code,
            ps.status
        FROM pakketten p
        JOIN gebruikers g ON g.user_id = p.user_id
        JOIN pakket_status ps ON ps.status_id = p.status_id
        WHERE p.pakket_id = :pid
        LIMIT 1
    ");
    $stmt->execute([':pid' => $pakket_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$data ||
        empty($data['klant_email']) ||
        empty($data['track_trace_code']) ||
        empty($data['status'])
    ) {
        throw new Exception('Maildata voor pakket ontbreekt.');
    }

    return $data;
}

function stuurPakketStatusMail(
    string $email,
    string $trackTrace,
    string $status,
    string $datumTijd,
    string $trackUrl
): void {
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

        $mail->setFrom('info@pas.nl', 'PAS');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Update: status van je pakket';

        $mail->Body =
            "<p>De status van je pakket is gewijzigd.</p>" .
            "<p><b>Track & Trace:</b> " . htmlspecialchars($trackTrace) . "</p>" .
            "<p><b>Nieuwe status:</b> " . htmlspecialchars($status) . "</p>" .
            "<p><b>Datum & tijd:</b> " . htmlspecialchars($datumTijd) . "</p>" .
            "<p><b>Volg je pakket:</b><br><a href=\"" . htmlspecialchars($trackUrl) . "\">" . htmlspecialchars($trackUrl) . "</a></p>" .
            "<br><p>Met vriendelijke groet,<br><strong>PAS Team</strong></p>";

        $mail->AltBody =
            "De status van je pakket is gewijzigd.\n" .
            "Track & Trace: {$trackTrace}\n" .
            "Nieuwe status: {$status}\n" .
            "Datum & tijd: {$datumTijd}\n" .
            "Volg je pakket: {$trackUrl}\n\n" .
            "Met vriendelijke groet,\nPAS Team";

        $mail->send();

    } catch (Exception $e) {
        // Geen echo hier, anders krijg je headers already sent in je redirect-scripts.
        // Als je logging hebt: log dit daar.
        throw new Exception("Mailer fout: {$mail->ErrorInfo}");
    }
}
