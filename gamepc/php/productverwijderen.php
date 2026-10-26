<?php
// Include database connection
include '../../PrivateW/connection_GamePc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Product_id'])) {

    $Product_id = $_POST['Product_id'];

    // Delete the product from the database
    $sql = 'DELETE FROM producten WHERE Product_id = :Product_id';
    $Statement = $pdo->prepare($sql);
    $Statement->bindParam(':Product_id', $Product_id);

    if ($Statement->execute()) {
        // Redirect back to product page after successful deletion
        header('Location: ../index.php?page=productenoverzicht');
        exit();
    } else {
        echo 'Error deleting product.';
    }
}
?>

