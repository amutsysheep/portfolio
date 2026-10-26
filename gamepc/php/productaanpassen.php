<?php
session_start();

include '../../PrivateW/connection_GamePc.php';

// Variables
$Naam = htmlspecialchars($_POST['Naam_product']);
$Prijs = htmlspecialchars($_POST['Prijs']);
$Beschrijving = htmlspecialchars($_POST['Beschrijving']);
$Aantal = htmlspecialchars($_POST['Aantal']);
$Categorie_id = htmlspecialchars($_POST['Categorie_id']);
$Foto = htmlspecialchars($_POST['Foto']);
$Product_id = htmlspecialchars($_POST['Product_id']);

// Check if product name already exists
$sqlCheck = 'SELECT COUNT(*) FROM producten WHERE Naam_product = :Naam_product AND Product_id != :Product_id';
$statementCheck = $pdo->prepare($sqlCheck);
$statementCheck->bindParam(':Naam_product', $Naam);
$statementCheck->bindParam(':Product_id', $Product_id);
$statementCheck->execute();
$productExists = $statementCheck->fetchColumn();

if ($productExists > 0) {
    echo "<script>alert('Er bestaat al een product met deze naam');</script>";
    echo "<script>window.location.href='../Index.php?page=Producten';</script>";



} else {

    // Logic for updating product
    $sql = 'UPDATE producten SET Naam_product = :Naam_product, Prijs = :Prijs, Beschrijving = :Beschrijving, Aantal = :Aantal, Categorie_id = :Categorie_id, Foto = :Foto WHERE Product_id = :Product_id';
    $statement = $pdo->prepare($sql);
    $statement->bindParam(':Naam_product', $Naam);
    $statement->bindParam(':Prijs', $Prijs);
    $statement->bindParam(':Beschrijving', $Beschrijving);
    $statement->bindParam(':Aantal', $Aantal);
    $statement->bindParam(':Categorie_id', $Categorie_id);
    $statement->bindParam(':Foto', $Foto);
    $statement->bindParam(':Product_id', $Product_id);



    if ($statement->execute()) {
        // Redirect back to product overview or show success message
        header('Location: ../index.php?page=productenoverzicht');
        exit;
    } else {
        echo 'Error updating product.';
    }
}
?>
