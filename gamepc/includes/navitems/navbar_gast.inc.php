<html>
<div class="container">
    <h1 class="logo">Game PC Builder</h1>
    <nav class="navbar">
        <ul class="nav-links">
            <li><a href="index.php?page=home" title="home"><img src="images/desktop-regular-24.png" alt="logo V&D" width="50px" style="margin-left: 10px"></a></li>
<!--            <li><a href="#" title="categoriën">categoriën</a></li>-->
            <li class="dropdown">
                <a href="#" title="categoriën" class="nav-item">categorieën ▼</a>
                <ul class="dropdown-menu">
                    <?php
                    include '../PrivateW/connection_GamePC.php'; // Verbinding met de database

                    $sql = "SELECT Categorie_naam, Categorie_id FROM Categorien";
                    $statement = $pdo->prepare($sql);
                    $statement->execute();

                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                    foreach($result as $categorie) {
                        echo'<li><a href="index.php?page=categorie&Categorie_id='.$categorie['Categorie_id'].'" style="text-decoration:none; padding: 13px">'.$categorie['Categorie_naam'].'</a></li>';
                    }
                    ?>
                </ul>
            </li>
            <li><a href="#" title="acties">acties</a></li>
            <li><a href="#" title="pre-builds">prebuilds</a></li>
            <li><a href="#" title="accesoires">accesoires</a></li>
            <li><a href="index.php?page=winkelmandbeheer" style="text-decoration:none" title= "winkelmandje"><img src="images/cart-regular-24.png" alt="winkelmandje icon" width="50px"></a></li>
            <li><a href="index.php?page=inlog" style="text-decoration:none" title= "login"><img src="images/user-regular-24.png" alt="User icon" width="50px"></a></li>
            <li><a href="#" title="contact"><img src="images/phone-regular-24.png" alt="contact" width="50px"></a></li>
        </ul>
    </nav>
</div>
</html>
