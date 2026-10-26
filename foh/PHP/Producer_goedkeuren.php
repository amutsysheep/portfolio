<?php

session_start();

include "../../Private/connection_Foh.php";

// Producer_id ophalen
$producer_id = $_GET['Producer_id'] ?? '';

if (!$producer_id) {
    $_SESSION['message'] = "Ongeldige aanvraag.";
    header("Location: ../Index.php?page=producers_beheer");
    exit();
}

// Producer goedkeuren
$stmt = $pdo->prepare("
    UPDATE producers
    SET
        Producer_status_id = 1
    WHERE Producer_id = :Producer_id
");

$stmt->bindParam(":Producer_id", $producer_id);
$stmt->execute();

$_SESSION['message'] = "Producer succesvol goedgekeurd.";

header("Location: ../Index.php?page=Producers_goedkeuren");
exit();