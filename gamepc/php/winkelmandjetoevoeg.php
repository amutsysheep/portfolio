<?php
session_start();
include '../../PrivateW/connection_GamePC.php'; // Verbinding met de database

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['User_id'])) {
    header("Location: inlog.php"); // Stuur door naar de inlogpagina als de gebruiker niet is ingelogd
    exit();
}

$userId = $_SESSION['User_id'];

// Controleer of er POST-data is
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producten'])) {
    $producten = $_POST['producten'];

    // Voeg elk geselecteerd product toe aan het winkelmandje
    try {
        foreach ($producten as $categorieId => $productId) {
            // Zorg ervoor dat de product-ID geldig is
            if (!empty($productId)) {
                $sql = "INSERT INTO winkelmandje (User_id, Product_id, Aantal)
                        VALUES (:user_id, :product_id, 1)
                        ON DUPLICATE KEY UPDATE Aantal = Aantal + 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':user_id' => $userId,
                    ':product_id' => $productId,
                ]);
            }
        }
        // Na succesvolle invoer doorsturen naar het winkelmandje
        header("Location: ../index.php?page=winkelmandbeheer");
        exit();
    } catch (PDOException $e) {
        error_log("Fout bij toevoegen aan winkelmandje: " . $e->getMessage());
        echo "Er is een fout opgetreden bij het toevoegen aan je winkelmand.";
    }
} else {
    echo "Ongeldige invoer. Controleer je formulier.";
}
?>
