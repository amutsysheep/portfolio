<?php
session_start();

echo "<pre>", print_r($_POST), "</pre>";

//Dependencies
include '../../PrivateW/connection_GamePc.php';

//Variables
$Categorie_id = $_GET['Categorie_id'];

//Logic
$sql_check = 'SELECT COUNT(*) FROM Producten WHERE Categorie_id = :Categorie_id';
$Statement_check = $pdo->prepare($sql_check);
$Statement_check->bindParam(":Categorie_id", $Categorie_id);
$Statement_check->execute();
$product_count = $Statement_check->fetchColumn();

if ($product_count == 0) {
    $sql = 'DELETE FROM Categorien WHERE Categorie_id = :Categorie_id';
    $Statement = $PDO->prepare($sql);
    $Statement->bindParam(":Categorie_id", $Categorie_id);
    $Statement->execute();

//Redirect
    echo "<pre>", print_r($_POST), "</pre>";
    header('Location: ../index.php?page=categorie_beheer');
} else {
    echo "<script>alert('Categorie kan alleen verwijderd worden als deze leeg is');</script>";
    echo "<script>window.location.href='../index.php?page=categorie_beheer';</script>";
    exit();
}
?>