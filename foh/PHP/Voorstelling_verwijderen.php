<?php

session_start();

require_once __DIR__ . "/../PHP/Showmessage.php";
include "../../Private/connection_Foh.php";


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header("Location: ../index.php?page=Voorstellingen_beheren");
    exit();
}


$voorstelling_id = (int)($_GET['Voorstelling_id'] ?? 0);


if ($voorstelling_id <= 0) {

    ShowMessage(
        "error",
        "Ongeldige voorstelling."
    );

    header("Location: ../index.php?page=Voorstellingen_beheren");
    exit();
}


try {

    $stmt = $pdo->prepare("
                 UPDATE voorstellingen
                 SET Actief = 0
                 WHERE Voorstelling_id = :Voorstelling_id
    ");

    $stmt->execute([
        'Voorstelling_id' => $voorstelling_id
    ]);


    ShowMessage(
        "success",
        "Voorstelling succesvol verwijderd."
    );

    header("Location: ../index.php?page=Voorstellingen_beheren");
    exit();


} catch (PDOException $e) {

    ShowMessage(
        "error",
        "Voorstelling verwijderen mislukt."
    );

    header("Location: ../index.php?page=Voorstellingen_beheren");
    exit();
}