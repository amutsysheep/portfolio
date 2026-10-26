<?php
require_once 'PHP/ShowMessage.php';

$sql = "
    SELECT
        Locatie_id,
        Naam,
        Zaal,
        Plaats,
        Straatnaam,
        Huisnummer,
        Postcode,
        Actief
    FROM locaties
    ORDER BY Naam ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$locaties = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="beheer-container">
    <div class="beheer-header">
        <h2>Locaties</h2>
        <a
                href="Index.php?page=Locatie_aanmaken"
                class="button-primary"
        >
            Locatie aanmaken
        </a>
    </div>

    <?php DisplayMessage(); ?>

    <?php if (empty($locaties)): ?>

        <p>Er zijn nog geen locaties aangemaakt.</p>

    <?php else: ?>

        <div class="table-container">
            <table class="beheer-table">
                <thead>
                <tr>
                    <th>Naam</th>
                    <th>Zaal</th>
                    <th>Plaats</th>
                    <th>Straatnaam</th>
                    <th>Huisnummer</th>
                    <th>Postcode</th>
                    <th>Status</th>
                    <th>Bewerken</th>
                    <th>Actief / Inactief</th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($locaties as $locatie): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($locatie['Naam']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($locatie['Zaal'] ?? '') ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($locatie['Plaats']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($locatie['Straatnaam'] ?? '') ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($locatie['Huisnummer'] ?? '') ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($locatie['Postcode'] ?? '') ?>
                        </td>
                        <td>
                            <?php if ($locatie['Actief'] == 1): ?>

                                <span class="status-active">
                                Actief
                            </span>

                            <?php else: ?>

                                <span class="status-inactive">
                                Inactief
                            </span>

                            <?php endif; ?>
                        </td>
                        <td class="table-actions">
                            <a
                                    href="Index.php?page=Locatie_bewerken&id=<?= (int)$locatie['Locatie_id'] ?>"
                                    class="button button-edit"
                            >
                                Bewerken
                            </a>
                        </td>
                        <td class="table-actions">
                            <?php if ($locatie['Actief'] == 1): ?>

                                <a
                                        href="PHP/Locatie_verwijderen.php?id=<?= (int)$locatie['Locatie_id'] ?>"
                                        class="button button-delete"
                                >
                                    Inactief
                                </a>

                            <?php else: ?>

                                <a
                                        href="PHP/Locatie_actief.php?id=<?= (int)$locatie['Locatie_id'] ?>"
                                        class="button button-success"
                                >
                                    Actief
                                </a>

                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
