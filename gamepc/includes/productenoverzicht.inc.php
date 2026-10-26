<?php
include '../PrivateW/connection_GamePc.php';


$sql = "SELECT * FROM producten";
$Statement = $pdo->prepare($sql);
$Statement->execute();
$result = $Statement->fetch(PDO::FETCH_ASSOC);

// Update SQL query to join the categorie table
$sql = 'SELECT p.*, c.Categorie_naam AS Categorie_naam
        FROM producten p
        JOIN categorien c ON p.Categorie_id = c.Categorie_id';
$Statement = $pdo->prepare($sql);
$Statement->execute();


$Rol_id = ['1'];
?>

<div class="overzicht-container">
    <h1 class="overzicht-header">Producten overzicht</h1>

    <table class="producten-table">
        <thead>
        <tr class="producten-row">
            <th class="producten-header">Naam</th>
            <th class="producten-header">Categorie</th>
            <th class="producten-header">Prijs</th>
            <th class="producten-header">Aantal</th>
            <th class="producten-header">Acties</th>
            <th class="producten-header">Acties</th>
            <th class="producten-header">Acties</th>
            <th class="producten-header">Acties</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($result = $Statement->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr class="celestial-row <?= $result['Actief'] == 0 ? 'non-actief' : ''; ?>">
                <td class="producten-data"><?= htmlspecialchars($result['Naam_product']) ?></td>
                <td class="producten-data"><?= htmlspecialchars($result['Categorie_naam']) ?></td>
                <td class="producten-data"><?= htmlspecialchars($result['Prijs']) ?></td>
                <td class="producten-data"><?= htmlspecialchars($result['Aantal']) ?></td>
                <td>
                    <form action="php/productverwijderen.php" method="POST" onsubmit="return confirm('Weet u zeker dat u dit product wilt verwijderen?');">
                        <input type="hidden" name="Product_id" value="<?= htmlspecialchars($result['Product_id']) ?>">
                        <button type="submit" class="delete-button">Verwijderen</button>
                    </form>
                </td>
                        <td>
                            <button class="bewerken-button">
                                <a href="index.php?page=productaanpassen&Product_id=<?= $result['Product_id'] ?>">bewerk</a>
                            </button>
                        </td>
                        <td>
                            <button class="bewerken-button">
                                <a href="php/product_activeren.php?Product_id=<?= $result['Product_id']?>">activeer</a>
                            </button>
                        </td>
                        <td>
                            <button class="bewerken-button">
                                <a href="php/product_deactiveren.php?Product_id=<?= $result['Product_id']?>">deactiveer</a>
                            </button>
                        </td>
                    </tr>
                    <?php }?>
<!--                --><?php //} ?>
                </tbody>
            </table>

    <button class="toevoeg-button"><a href="index.php?page=producttoevoegen">toevoegen</a></button>
<!--    --><?php //} else {?>
<!--    <h3 style="color:red">U heeft geen toegang tot deze functies</h3>-->
<!--    --><?php //}?>
<!--    --><?php //} else {?>
<!--    <h3 style="color:red">U heeft geen toegang tot deze functies</h3>-->
<!--    --><?php //}?>
</div>