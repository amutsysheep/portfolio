<?php
session_start();
include "../../connection/connection.php";

if (!isset($_GET['userid'])) {
    $_SESSION['message'] = "Geen leerling geselecteerd.";
    header("Location: ../../index.php?page=studentoverview&panel=Admin");
    exit();
}

$userid = intval($_GET['userid']);

try {
    // Controleer eerst huidige status
    $stmt = $pdo->prepare("SELECT active FROM user WHERE userid = :userid AND roleid = 2");
    $stmt->execute([':userid' => $userid]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        $_SESSION['message'] = "Leerling niet gevonden.";
    } elseif ($student['active'] == 1) {
        $_SESSION['message'] = "Leerling is al actief.";
    } else {
        // Activeer de leerling
        $stmt = $pdo->prepare("UPDATE user SET active = 1 WHERE userid = :userid AND roleid = 2");
        $stmt->execute([':userid' => $userid]);
        $_SESSION['message'] = "Leerling succesvol geactiveerd.";
    }

    header("Location: ../../index.php?page=studentoverview&panel=Admin");
    exit();

} catch (Exception $e) {
    $_SESSION['message'] = "Fout bij activeren: " . $e->getMessage();
    header("Location: ../../index.php?page=studentoverview&panel=Admin");
    exit();
}
