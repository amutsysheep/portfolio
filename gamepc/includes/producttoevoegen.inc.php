<?php
include '../PrivateW/connection_GamePc.php';


// Fetch categories from the database
$sql = 'SELECT Categorie_id, Categorie_naam FROM categorien';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$Naam = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<body class="product-toevoegen-body">
<div class="toevoegen-container">
    <h1 style="margin-top: 35px">Product Toevoegen</h1>
    <br>
    <form class="categorie-acties" action="php/producttoevoegen.php" method="post">
        <label for="Naam_product">Naam product</label>
        <input class="input" type="text" id="Naam_product" name="Naam_product" required<br>

        <label for="Beschrijving">Beschrijving</label>
        <input class="input" type="text" id="Beschrijving" name="Beschrijving" required<br>

        <label for="Prijs">Prijs</label>
        <input type="number" step="0.01" id="Prijs" name="Prijs" required<br>

        <label for="Categorie_id">Categorie</label>
        <select id="Categorie_id" name="Categorie_id" required>
            <option value="">Selecteer een categorie</option>
            <?php
            foreach ($Naam as $Categorie) {
                echo "<option value='" . htmlspecialchars($Categorie['Categorie_id']) . "'>" . htmlspecialchars($Categorie['Categorie_naam']) . "</option>";
            }
            ?>
        </select><br>

        <label for="Foto">Foto</label>
        <input type="file" id="Foto" name="Foto" accept="image/*"><br>

        <input type="hidden" name="Product_id" value="<?= htmlspecialchars($result['Product_id']) ?>">

        <input class="toevoegen-button" type="submit" value="Toevoegen">
    </form>
</div>
</body>
