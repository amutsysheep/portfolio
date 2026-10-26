<?php
session_start();
include '../../PrivateW/connection_GamePc.php';

if (isset($_GET['Product_id'])) {
    $Product_id = intval($_GET['Product_id']);

    $sql = 'UPDATE producten SET Actief = 1 WHERE Product_id = :Product_id';
    $Statement = $pdo->prepare($sql);
    $Statement->bindParam(':Product_id', $Product_id, PDO::PARAM_INT);
    $Statement->execute();
} else {
    echo "<script>alert('Ongeldig Product ID');</script>";
    echo "<script>window.location.href='../index.php?page=productenoverzicht';</script>";
}

header('Location: ../index.php?page=productenoverzicht');
?>