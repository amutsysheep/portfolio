<?php
include "../../connection/connection.php";
session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists in the users table and the status is 'pending'
    $stmt = $pdo->prepare("SELECT * FROM user WHERE confirmationtoken = :token AND active = 0");
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update the user's status to 'active'
        $stmt = $pdo->prepare("UPDATE user SET active = 1 WHERE confirmationtoken = :token");
        $stmt->execute(['token' => $token]);

        // Set a session message
        $_SESSION['message'] = "Your account has been activated!";
        header("location: ../../index.php?page=login");
        exit();
    } else {
        $_SESSION['message'] = "The token is incorrect or the account is already activated!";
        header("location: ../../index.php?page=login");
        exit();
    }
}
?>
