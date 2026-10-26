<?php
session_start();
include "../../../private/connection.php";
require_once './../vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../env/phpmailer');
$dotenv->load();

// Check POST
if (!isset($_POST['user_id']) || empty($_POST['reason'])) {
    $_SESSION['message'] = "Afwijzen mislukt: geen reden opgegeven.";
    header("Location: ../index.php?page=courier_beheer");
    exit();
}

$user_id = $_POST['user_id'];
$reason = trim($_POST['reason']);

// Zet status op afgekeurd = 2
$stmt = $pdo->prepare("UPDATE couriers SET is_Goedgekeurd = 2, afwijsreden = :reden, beoordeeld_op = current_timestamp WHERE user_id = :user_id");
$stmt->bindParam(":reden", $reason);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

// Haal email + naam
$stmtEmail = $pdo->prepare("SELECT email, naam FROM gebruikers WHERE user_id = :user_id");
$stmtEmail->bindParam(":user_id", $user_id);
$stmtEmail->execute();
$courier = $stmtEmail->fetch(PDO::FETCH_ASSOC);

// Verstuur mail
if ($courier) {

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->Port = $_ENV['SMTP_PORT'];

    $mail->setFrom("info@pas.nl", "PAS");
    $mail->addAddress($courier['email']);
    $mail->Subject = "Je aanvraag is afgewezen";
    $mail->isHTML(true);
    $mail->Body = "
        Hallo <strong>{$courier['naam']}</strong>,<br><br>
        Helaas is je aanvraag <strong>afgewezen</strong>.<br><br>
        <strong>Reden:</strong><br>
        <i>$reason</i><br><br>
        Je kunt eventueel opnieuw aanvragen nadat je dit hebt opgelost.<br><br>
        Met vriendelijke groet,<br>
        <strong>PAS Team</strong>
    ";

    $mail->send();
}

$_SESSION['message'] = "Courier succesvol afgewezen.";
header("Location: ../index.php?page=courier_beheer");
exit();
