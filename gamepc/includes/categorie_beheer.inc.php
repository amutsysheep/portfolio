<?php
include '../PrivateW/connection_GamePc.php';


$Rol = $_SESSION['Rol'];

$sql = "SELECT * FROM Categorien";
$Statement = $pdo->prepare($sql);
$Statement->execute();
?>

<body class="cat-body">
<div class="overzicht-container" style="width: 625px">
    <h2 class="overzicht-header">Categorie beheer</h2>
    <?php if(isset($_SESSION['Rol'])) {?>
        <?php if ($_SESSION['Rol'] == '1') {?>
            <table class="producten-table">
                <tr class="producten-row">
                    <th class="producten-header">Categorie</th>
                    <th class="producten-header">Bewerken</th>
                    <th class="producten-header">deactiveren</th>
                    <th class="producten-header">Activeren</th>
<!--                    <th>Verwijderen</th>-->
                </tr>

                <?php
                while($result = $Statement->fetch(PDO::FETCH_ASSOC)){?>
                    <tr class="<?= $result['Actief'] == 0 ? 'non-actief' : ''; ?>">
                        <td><?= $result['Categorie_naam'] ?></td>
                        <td>
                            <button class="bewerken-button">
                                <a href="./index.php?page=categorie_aanpassen&Categorie_id=<?= $result['Categorie_id'] ?>">bewerk</a>
                            </button></td>
                        <td>
                            <button class="delete-button">
                                <a href ="./php/categorie_deactiveren.php?Categorie_id=<?= $result['Categorie_id'] ?>">deactiveer</a>
                            </button></td>
                        <td>
                            <button class="bewerken-button">
                                <a href="./php/categorie_activeren.php?Categorie_id=<?= $result['Categorie_id'] ?>">activeer</a>
                            </button>
                        </td>
<!--                        <td>-->
<!--                            <button class="verwijder-button">-->
<!--                                <a href ="php/categorie_verwijderen.php?Categorie_id=--><?php //= $result['Categorie_id'] ?><!--">verwijder</a>-->
<!--                            </button>-->
<!--                        </td>-->
                    </tr>
                <?php } ?>
            </table>

            <button class="toevoeg-button"><a href="./index.php?page=categorie_toevoegen">toevoegen</a></button>
        <?php } else {?>
            <br><br>
            <h3 style="color:red">U heeft geen toegang tot deze funcies</h3>
        <?php } ?>
    <?php } else { ?>
        <br><br>
        <h3 style="color:red">U heeft geen toegang tot deze funcies</h3>
    <?php } ?>
</div>
</body>