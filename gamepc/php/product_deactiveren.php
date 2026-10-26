<?php
session_start();

//Dependencies
include '../../PrivateW/connection_GamePc.php';

//echo "<pre>", print_r($_POST), "</pre>";

//Variables
if (isset($_GET['Product_id'])) {
    $Product_id = $_GET['Product_id'];

//Logic
    $sql = 'UPDATE producten SET Actief = 0 WHERE Product_id = :Product_id';
    $Statement = $pdo->prepare($sql);
    $Statement->bindParam(":Product_id", $Product_id, PDO::PARAM_INT);

    if (!$Statement->execute()) {
        echo "<script>alert('Product deactiveren niet gelukt.');</script>";
        echo "<script>window.location.href='../index.php?page=productenoverzicht';</script>";
    } else {

         //Redirect
        header('Location: ../index.php?page=productenoverzicht');
    }
} else {
    echo "<script>alert('Geen product gevonden.');</script>";
    echo "<script>window.location.href='../index.php?page=productenoverzicht';</script>";
}
?>