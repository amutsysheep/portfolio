<?php
require "PHP/Showmessage.php";

function veldNietGevonden($waarde)
{
    if (!isset($waarde) || $waarde === '' || $waarde === null) {
        return 'Veld niet gevonden';
    }

    return htmlspecialchars($waarde);
}

$Opera = [];

if ($pdo !== null) {

    $sql = "
    SELECT 
        o.Opera_id,
        o.Naam,
        o.Omschrijving,
        o.Cover_afbeelding,
        g.Genre
    FROM Operas o
    INNER JOIN genres g ON o.Genre_id = g.Genre_id
    WHERE o.Actief = 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $Opera = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<div class="Showmessage">
    <?php DisplayMessage(); ?>
</div>
<div class="opera-grid">
    <a href="Index.php?page=Opera_aanmaken_producer" class="opera-card opera-toevoegen-card">
        <span class="opera-plus">+</span>
    </a>
    <?php if ($pdo === null) { ?>
        <div class="opera-card">
            <div class="opera-card-content">
                <span class="opera-genre">
                    Veld niet gevonden
                </span>
                <div class="opera-info">
                    <h3>
                        Veld niet gevonden
                    </h3>
                    <p>
                        Veld niet gevonden
                    </p>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <?php foreach ($Opera as $result) { ?>
            <div class="opera-card">
                <?php if (!empty($result['Cover_afbeelding'])) { ?>

                    <img
                        src="Images/<?= htmlspecialchars($result['Cover_afbeelding']); ?>"
                        alt="<?= veldNietGevonden($result['Naam']); ?>"
                        class="opera-image"
                    >
                <?php } else { ?>

                    <div class="opera-image">
                        Veld niet gevonden
                    </div>
                <?php } ?>
                <div class="opera-overlay"></div>
                <div class="opera-card-content">
                    <span class="opera-genre">
                        <?= veldNietGevonden($result['Genre']); ?>
                    </span>
                    <div class="opera-info">
                        <h3>
                            <?= veldNietGevonden($result['Naam']); ?>
                        </h3>
                        <p>
                            <?= veldNietGevonden($result['Omschrijving']); ?>
                        </p>
                        <a href="index.php?page=Opera_bewerken_producer&id=<?= (int)$result['Opera_id']; ?>" class="opera-button">
                            Bewerk
                        </a>
                        <form action="PHP/Opera_verwijderen_producer.php" method="POST" onsubmit="return confirm('Weet je zeker dat je deze opera wilt verwijderen?');" style="display: inline;">
                            <input type="hidden" name="opera_id" value="<?= (int)$result['Opera_id']; ?>">
                            <button type="submit" class="opera-verwijder-button">
                                Verwijder
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
</div>