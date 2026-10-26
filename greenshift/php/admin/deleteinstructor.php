<?php
session_start();
include "../../connection/connection.php";

// Controleer of er een userid is doorgegeven
if (!isset($_GET['userid'])) {
    $_SESSION['message'] = "Geen instructeur geselecteerd.";
    header("Location: ../../index.php?page=instructormangement&panel=Admin");
    exit();
}

$userid = intval($_GET['userid']);



try {
    // Optie 1: Soft delete (aanbevolen)
    $stmt = $pdo->prepare("UPDATE user SET active = 0 WHERE userid = :userid AND roleid = 3");
    $stmt->execute([':userid' => $userid]);

    // Optie 2: Hard delete (verwijdert echt uit database)
    // $stmt = $pdo->prepare("DELETE FROM user WHERE userid = :userid AND roleid = 3");
    // $stmt->execute([':userid' => $userid]);

    $_SESSION['message'] = "Instructeur succesvol verwijderd.";
    header("Location: ../../index.php?page=instructormangement&panel=Admin");
    exit();

} catch (Exception $e) {
    $_SESSION['message'] = "Fout bij verwijderen: " . $e->getMessage();
    header("Location: ../../index.php?page=instructormangement&panel=Admin");
    exit();
}
?>
