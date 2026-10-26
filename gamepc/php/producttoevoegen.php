<?php
// Dependencies
session_start();
include '../../PrivateW/connection_GamePc.php';

// Role check: Only allow admins to access this page
//if (!isset($_SESSION['Rol']) || $_SESSION['Rol'] !== 'Admin') {
    //header("Location: ../index.php?page=inlog");
    //exit();
//}

// Variables
$Naam = $_POST['Naam_product'];
$Categorie_id = $_POST['Categorie_id'];
$Prijs = $_POST['Prijs'];
$Beschrijving = $_POST['Beschrijving'];
$Foto = $_POST['Foto'];

// Validate input
if (empty($Naam)) {
    die('Naam is required.');
}

try {
    // Check if the product already exists
    $sqlCheck = 'SELECT COUNT(*) FROM producten WHERE Naam_product = :Naam_product';
    $checkStatement = $pdo->prepare($sqlCheck);
    $checkStatement->bindParam(':Naam_product', $Naam);
    $checkStatement->execute();
    $productExists = $checkStatement->fetchColumn();

    if ($productExists > 0) {
        echo "<script>alert('Er bestaat al een product met deze naam');</script>";
        echo "<script>window.location.href='../index.php?page=producttoevoegen';</script>";
    } else {

        // Insert into the database
        $sqlInsert = 'INSERT INTO producten (Naam_product, Categorie_id, Prijs, Beschrijving, Foto) VALUES (:Naam_product, :Categorie_id, :Prijs, :Beschrijving, :Foto)';
        $statement = $pdo->prepare($sqlInsert);
        $statement->bindParam(':Naam_product', $Naam);
        $statement->bindParam(':Categorie_id', $Categorie_id);
        $statement->bindParam(':Prijs', $Prijs);
        $statement->bindParam(':Beschrijving', $Beschrijving);
        $statement->bindParam(':Foto', $Foto);
        $statement->execute();

        echo 'Product succesvol toegevoegd.<br>';
        header('Location: ../index.php?page=productenoverzicht');
        exit();
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
