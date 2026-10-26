<?php
include "../../connection/connection.php";
session_start();


// Pak token uit GET of POST
$token = $_GET['token'] ?? $_POST['token'] ?? null;

if (!$token) {
    $_SESSION['message'] = "Geen token opgegeven!";
    header("location: ../../index.php?page=login");
    exit();
}


// Lookup user with token
$stmt = $pdo->prepare("
    SELECT u.userid, u.roleid, r.rolename 
    FROM user u 
    JOIN role r ON u.roleid = r.roleid 
    WHERE u.confirmationtoken = :token AND u.active = 0
");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['message'] = "Token ongeldig of account al geactiveerd!";
    header("location: ../../index.php?page=login");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if ($password !== $confirm) {
        $_SESSION['message'] = "Wachtwoorden komen niet overeen!";
    } elseif (strlen($password) < 15) {
        $_SESSION['message'] = "Wachtwoord moet minimaal 15 tekens zijn.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare("
            UPDATE user 
            SET password = :password, active = 1, confirmationtoken = NULL, updated_at = NOW()
            WHERE userid = :userid
        ");
        $update->execute([
            ':password' => $hash,
            ':userid' => $user['userid']
        ]);

        // Role-based message
        if ($user['rolename'] === 'logout_user') {
            $_SESSION['message'] = "Uw instructeur account is nu geactiveerd. U kunt inloggen!";
        }

        header("location: ../../index.php?page=login");
        exit();
    }
}

?>