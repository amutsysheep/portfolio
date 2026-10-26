<?php
include '../PrivateW/connection_GamePc.php';

$Categorie_id = $_GET['Categorie_id'];

$sql = 'SELECT * FROM Categorien WHERE Categorie_id = :Categorie_id';
$statement = $pdo->prepare($sql);
$statement->bindParam('Categorie_id', $Categorie_id);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

?>

<body>
<div class="cat-tv-container">
    <h1 style="margin-top: 35px">categorie aanpassen</h1>
    <br>
    <form action="php/categorie_aanpassen.php" method="post">
        <label for="Categorie_naam">Naam categorie</label>
        <input class="input" type="text" id="Categorie_naam" name="Categorie_naam" required value="<?= $result['Categorie_naam'] ?>"><br><br>
        <input type="hidden" name="Categorie_id" value="<?= $result['Categorie_id']?>">

        <input class="toevoegen-button" type="submit" value="aanpassen">
    </form>
</div>
</body>