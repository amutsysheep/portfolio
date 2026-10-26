<?php
session_start();

//Dependencies
include '../../PrivateW/connection_GamePc.php';

//Variables
$Categorie_naam = htmlspecialchars($_POST['Categorie_naam']);
$Categorie_id =htmlspecialchars($_POST['Categorie_id']);

//Logic
$Check_categorie_sql = 'SELECT COUNT(*) FROM Categorien WHERE Categorie_naam = :Categorie_naam';
$Check_categorie_stmt = $pdo->prepare($Check_categorie_sql);
$Check_categorie_stmt->bindParam(':Categorie_naam', $Categorie_naam);
$Check_categorie_stmt->execute();
$Categorie_Count = $Check_categorie_stmt->fetchColumn();

if ($Categorie_Count > 0) {
    echo "<script>alert('Er bestaat al een categorie met deze naam');</script>";
    echo "<script>window.location.href='../index.php?page=categorie_beheer';</script>";
} else {
    $sql = 'UPDATE Categorien SET Categorie_naam = :Categorie_naam WHERE Categorie_id= :Categorie_id';

    $Statement = $PDO->prepare($sql);
    $Statement->bindParam(":Categorie_naam", $Categorie_naam);
    $Statement->bindParam(":Categorie_id", $Categorie_id);
    $Statement->execute();

//Redirect
    header('Location: ../index.php?page=categorie_beheer');
}
?>