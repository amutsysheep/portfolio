<?php

session_start();

include "../../Private/connection_Foh.php";
require_once __DIR__ . "/Mail.php";
require_once __DIR__ . "/Showmessage.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../Index.php?page=login");
    exit();
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if (empty($email) || empty($password)) {
    ShowMessage("error", "Vul alle velden in.");
    header("Location: ../Index.php?page=login");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ShowMessage("error", "Ongeldig e-mailadres.");
    header("Location: ../Index.php?page=login");
    exit();
}

try {

    $stmt = $pdo->prepare("
        SELECT
            Login_id,
            Rol_id,
            Email,
            Wachtwoord
        FROM login
        WHERE Email = :email
        LIMIT 1
    ");

    $stmt->execute([
        "email" => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user["Wachtwoord"])) {

        ShowMessage(
            "error",
            "Ongeldige combinatie van e-mail en wachtwoord."
        );

        header("Location: ../Index.php?page=login");
        exit();
    }

    $code = random_int(100000, 999999);

    $_SESSION["2fa_code"] = (string)$code;
    $_SESSION["2fa_expire"] = time() + 300;

    $_SESSION["2fa_login_id"] = $user["Login_id"];
    $_SESSION["2fa_email"] = $user["Email"];
    $_SESSION["2fa_rol_id"] = $user["Rol_id"];

    if (!sendTwoFactorCode($user["Email"], $code)) {

        unset(
            $_SESSION["2fa_code"],
            $_SESSION["2fa_expire"],
            $_SESSION["2fa_login_id"],
            $_SESSION["2fa_email"],
            $_SESSION["2fa_rol_id"]
        );

        ShowMessage(
            "error",
            "De verificatiecode kon niet worden verzonden. Probeer het opnieuw."
        );

        header("Location: ../Index.php?page=login");
        exit();
    }

    header("Location: ../Index.php?page=2fa");
    exit();

} catch (PDOException $e) {

    error_log("Login error: " . $e->getMessage());

    ShowMessage(
        "error",
        "Er is een technische fout opgetreden."
    );

    header("Location: ../Index.php?page=login");
    exit();
}