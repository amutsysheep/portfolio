<?php
session_start();
include '../../PrivateW/connection_GamePC.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $user_id = $_SESSION['User_id'] ?? null;

    if ($product_id && $user_id) {
        try {
            $sql = "DELETE FROM winkelmandje WHERE Product_id = :product_id AND User_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: ../index.php?page=winkelmandbeheer");
            exit();
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            echo "error";
        }
    } else {
        echo "invalid_input";
    }
}
?>
