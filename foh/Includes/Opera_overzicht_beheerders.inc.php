<?php

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

?>

<div class="opera-grid">
    <?php foreach ($Opera as $result) { ?>
        <div class="opera-card">
            <img
                    src="Images/<?php echo htmlspecialchars($result['Cover_afbeelding']); ?>"
                    alt="<?= htmlspecialchars($result['Naam']); ?>"
                    class="opera-image"
            >
            <div class="opera-overlay"></div>
            <div class="opera-card-content">

                <span class="opera-genre">
                    <?= htmlspecialchars($result['Genre']); ?>
                </span>

                <div class="opera-info">
                    <h3>
                        <?= htmlspecialchars($result['Naam']); ?>
                    </h3>
                    <p>
                        <?= htmlspecialchars($result['Omschrijving']); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
