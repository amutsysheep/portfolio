<?php
include '../PrivateW/connection_GamePC.php'; // Verbinding met de database

// Query om de unieke categorieën op te halen
$query = "SELECT DISTINCT Categorie_id, Categorie_naam FROM categorien"; // Haal zowel Categorie_id als Categorie_naam op
$stmt = $pdo->query($query);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<body class="body-home">
<div class="registratie-container">
    <h1 class="registratie-header">Bouw je Pc</h1>

    <!-- Formulier voor toevoegen aan winkelmand -->
    <form action="php/winkelmandjetoevoeg.php" method="POST">
        <?php
        // Loop door alle categorieën en maak een dropdown voor elke categorie
        foreach ($categories as $category) {
            $categoryName = htmlspecialchars($category['Categorie_naam']);
            $categoryId = htmlspecialchars($category['Categorie_id']); // Haal de Categorie_id op
            ?>
            <div class="input-group">
                <label for="categorie_<?= $categoryId; ?>"><?= ucfirst($categoryName); ?>:</label>
                <select name="producten[<?= $categoryId; ?>]" id="categorie_<?= $categoryId; ?>" class="custom-select">
                    <?php
                    // Query om de producten op te halen voor deze specifieke categorie
                    $productQuery = "SELECT Naam_product, Product_id, Prijs FROM producten WHERE Categorie_id = :categorie_id ORDER BY Prijs ASC"; // Sorteer op prijs (goedkoopste eerst)
                    $stmt = $pdo->prepare($productQuery);
                    $stmt->bindParam(':categorie_id', $categoryId, PDO::PARAM_INT);
                    $stmt->execute();
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Als er producten zijn, toon ze in de dropdown
                    if (count($products) > 0) {
                        foreach ($products as $product) {
                            $productId = htmlspecialchars($product['Product_id']);
                            $productName = htmlspecialchars($product['Naam_product']);
                            $productPrice = number_format($product['Prijs'], 2, ',', '.');
                            echo "<option value=\"$productId\">$productName - €$productPrice</option>";
                        }
                    } else {
                        echo '<option disabled>Geen producten beschikbaar</option>';
                    }
                    ?>
                </select>
            </div>
            <?php
        }
        ?>
        <div class="form-submit">
            <input type="submit" value="Voeg toe aan winkelmandje" class="registreer-button">
        </div>
    </form>
</div>
