<?php
session_start();
include "../../../private/connection.php";
require_once './../vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../env/phpmailer');
$dotenv->load();

// Haal user_id op
$user_id = $_GET['user_id'] ?? '';

if (!$user_id) {
    $_SESSION['message'] = "Ongeldige aanvraag.";
    header("Location: ../../index.php?page=courier_beheer");
    exit();
}

// Update courier status naar 1 (goedgekeurd)
$stmt = $pdo->prepare("UPDATE couriers SET is_Goedgekeurd = 1, beoordeeld_op = current_timestamp WHERE user_id = :user_id");
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

// Haal email op om mail te sturen
$stmtEmail = $pdo->prepare("SELECT email, naam FROM gebruikers WHERE user_id = :user_id");
$stmtEmail->bindParam(":user_id", $user_id);
$stmtEmail->execute();
$courier = $stmtEmail->fetch(PDO::FETCH_ASSOC);

// Mail versturen
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
    $mail->Subject = "Je courier aanvraag is goedgekeurd!";
    $mail->Body = "Hallo {$courier['naam']}, Goed nieuws! Je account is goedgekeurd.- PAS Team";
    $mail->send();
}

$_SESSION['message'] = "Courier succesvol goedgekeurd.";
header("Location: ../index.php?page=courier_beheer");
exit();
