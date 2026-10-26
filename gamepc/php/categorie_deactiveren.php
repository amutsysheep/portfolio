<?php
session_start();

//Dependencies
include '../../PrivateW/connection_GamePc.php';

//Variables
if (isset($_GET['Categorie_id'])) {
    $Categorie_id = $_GET['Categorie_id'];

//Logic
    $sql = 'UPDATE Categorien SET Actief = 0 WHERE Categorie_id = :Categorie_id';
    $Statement = $pdo->prepare($sql);
    $Statement->bindParam(":Categorie_id", $Categorie_id, PDO::PARAM_INT);

    if (!$Statement->execute()) {
        echo "<script>alert('Categorie deactiveren niet gelukt.');</script>";
        echo "<script>window.location.href='../index.php?page=categorie_beheer';</script>";
    } else {

        // Redirect
        header('Location: ../index.php?page=categorie_beheer');
    }
} else {
    echo "<script>alert('Geen categorie gevonden.');</script>";
    echo "<script>window.location.href='../index.php?page=categorie_beheer';</script>";
}
?>