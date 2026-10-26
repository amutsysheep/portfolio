<?php

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
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $Opera = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<div class="opera-grid">
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
                        <a href="#" class="opera-button">
                            Koop tickets
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
</div>