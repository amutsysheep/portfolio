<?php
session_start();
include '../../PrivateW/connection_GamePC.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $user_id = $_SESSION['User_id'] ?? null;

    if ($product_id && $quantity && $user_id && $quantity > 0) {
        try {
            $sql = "UPDATE winkelmandje SET Aantal = :quantity WHERE Product_id = :product_id AND User_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            echo "success";
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            echo "error";
        }
    } else {
        echo "invalid_input";
    }
}
?>
