<?php
include '../PrivateW/connection_GamePc.php';

// Check if 'Product_id' is set in the GET request
if (!isset($_GET['Product_id'])) {
    die('Product_id is missing in the URL.');
}

$Product_id = $_GET['Product_id'];

$sql = 'SELECT * FROM producten WHERE Product_id = :Product_id';
$statement = $pdo->prepare($sql);
$statement->bindParam(':Product_id', $Product_id);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

// Fetch categories from the database
$sql = 'SELECT Categorie_id, Categorie_naam FROM categorien';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$Naam = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="aanpassen-container">
    <h1 style="margin-top: 35px">Product Aanpassen</h1>
    <br>
    <form class="categorie-acties" action="php/productaanpassen.php" method="post">
        <label for="Naam_product">Naam product</label>
        <input class="input" type="text" id="Naam_product" name="Naam_product" required value="<?= htmlspecialchars($result['Naam_product']) ?>"><br>

        <label for="Prijs">Prijs</label>
        <input type="number" step="0.01" id="Prijs" name="Prijs" required value="<?= htmlspecialchars($result['Prijs']) ?>"><br>

        <label for="Beschrijving">Beschrijving</label>
        <input type="text" id="Beschrijving" name="Beschrijving" required value="<?= htmlspecialchars($result['Beschrijving']) ?>"><br>

        <label for="Aantal">Aantal</label>
        <input type="number" step="1.0" id="Aantal" name="Aantal" required value="<?= htmlspecialchars($result['Aantal']) ?>"><br>

        <label for="Categorie_id">Categorie</label>
        <select id="Categorie_id" name="Categorie_id" required>
            <option value="">Selecteer een categorie</option>
            <?php
            foreach ($Naam as $Categorie) {
                ?>
                <option value=" <?= htmlspecialchars($Categorie['Categorie_id']) ?> " <?php if($Categorie['Categorie_id'] == $result['Categorie_id']){ echo 'Selected';}?>> <?= htmlspecialchars($Categorie['Categorie_naam']) ?> </option>
            <?php }
            ?>
        </select><br>

        <label for="Foto">Foto</label>
        <input type="file" id="Foto" name="Foto" accept="image/*"><br>

        <input type="hidden" name="Product_id" value="<?= htmlspecialchars($result['Product_id']) ?>">

        <input class="toevoeg-button" type="submit" value="Aanpassen">
    </form>
</div>
</body>
