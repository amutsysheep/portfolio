<?php
session_start();
include '../../PrivateW/connection_GamePc.php';

if (isset($_GET['Categorie_id'])) {
    $Categorie_id = intval($_GET['Categorie_id']);

    $sql = 'UPDATE Categorien SET Actief = 1 WHERE Categorie_id = :Categorie_id';
    $Statement = $pdo->prepare($sql);
    $Statement->bindParam(':Categorie_id', $Categorie_id, PDO::PARAM_INT);
    $Statement->execute();
} else {
    echo "<script>alert('Ongeldig Categorie ID');</script>";
    echo "<script>window.location.href='../index.php?page=Categorie_beheer';</script>";
}

header('Location: ../index.php?page=Categorie_beheer');
?>