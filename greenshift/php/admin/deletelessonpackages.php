<?php
session_start();
include "../../connection/connection.php";

// Controleer of er een userid is doorgegeven
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "Geen instructeur geselecteerd.";
    header("Location: ../../index.php?page=lessonpackage&panel=Admin");
    exit();
}

$userid = intval($_GET['id']);


try {
    // Optie 1: delete (aanbevolen)
    $stmt = $pdo->prepare("DELETE FROM lesson_packages WHERE id = :id");
    $stmt->execute([':id' => $userid]);

    $_SESSION['message'] = "Instructeur succesvol verwijderd.";
    header("Location: ../../index.php?page=lessonpackage&panel=Admin");
    exit();

} catch (Exception $e) {
    $_SESSION['message'] = "Fout bij verwijderen: " . $e->getMessage();
    header("Location: ../../index.php?page=lessonpackage&panel=Admin");
    exit();
}

?>