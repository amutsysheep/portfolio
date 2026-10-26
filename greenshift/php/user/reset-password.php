<?php
session_start();

require '../../vendor/autoload.php';
include "../../connection/connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordRepeat = $_POST['password_repeat'] ?? '';


    if ($password !== $passwordRepeat) {
        $_SESSION['message'] = "Wachtwoorden komen niet overeen.";
        header("Location: ../../index.php?page=reset-password&token=$token&email=" . urlencode($email));
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['message'] = "Wachtwoord moet minstens 8 tekens zijn.";
        header("Location: ../../index.php?page=reset-password&token=$token&email=" . urlencode($email));
        exit();
    }


    $stmt = $pdo->prepare("SELECT pr.id, pr.expires_at, u.userid 
                           FROM password_resets pr
                           JOIN user u ON pr.user_id = u.userid
                           WHERE u.email = ? AND pr.token_hash = ? LIMIT 1");
    $stmt->execute([$email, hash('sha256', $token)]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        $_SESSION['message'] = "Ongeldige of verlopen resetlink.";
        header("Location: ../../index.php?page=login");
        exit();
    }

    if (strtotime($reset['expires_at']) < time()) {
        $_SESSION['message'] = "Deze resetlink is verlopen.";
        header("Location: ../../index.php?page=forgot-password");
        exit();
    }


    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE email = ?");
    $stmt->execute([$hashedPassword, $email]);


    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE id = ?");
    $stmt->execute([$reset['id']]);

    $_SESSION['message'] = "Je wachtwoord is succesvol vernieuwd. Log nu opnieuw in.";
    header("Location: ../../index.php?page=login");
    exit();
}
