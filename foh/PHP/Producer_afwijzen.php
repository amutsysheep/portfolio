<?php

session_start();

include "../../Private/connection_Foh.php";

// Check POST
if (!isset($_POST['Producer_id']) || empty($_POST['reason'])) {
    $_SESSION['message'] = "Afwijzen mislukt: geen reden opgegeven.";
    header("Location: ../Index.php?page=producers_beheer");
    exit();
}

$producer_id = $_POST['Producer_id'];
$reason = trim($_POST['reason']);

// Zet status op afgekeurd = 2
$stmt = $pdo->prepare("
    UPDATE producers
    SET
        Producer_status_id = 2,
        Afwijsreden = :reden
    WHERE Producer_id = :Producer_id
");

$stmt->bindParam(":reden", $reason);
$stmt->bindParam(":Producer_id", $producer_id);
$stmt->execute();

$_SESSION['message'] = "Producer succesvol afgewezen.";

header("Location: ../Index.php?page=Producers_goedkeuren");
exit();