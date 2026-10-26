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
    $stmt = $pdo->prepare("UPDATE user SET active = 0 WHERE userid = :userid AND roleid = 2");
    $stmt->execute([':userid' => $userid]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "Leerling succesvol gedeactiveerd.";
    } else {
        $_SESSION['message'] = "Leerling niet gevonden of al gedeactiveerd.";
    }

    header("Location: ../../index.php?page=studentoverview&panel=Admin");
    exit();

} catch (Exception $e) {
    $_SESSION['message'] = "Fout bij deactiveren: " . $e->getMessage();
    header("Location: ../../index.php?page=studentoverview&panel=Admin");
    exit();
}
